async (page) => {
  await page.setViewportSize({ width: 1600, height: 900 });

  const coverHtml = `<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Projeto Fluxo de Caixa</title>
  <style>
    :root {
      --bg-1: #0f172a;
      --bg-2: #1d4ed8;
      --bg-3: #14b8a6;
      --ink: #e2e8f0;
      --muted: rgba(226, 232, 240, 0.74);
      --card: rgba(15, 23, 42, 0.46);
      --stroke: rgba(255, 255, 255, 0.14);
    }
    * { box-sizing: border-box; }
    html, body { width: 100%; height: 100%; margin: 0; }
    body {
      font-family: "Space Grotesk", system-ui, sans-serif;
      color: var(--ink);
      background:
        radial-gradient(900px 420px at 10% 15%, rgba(20, 184, 166, 0.35), transparent 58%),
        radial-gradient(700px 420px at 90% 12%, rgba(59, 130, 246, 0.38), transparent 54%),
        linear-gradient(135deg, var(--bg-1), #10203f 42%, #0f3f5c 100%);
      overflow: hidden;
    }
    .frame {
      width: 100%;
      height: 100%;
      padding: 56px;
    }
    .grid {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 28px;
      height: 100%;
    }
    .hero, .panel {
      border: 1px solid var(--stroke);
      border-radius: 28px;
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(16px);
      box-shadow: 0 30px 80px rgba(2, 6, 23, 0.32);
    }
    .hero {
      padding: 44px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      color: rgba(226, 232, 240, 0.76);
      font-size: 12px;
      margin-bottom: 16px;
    }
    .eyebrow::before {
      content: "";
      width: 34px;
      height: 2px;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--bg-3), #f59e0b);
      box-shadow: 0 0 20px rgba(20, 184, 166, 0.55);
    }
    h1 {
      margin: 0;
      font-size: 64px;
      line-height: 0.96;
      letter-spacing: -0.04em;
      max-width: 11ch;
    }
    .subtitle {
      max-width: 62ch;
      font-size: 19px;
      line-height: 1.55;
      color: var(--muted);
      margin-top: 18px;
    }
    .tags {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 26px;
    }
    .tag {
      padding: 10px 14px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.06);
      font-size: 14px;
      color: #f8fafc;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      margin-top: 32px;
    }
    .card {
      padding: 16px;
      border-radius: 20px;
      background: var(--card);
      border: 1px solid var(--stroke);
      min-height: 116px;
    }
    .card strong {
      display: block;
      font-size: 16px;
      margin-bottom: 8px;
    }
    .card span {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.45;
    }
    .panel {
      padding: 28px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .mini {
      height: 92px;
      border-radius: 22px;
      border: 1px solid rgba(255, 255, 255, 0.12);
      background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.06));
      position: relative;
      overflow: hidden;
    }
    .mini::after {
      content: "";
      position: absolute;
      inset: 16px;
      border-radius: 16px;
      background:
        linear-gradient(90deg, rgba(255, 255, 255, 0.18) 0 28%, transparent 28% 100%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.04));
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .status {
      margin-top: auto;
      padding: 18px 20px;
      border-radius: 22px;
      border: 1px solid rgba(255, 255, 255, 0.14);
      background: rgba(2, 6, 23, 0.34);
    }
    .status .label {
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: rgba(226, 232, 240, 0.68);
      font-size: 11px;
    }
    .status .value {
      margin-top: 8px;
      font-size: 28px;
      line-height: 1.15;
      font-weight: 700;
    }
    .status .note {
      margin-top: 8px;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.45;
    }
    .footer {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      align-items: center;
      margin-top: 18px;
      color: rgba(226, 232, 240, 0.68);
      font-size: 13px;
    }
    .bullet {
      display: inline-flex;
      gap: 8px;
      align-items: center;
    }
    .bullet i {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #34d399;
      box-shadow: 0 0 14px rgba(52, 211, 153, 0.75);
    }
  </style>
</head>
<body>
  <div class="frame">
    <div class="grid">
      <section class="hero">
        <div>
          <div class="eyebrow">Sistema web para pequeno comercio</div>
          <h1>Fluxo de Caixa</h1>
          <p class="subtitle">Plataforma desenvolvida em Laravel para centralizar operacao, estoque, compras, fechamento de caixa, relatorios e auditoria em uma unica interface.</p>
          <div class="tags">
            <span class="tag">Laravel 9</span>
            <span class="tag">PHP</span>
            <span class="tag">MySQL</span>
            <span class="tag">Blade</span>
            <span class="tag">Bootstrap</span>
            <span class="tag">Vite</span>
          </div>
          <div class="cards">
            <div class="card"><strong>Gestao completa</strong><span>Usuarios, produtos, estoque, lancamentos e configuracoes em um unico fluxo.</span></div>
            <div class="card"><strong>Controle financeiro</strong><span>Fechamento de caixa, relatorios por periodo e rastreabilidade das operacoes.</span></div>
            <div class="card"><strong>Perfil por acesso</strong><span>Experiencia separada para admin e funcionario com permissoes especificas.</span></div>
            <div class="card"><strong>Auditoria ativa</strong><span>Historico detalhado de tudo que os usuarios fazem no sistema.</span></div>
          </div>
        </div>
        <div class="footer">
          <span class="bullet"><i></i> Projeto pronto para apresentacao comercial</span>
          <span>Interface moderna e responsiva</span>
        </div>
      </section>
      <aside class="panel">
        <div class="mini"></div>
        <div class="mini"></div>
        <div class="mini"></div>
        <div class="status">
          <div class="label">Proposta de valor</div>
          <div class="value">Mais visibilidade<br/>para a rotina do caixa</div>
          <div class="note">O sistema entrega controle operacional, visao gerencial e suporte anual para pequeno comercio.</div>
        </div>
      </aside>
    </div>
  </div>
</body>
</html>`;

  await page.setContent(coverHtml, { waitUntil: 'load' });
  await page.screenshot({ path: 'linkedin-media/00-capa.png', fullPage: true });

  const pdfHtml = `<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Resumo do Projeto</title>
  <style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; width: 100%; }
    body {
      font-family: Arial, sans-serif;
      color: #0f172a;
      background: #f8fafc;
    }
    .page {
      width: 100%;
      min-height: 1123px;
      padding: 56px;
      page-break-after: always;
      background:
        radial-gradient(700px 280px at 100% 0%, rgba(37, 99, 235, 0.10), transparent 65%),
        radial-gradient(600px 260px at 0% 0%, rgba(20, 184, 166, 0.10), transparent 65%),
        #f8fafc;
    }
    .page:last-child { page-break-after: auto; }
    .kicker {
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 11px;
      color: #2563eb;
      margin-bottom: 12px;
      font-weight: 700;
    }
    h1 { margin: 0; font-size: 34px; line-height: 1.1; }
    h2 { margin: 0; font-size: 24px; }
    p { line-height: 1.55; color: #334155; }
    .lead { max-width: 64ch; font-size: 15px; }
    .grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-top: 28px; }
    .box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 20px;
      box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }
    .list { margin: 14px 0 0; padding: 0; list-style: none; }
    .list li {
      padding: 12px 0;
      border-top: 1px solid #e2e8f0;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }
    .dot {
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: linear-gradient(135deg, #2563eb, #14b8a6);
      margin-top: 6px;
      flex: 0 0 auto;
    }
    .pillrow { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
    .pill {
      padding: 9px 12px;
      border-radius: 999px;
      background: #eff6ff;
      border: 1px solid #dbeafe;
      color: #1d4ed8;
      font-size: 13px;
      font-weight: 700;
    }
    .feature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 18px; }
    .feature {
      padding: 16px;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      background: #ffffff;
    }
    .feature strong { display: block; margin-bottom: 6px; }
    .muted { color: #64748b; }
    .timeline { margin-top: 16px; display: grid; gap: 12px; }
    .step {
      padding: 14px 16px;
      border-radius: 16px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      display: grid;
      grid-template-columns: 24px 1fr;
      gap: 10px;
      align-items: start;
    }
    .num {
      width: 24px;
      height: 24px;
      border-radius: 999px;
      background: #0f172a;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 700;
    }
    .callout {
      margin-top: 18px;
      padding: 18px;
      border-radius: 18px;
      background: linear-gradient(135deg, #0f172a, #1d4ed8);
      color: #fff;
    }
    .callout p { color: rgba(255, 255, 255, 0.82); }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 16px; }
    .small { font-size: 13px; }
  </style>
</head>
<body>
  <section class="page">
    <div class="kicker">Projeto em destaque</div>
    <h1>Resumo executivo do Sistema Fluxo de Caixa</h1>
    <p class="lead">Sistema web desenvolvido para pequeno comercio com foco em controle operacional e financeiro. A solucao organiza o trabalho de admin e funcionario, reduz retrabalho e melhora a visibilidade sobre vendas, estoque, fechamento de caixa e auditoria.</p>
    <div class="pillrow">
      <span class="pill">Laravel 9</span>
      <span class="pill">PHP</span>
      <span class="pill">MySQL</span>
      <span class="pill">Blade</span>
      <span class="pill">Bootstrap</span>
      <span class="pill">Vite</span>
    </div>
    <div class="grid">
      <div class="box">
        <h2>O que o sistema entrega</h2>
        <ul class="list">
          <li><span class="dot"></span><span><strong>Dashboard gerencial</strong><br/><span class="muted">Indicadores de produtos, estoque, lancamentos, compras, fechamento e auditoria em uma so tela.</span></span></li>
          <li><span class="dot"></span><span><strong>Operacao diaria</strong><br/><span class="muted">Leitor de produtos, finalizacao de compra e historico do funcionario.</span></span></li>
          <li><span class="dot"></span><span><strong>Financeiro</strong><br/><span class="muted">Fechamento de caixa com fundo, dinheiro, cartao, PIX e outros valores.</span></span></li>
          <li><span class="dot"></span><span><strong>Rastreabilidade</strong><br/><span class="muted">Auditoria detalhada com data, hora, rota e descricao de cada acao.</span></span></li>
        </ul>
      </div>
      <div class="box">
        <h2>Competencias aplicadas</h2>
        <div class="feature-grid">
          <div class="feature"><strong>Backend</strong><span class="muted small">Modelagem, relacoes, regras de negocio e rotas protegidas.</span></div>
          <div class="feature"><strong>Frontend</strong><span class="muted small">Interfaces responsivas, layout moderno e experiencia fluida.</span></div>
          <div class="feature"><strong>Banco</strong><span class="muted small">Persistencia, relatorios e consolidacao de dados.</span></div>
          <div class="feature"><strong>Governanca</strong><span class="muted small">Auditoria, perfis e controle de acesso por papel.</span></div>
        </div>
        <div class="callout">
          <strong>Posicionamento comercial</strong>
          <p>Projeto indicado para venda como pacote fechado com implementacao, treinamento e suporte anual.</p>
        </div>
      </div>
    </div>
  </section>
  <section class="page">
    <div class="kicker">Escopo e entrega</div>
    <h1>Rotina de uso e valor para o cliente</h1>
    <div class="two-col">
      <div class="box">
        <h2>Modulos principais</h2>
        <div class="timeline">
          <div class="step"><div class="num">1</div><div><strong>Cadastros</strong><div class="muted small">Usuarios, produtos, tipos, centros de custo e configuracoes gerais.</div></div></div>
          <div class="step"><div class="num">2</div><div><strong>Operacao</strong><div class="muted small">Leitor de produtos, compra e historico da atividade do funcionario.</div></div></div>
          <div class="step"><div class="num">3</div><div><strong>Financeiro</strong><div class="muted small">Lancamentos, controle financeiro e fechamento de caixa.</div></div></div>
          <div class="step"><div class="num">4</div><div><strong>Gestao</strong><div class="muted small">Relatorios, auditoria, filtros e exportacao PDF/CSV.</div></div></div>
        </div>
      </div>
      <div class="box">
        <h2>Beneficios</h2>
        <ul class="list">
          <li><span class="dot"></span><span><strong>Mais controle</strong><br/><span class="muted">Centraliza os dados do caixa e reduz informacao dispersa.</span></span></li>
          <li><span class="dot"></span><span><strong>Mais visibilidade</strong><br/><span class="muted">Entrega leitura rapida do negocio para tomada de decisao.</span></span></li>
          <li><span class="dot"></span><span><strong>Mais seguranca</strong><br/><span class="muted">Registro de acoes dos usuarios e separacao por perfil.</span></span></li>
          <li><span class="dot"></span><span><strong>Mais agilidade</strong><br/><span class="muted">Fluxo simples para rotina de pequeno comercio.</span></span></li>
        </ul>
      </div>
    </div>
  </section>
  <section class="page">
    <div class="kicker">Entrega e suporte</div>
    <h1>Pacote comercial pronto para implantacao</h1>
    <div class="grid">
      <div class="box">
        <h2>Fases sugeridas</h2>
        <div class="timeline">
          <div class="step"><div class="num">1</div><div><strong>Implementacao</strong><div class="muted small">Configuracao do ambiente, banco, usuarios e dados iniciais.</div></div></div>
          <div class="step"><div class="num">2</div><div><strong>Treinamento</strong><div class="muted small">Onboarding com admin e equipe operacional.</div></div></div>
          <div class="step"><div class="num">3</div><div><strong>Go-live</strong><div class="muted small">Virada controlada com acompanhamento do cliente.</div></div></div>
          <div class="step"><div class="num">4</div><div><strong>Suporte</strong><div class="muted small">Manutencao corretiva e apoio por 12 meses.</div></div></div>
        </div>
      </div>
      <div class="box">
        <h2>O que apresentar no LinkedIn</h2>
        <ul class="list">
          <li><span class="dot"></span><span><strong>Capa do projeto</strong><br/><span class="muted">Uma imagem forte com o nome do sistema e a proposta de valor.</span></span></li>
          <li><span class="dot"></span><span><strong>Login e dashboard</strong><br/><span class="muted">Mostram a experiencia inicial e a visao geral do negocio.</span></span></li>
          <li><span class="dot"></span><span><strong>Modulos-chave</strong><br/><span class="muted">Produtos, financeiro, fechamento, relatorios e auditoria.</span></span></li>
          <li><span class="dot"></span><span><strong>Resumo em PDF</strong><br/><span class="muted">Documento curto para reforcar escopo, stack e entrega.</span></span></li>
        </ul>
        <div class="callout">
          <strong>Mensagem principal</strong>
          <p>Um sistema completo, orientado para pequena operacao, com foco em controle, organizacao e evolucao.</p>
        </div>
      </div>
    </div>
  </section>
</body>
</html>`;

  await page.setContent(pdfHtml, { waitUntil: 'load' });
  await page.pdf({
    path: 'linkedin-media/09-resumo-projeto.pdf',
    format: 'A4',
    printBackground: true,
    margin: { top: '0', right: '0', bottom: '0', left: '0' },
  });
}
