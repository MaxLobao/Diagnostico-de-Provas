<?php $title = 'Diagnóstico de Provas 7.1'; require __DIR__.'/../partials/header.php'; ?>
<header class="hero">
  <div class="container py-5">
    <div class="row align-items-center g-4">
      <div class="col-12 col-lg-7">
        <div class="badge bg-primary-subtle text-primary fw-semibold rounded-pill px-3 py-2 mb-3">
          Método baseado em prática + análise + priorização
        </div>
        <h1 class="display-5 fw-bold mb-3">
          Transforme seus simulados em um <span class="text-primary">plano de estudos</span> claro.
        </h1>
        <p class="lead text-secondary mb-4">
          O Diagnóstico de Provas 7.1 organiza seus simulados, identifica onde você mais erra (e onde chuta),
          e cria um radar de prioridades para você evoluir com consistência.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= e(url('auth')) ?>" class="btn btn-primary btn-lg rounded-pill px-4">
            <span class="material-icons align-middle me-1">login</span>
            Login
          </a>
          <a href="<?= e(url('auth', ['tab'=>'register'])) ?>" class="btn btn-outline-primary btn-lg rounded-pill px-4">
            <span class="material-icons align-middle me-1">person_add</span>
            Cadastre-se
          </a>
        </div>
        <div class="mt-4 small text-secondary">
          Protótipo em PHP + Bootstrap 5. Vamos ajustando juntos conforme você for refinando o sistema.
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0 rounded-5 p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="icon-pill"><span class="material-icons">insights</span></div>
            <div>
              <div class="fw-semibold">Radar de prioridades</div>
              <div class="text-secondary small">Veja o que atacar primeiro.</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="icon-pill"><span class="material-icons">timeline</span></div>
            <div>
              <div class="fw-semibold">Evolução por grupo</div>
              <div class="text-secondary small">Acompanhe seu avanço por ENEM/UFV/…</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="icon-pill"><span class="material-icons">checklist</span></div>
            <div>
              <div class="fw-semibold">Caderno do simulado</div>
              <div class="text-secondary small">Marque erro/chute e gere estatísticas.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<section class="py-5">
  <div class="container">
    <div class="row g-3">
      <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-primary mb-2"><span class="material-icons">school</span></div>
            <h5 class="fw-semibold">Simulado → Diagnóstico</h5>
            <p class="text-secondary mb-0">Você faz a prova, depois analisa e o sistema te mostra onde focar.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-primary mb-2"><span class="material-icons">bolt</span></div>
            <h5 class="fw-semibold">Prioridade real</h5>
            <p class="text-secondary mb-0">Erros e chutes viram ranking de disciplinas e motivos.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-primary mb-2"><span class="material-icons">verified</span></div>
            <h5 class="fw-semibold">Evolução</h5>
            <p class="text-secondary mb-0">Acompanhe média e volume de simulados por grupo ao longo do tempo.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__.'/../partials/footer.php'; ?>
