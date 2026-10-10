<?php

namespace App\Support;

/** Formatação e leitura de valores no padrão brasileiro. */
final class Formato
{
    public static function moeda(float|int|string|null $valor): string
    {
        return 'R$ '.number_format((float) $valor, 2, ',', '.');
    }

    /** Moeda sem centavos para indicadores (R$ 12.400). */
    public static function moedaInteira(float|int|string|null $valor): string
    {
        return 'R$ '.number_format((float) $valor, 0, ',', '.');
    }

    /**
     * Lê um valor digitado ("1.500,50", "R$ 1500", "1500.50") e devolve um número, ou null se não for número.
     * Com vírgula, o ponto é separador de milhar; sem vírgula, "1.500" também é lido como milhar.
     */
    public static function decimal(mixed $texto): ?float
    {
        if (is_int($texto) || is_float($texto)) {
            return (float) $texto;
        }
        $texto = trim(str_replace(['R$', ' ', "\u{00A0}"], '', (string) $texto));
        if ($texto === '') {
            return null;
        }
        if (str_contains($texto, ',')) {
            $texto = str_replace(',', '.', str_replace('.', '', $texto));
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $texto)) {
            $texto = str_replace('.', '', $texto);
        }

        return is_numeric($texto) ? (float) $texto : null;
    }
}
