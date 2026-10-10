@props(['nome'])
{{-- Ícones de traço simples, sempre decorativos (o texto ao lado dá o nome). --}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes }}>
    @switch($nome)
        @case('painel')
            <rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/>
            @break
        @case('clientes')
            <circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17 14.2c2.6.2 4.5 2 4.5 5.3"/>
            @break
        @case('funil')
            <rect x="3" y="4" width="5" height="16" rx="1.5"/><rect x="9.5" y="4" width="5" height="10" rx="1.5"/><rect x="16" y="4" width="5" height="13" rx="1.5"/>
            @break
        @case('tarefas')
            <circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.8 2.8 5.7-5.8"/>
            @break
        @case('etiqueta')
            <path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V4a1 1 0 0 1 1-1h9l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="8" cy="8" r="1.3"/>
            @break
        @case('whatsapp')
            <path d="M3 21l1.6-4.9A8.5 8.5 0 1 1 8 19.4z"/><path d="M9.2 8.8c0 2.8 2.8 5.6 5.6 5.6l1-1.3-1.9-1-.9.7a3.8 3.8 0 0 1-1.7-1.7l.7-.9-1-1.9z"/>
            @break
        @case('importar')
            <path d="M12 4v12m0 0-4-4m4 4 4-4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>
            @break
        @case('exportar')
            <path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>
            @break
        @case('mais')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('ok')
            <path d="m5 12.5 4.5 4.5L19 7.5"/>
            @break
    @endswitch
</svg>
