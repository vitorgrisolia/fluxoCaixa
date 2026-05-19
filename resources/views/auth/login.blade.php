<x-guest-layout>
    <div class="login-layout">
        <section class="login-hero" aria-hidden="true">
            <p class="login-eyebrow">Fluxo de Caixa</p>
            <h1 class="login-title">Seu caixa no controle, todos os dias.</h1>
            <p class="login-subtitle">
                Acompanhe compras, estoque, lancamentos, fechamento de caixa e auditoria em um unico painel.
            </p>

            <div class="login-point-list">
                <article class="login-point">
                    <h2>Gestao rapida</h2>
                    <p>Resumo do que importa logo na entrada do sistema.</p>
                </article>
                <article class="login-point">
                    <h2>Operacao segura</h2>
                    <p>Auditoria completa para saber quem fez o que e quando.</p>
                </article>
                <article class="login-point">
                    <h2>Financeiro visivel</h2>
                    <p>Relatorios por periodo, centro, tipo e fechamento de caixa.</p>
                </article>
            </div>

            <div class="login-live-card">
                <span class="login-live-label">Dica dinamica</span>
                <strong id="login-dynamic-text">Use filtros de periodo para analisar melhor seus resultados.</strong>
                <span class="login-live-time" id="login-live-time"></span>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-panel-head">
                <img src="{{ asset('img/logo-grisolia.jpg') }}" alt="Logo do sistema" class="login-logo">
                <div>
                    <p class="login-panel-eyebrow">Acesso seguro</p>
                    <h2 class="login-panel-title">Entrar no sistema</h2>
                </div>
            </div>

            @if (session('status'))
                <div class="login-alert login-alert--success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="login-alert login-alert--error">
                    <strong>Confira os dados informados:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form" id="login-form">
                @csrf

                <div class="login-field">
                    <label for="email" class="login-label">E-mail</label>
                    <input
                        id="email"
                        class="login-input"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="seuemail@empresa.com"
                        required
                        autofocus
                    >
                </div>

                <div class="login-field">
                    <label for="password" class="login-label">Senha</label>
                    <div class="login-password-wrap">
                        <input
                            id="password"
                            class="login-input login-input--password"
                            type="password"
                            name="password"
                            placeholder="Digite sua senha"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="login-password-toggle" data-password-toggle aria-label="Mostrar senha">
                            Mostrar
                        </button>
                    </div>
                    <span class="login-hint" id="caps-lock-hint" hidden>Caps Lock ativado.</span>
                </div>

                <div class="login-row">
                    <label for="remember_me" class="login-remember">
                        <input id="remember_me" type="checkbox" class="login-checkbox" name="remember">
                        <span>Lembrar-me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="login-link" href="{{ route('password.request') }}">
                            Esqueceu sua senha?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-submit" id="login-submit">
                    <span class="login-submit__text">Acessar painel</span>
                    <span class="login-submit__spinner" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var frases = [
                'Use filtros de periodo para analisar melhor seus resultados.',
                'Confira os fechamentos de caixa para validar o saldo final.',
                'A auditoria ajuda a rastrear todas as alteracoes do sistema.'
            ];
            var fraseAtual = 0;
            var fraseElemento = document.getElementById('login-dynamic-text');
            var horaElemento = document.getElementById('login-live-time');
            var senha = document.getElementById('password');
            var toggleSenha = document.querySelector('[data-password-toggle]');
            var capsLockHint = document.getElementById('caps-lock-hint');
            var form = document.getElementById('login-form');
            var submitButton = document.getElementById('login-submit');

            if (fraseElemento) {
                setInterval(function () {
                    fraseElemento.classList.add('is-fading');
                    setTimeout(function () {
                        fraseAtual = (fraseAtual + 1) % frases.length;
                        fraseElemento.textContent = frases[fraseAtual];
                        fraseElemento.classList.remove('is-fading');
                    }, 160);
                }, 4200);
            }

            if (horaElemento) {
                var atualizaHora = function () {
                    var agora = new Date();
                    horaElemento.textContent = agora.toLocaleString('pt-BR', {
                        dateStyle: 'short',
                        timeStyle: 'short'
                    });
                };

                atualizaHora();
                setInterval(atualizaHora, 30000);
            }

            if (toggleSenha && senha) {
                toggleSenha.addEventListener('click', function () {
                    var exibir = senha.type === 'password';
                    senha.type = exibir ? 'text' : 'password';
                    toggleSenha.textContent = exibir ? 'Ocultar' : 'Mostrar';
                    toggleSenha.setAttribute('aria-label', exibir ? 'Ocultar senha' : 'Mostrar senha');
                    senha.focus();
                });
            }

            if (senha && capsLockHint) {
                var verificaCapsLock = function (evento) {
                    if (typeof evento.getModifierState !== 'function') {
                        return;
                    }

                    capsLockHint.hidden = !evento.getModifierState('CapsLock');
                };

                senha.addEventListener('keydown', verificaCapsLock);
                senha.addEventListener('keyup', verificaCapsLock);
                senha.addEventListener('blur', function () {
                    capsLockHint.hidden = true;
                });
            }

            if (form && submitButton) {
                form.addEventListener('submit', function () {
                    submitButton.classList.add('is-loading');
                    submitButton.disabled = true;
                });
            }
        });
    </script>
</x-guest-layout>
