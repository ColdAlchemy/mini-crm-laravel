<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Importa clientes de uma planilha CSV (UTF-8 ou Windows-1252, separador "," ou ";").
 * Cliente com o mesmo e-mail é atualizado; os demais são criados. Linhas com problema são
 * puladas e listadas no relatório, sem interromper o resto.
 */
class ImportadorCsv
{
    public const MAX_LINHAS = 2000;

    private const ALIASES = [
        'nome' => ['nome', 'name', 'cliente', 'nomecompleto'],
        'email' => ['email', 'mail', 'emailprincipal'],
        'telefone' => ['telefone', 'celular', 'whatsapp', 'fone', 'tel', 'contato'],
        'empresa' => ['empresa', 'company', 'razaosocial', 'loja'],
        'status' => ['status', 'situacao'],
    ];

    /** @return array{criados: int, atualizados: int, ignoradas: array<int, array{linha: int, motivo: string}>} */
    public function importar(string $caminho): array
    {
        $texto = (string) file_get_contents($caminho);
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        if (! mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }

        $fluxo = fopen('php://temp', 'r+');
        fwrite($fluxo, $texto);
        rewind($fluxo);

        $primeira = strtok($texto, "\n") ?: '';
        $separador = substr_count($primeira, ';') > substr_count($primeira, ',') ? ';' : ',';

        $cabecalho = fgetcsv($fluxo, 0, $separador);
        $colunas = $this->mapearColunas($cabecalho ?: []);
        if (! isset($colunas['nome'])) {
            fclose($fluxo);
            throw new RuntimeException('A primeira linha da planilha precisa ter uma coluna "nome" (e, se quiser, email, telefone, empresa e status).');
        }

        $resultado = ['criados' => 0, 'atualizados' => 0, 'ignoradas' => []];
        $numero = 1;

        DB::transaction(function () use ($fluxo, $separador, $colunas, &$resultado, &$numero) {
            while (($linha = fgetcsv($fluxo, 0, $separador)) !== false) {
                $numero++;
                if ($linha === [null] || count(array_filter($linha, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue; // linha em branco
                }
                if ($numero - 1 > self::MAX_LINHAS) {
                    $resultado['ignoradas'][] = ['linha' => $numero, 'motivo' => 'Passou do limite de '.self::MAX_LINHAS.' linhas por importação.'];

                    continue;
                }
                $this->processarLinha($linha, $colunas, $numero, $resultado);
            }
        });
        fclose($fluxo);

        return $resultado;
    }

    /** @param array<int, string|null> $cabecalho @return array<string, int> campo => posição da coluna */
    private function mapearColunas(array $cabecalho): array
    {
        $mapa = [];
        foreach ($cabecalho as $posicao => $titulo) {
            $chave = preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $titulo)));
            foreach (self::ALIASES as $campo => $apelidos) {
                if (! isset($mapa[$campo]) && in_array($chave, $apelidos, true)) {
                    $mapa[$campo] = $posicao;
                }
            }
        }

        return $mapa;
    }

    private function processarLinha(array $linha, array $colunas, int $numero, array &$resultado): void
    {
        $valor = fn (string $campo) => isset($colunas[$campo]) ? trim((string) ($linha[$colunas[$campo]] ?? '')) : '';

        $nome = $valor('nome');
        $email = Str::lower($valor('email'));

        if ($nome === '') {
            $resultado['ignoradas'][] = ['linha' => $numero, 'motivo' => 'Sem nome.'];

            return;
        }
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultado['ignoradas'][] = ['linha' => $numero, 'motivo' => "E-mail inválido ({$email})."];

            return;
        }

        $dados = array_filter([
            'nome' => Str::limit($nome, 120, ''),
            'email' => $email ?: null,
            'telefone' => Str::limit($valor('telefone'), 30, '') ?: null,
            'empresa' => Str::limit($valor('empresa'), 120, '') ?: null,
            'status' => $this->status($valor('status')),
        ], fn ($v) => $v !== null && $v !== '');

        $existente = $email !== ''
            ? Cliente::whereRaw('lower(email) = ?', [$email])->first()
            : Cliente::where('nome', $dados['nome'])->when($dados['telefone'] ?? null, fn ($q, $t) => $q->where('telefone', $t))->first();

        if ($existente) {
            if ($email === '') {
                $resultado['ignoradas'][] = ['linha' => $numero, 'motivo' => 'Já cadastrado (mesmo nome e telefone).'];

                return;
            }
            $existente->update($dados);
            $resultado['atualizados']++;

            return;
        }

        Cliente::create($dados + ['status' => 'lead']);
        $resultado['criados']++;
    }

    private function status(string $texto): ?string
    {
        $chave = Str::lower(Str::ascii($texto));

        return match (true) {
            $chave === '' => null,
            str_contains($chave, 'inativ') => 'inativo',
            str_contains($chave, 'ativo') || str_contains($chave, 'cliente') => 'ativo',
            default => 'lead',
        };
    }
}
