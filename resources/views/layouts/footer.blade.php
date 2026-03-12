<footer class="app-footer">
    <div class="app-footer__content">
        <span class="app-footer__brand">
            {{ $configuracaoSistema->nome_sistema ?? config('app.name', 'FluxoCaixa') }}
        </span>
        <span class="app-footer__meta">
            @if(!empty($configuracaoSistema?->mensagem_rodape))
                {{ $configuracaoSistema->mensagem_rodape }}
            @else
                &copy; {{ date('l ,d/m/Y') }}. Todos os direitos reservados.
            @endif
        </span>
    </div>
</footer>
