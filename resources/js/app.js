const semMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const anuncio = document.getElementById('anuncio');

function anunciar(texto) {
    if (!anuncio) return;
    anuncio.textContent = '';
    requestAnimationFrame(() => { anuncio.textContent = texto; });
}

function toastErro(texto) {
    const el = document.createElement('div');
    el.className = 'toast-erro';
    el.setAttribute('role', 'alert');
    el.textContent = texto;
    document.body.append(el);
    setTimeout(() => el.remove(), 5000);
}

// ---------- Confirmação de ações destrutivas: <form data-confirmar="mensagem"> ----------
const dialogo = document.getElementById('confirmar');
if (dialogo) {
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirmar]');
        if (!form || form.dataset.confirmado) return;
        e.preventDefault();
        document.getElementById('confirmar-texto').textContent = form.dataset.confirmar;
        dialogo.returnValue = '';
        dialogo.showModal();
        dialogo.addEventListener('close', () => {
            if (dialogo.returnValue === 'ok') {
                form.dataset.confirmado = '1';
                form.requestSubmit();
            }
        }, { once: true });
    });
}

// Em formulários com erro, o foco vai para o primeiro campo inválido.
document.querySelector('[aria-invalid="true"]')?.focus();

// ---------- Funil: arrastar e soltar (e seletor "Mover para", que serve ao teclado) ----------
const quadro = document.querySelector('[data-quadro]');
if (quadro) {
    const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    let arrastado = null;
    let origem = null;

    const atualizarTotais = () => {
        quadro.querySelectorAll('.coluna').forEach((coluna) => {
            const cartoes = coluna.querySelectorAll('.negocio');
            const total = [...cartoes].reduce((s, c) => s + Number(c.dataset.valor || 0), 0);
            coluna.querySelector('[data-qtd]').textContent = String(cartoes.length);
            coluna.querySelector('[data-total]').textContent = brl.format(total);
        });
    };

    async function mover(cartao, etapa, destinoEl, antes) {
        const colunaAntiga = cartao.closest('.coluna');
        if (colunaAntiga.dataset.etapa === etapa) return;
        const proximo = cartao.nextElementSibling;

        destinoEl.append(cartao);
        cartao.querySelector('select[name="etapa"]').value = etapa;
        atualizarTotais();

        try {
            const resposta = await fetch(cartao.dataset.url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ etapa }),
            });
            if (!resposta.ok) throw new Error('falha');
            const dados = await resposta.json();
            anunciar(`“${cartao.dataset.titulo}” foi para ${dados.rotulo}.`);
        } catch {
            // desfaz: devolve o cartão ao lugar de antes
            colunaAntiga.querySelector('.cartoes').insertBefore(cartao, proximo);
            cartao.querySelector('select[name="etapa"]').value = colunaAntiga.dataset.etapa;
            atualizarTotais();
            toastErro('Não foi possível mover o negócio. Tente de novo.');
        }
    }

    quadro.addEventListener('dragstart', (e) => {
        const cartao = e.target.closest('.negocio');
        if (!cartao) return;
        arrastado = cartao;
        origem = cartao.closest('.coluna');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', cartao.dataset.titulo);
        requestAnimationFrame(() => cartao.classList.add('arrastando'));
    });
    quadro.addEventListener('dragend', () => {
        arrastado?.classList.remove('arrastando');
        quadro.querySelectorAll('.alvo').forEach((c) => c.classList.remove('alvo'));
        arrastado = null;
    });
    quadro.addEventListener('dragover', (e) => {
        const coluna = e.target.closest('.coluna');
        if (!coluna || !arrastado) return;
        e.preventDefault();
        quadro.querySelectorAll('.alvo').forEach((c) => c !== coluna && c.classList.remove('alvo'));
        coluna.classList.add('alvo');
    });
    quadro.addEventListener('drop', (e) => {
        const coluna = e.target.closest('.coluna');
        if (!coluna || !arrastado) return;
        e.preventDefault();
        coluna.classList.remove('alvo');
        mover(arrastado, coluna.dataset.etapa, coluna.querySelector('.cartoes'));
    });

    // Seletor "Mover para…" + botão "Mover": o mesmo efeito do arrastar, sem mouse.
    quadro.addEventListener('submit', (e) => {
        const form = e.target.closest('form.mover');
        if (!form) return;
        e.preventDefault();
        const cartao = form.closest('.negocio');
        const etapa = form.querySelector('select[name="etapa"]').value;
        const coluna = quadro.querySelector(`.coluna[data-etapa="${etapa}"]`);
        mover(cartao, etapa, coluna.querySelector('.cartoes'));
    });
}

// ---------- Formulário de negócio / retorno: foco no primeiro campo ao abrir <details> ----------
document.querySelectorAll('details[data-foco]').forEach((d) => {
    d.addEventListener('toggle', () => { if (d.open) d.querySelector('input, select, textarea')?.focus(); });
});
