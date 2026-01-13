<?php
$active = 'dashboard';
$title = 'Dashboard';
require __DIR__.'/../partials/header.php';
require __DIR__.'/../partials/navbar.php';

$u = current_user();
$stats = stats_overview((int)$u['id']);
$simulados = simulados_for_user((int)$u['id']);
$radar = radar_prioridades((int)$u['id']);
$evo = evolution_by_group((int)$u['id']);

$groups = array_map(fn($r) => $r['group_name'], $evo);
$media = array_map(fn($r) => $r['media'], $evo);
$qtSim = array_map(fn($r) => (int)$r['simulados'], $evo);
?>
<div class="container py-4">
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 sticky-dashboard-header">
            <div class="d-flex align-items-center gap-3">
              <button type="button" class="dp-avatar-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#modalAvatarPicker"
                  title="Trocar foto do perfil"
                >
                  <img src="/<?= e($u['avatar_path'] ?? 'assets/img/avatar.png') ?>" alt="avatar" class="avatar-lg">
                  <span class="dp-avatar-badge">
                    <span class="material-icons">edit</span>
                  </span>
                </button>

              <div>
                <div class="text-secondary small">Bem-vindo,</div>
                <div class="h5 mb-0 fw-semibold"><?= e($u['name']) ?></div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <div class="stat-pill">
                <div class="label">Média geral</div>
                <div class="value"><?= e((string)$stats['media_geral']) ?>%</div>
              </div>
              <div class="stat-pill">
                <div class="label">Simulados feitos</div>
                <div class="value"><?= e((string)$stats['total_simulados']) ?></div>
              </div>
              <div class="stat-pill">
                <div class="label">Questões certas</div>
                <div class="value"><?= e((string)$stats['acertos_conscientes']) ?></div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-12 col-lg-8">
              <div class="card border-0 bg-light rounded-4 h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-semibold mb-0">Meus simulados</h6>
                    <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCreateSimulado">
                      <span class="material-icons align-middle fs-6 me-1">add</span>
                      Criar simulado
                    </button>
                  </div>

                  <?php if (empty($simulados)): ?>
                    <div class="empty-state">
                      <span class="material-icons">playlist_add</span>
                      <div class="fw-semibold mt-2">Nenhum simulado ainda</div>
                      <div class="text-secondary small">Crie o primeiro para começar a gerar diagnóstico.</div>
                    </div>
                  <?php else: ?>
                    <div class="list-group list-group-flush radar-scroll gap-8">
                      <?php foreach ($simulados as $s): ?>
                        <a class="list-group-item list-group-item-action d-flex flex-wrap gap-2 justify-content-between align-items-center rounded-3"
                           href="<?= e(url('caderno', ['id'=>(int)$s['id']])) ?>">
                          <div>
                            <div class="fw-semibold"><?= e($s['name']) ?></div>
                            <div class="small text-secondary">
                              Grupo: <?= e($s['group_name']) ?> · Data: <?= e($s['applied_date']) ?>
                            </div>
                          </div>
                          <span class="badge <?= $s['status']==='concluido'?'bg-success':'bg-warning text-dark' ?> rounded-pill px-3">
                            <?= e($s['status']) ?>
                          </span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4">
              <div class="card border-0 bg-light rounded-4 h-100">
                <div class="card-body">
                  <h6 class="fw-semibold mb-0">Radar de prioridades</h6>
                  <div class="small text-secondary mb-2">Ranking por disciplinas com mais erro/chute.</div>

                  <?php if (empty($radar)): ?>
                    <div class="empty-state small">
                      <span class="material-icons">radar</span>
                      <div class="mt-2">Sem dados ainda.</div>
                      <div class="text-secondary">Analise um simulado no caderno.</div>
                    </div>
                  <?php else: ?>
                    <div class="vstack gap-2 radar-scroll">
                      <?php foreach ($radar as $r): $score = ((int)$r['erros'] + (int)$r['chutes']); ?>
                        <div class="d-flex align-items-center justify-content-between bg-white rounded-4 px-3 py-2">
                          <div class="text-truncate">
                            <div class="fw-semibold small"><?= e($r['discipline']) ?></div>
                            <div class="text-secondary small">Erros: <?= e((string)$r['erros']) ?> · Chutes: <?= e((string)$r['chutes']) ?></div>
                          </div>
                          <span class="badge bg-danger-subtle text-danger rounded-pill"><?= e((string)$score) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>

          <div class="row g-3 mt-1">
            <div class="col-12">
              <div class="card border-0 bg-light rounded-4">
                <div class="card-body">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <div>
                      <h6 class="fw-semibold mb-0">Evolução</h6>
                      <div class="text-secondary small">Resumo da tela de diagnóstico por grupo.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <label class="small text-secondary mb-0">Grupo</label>
                      <select class="form-select form-select-sm" id="groupSelect" style="min-width:220px;">
                        <option value="__all">Todos</option>
                        <?php foreach ($groups as $g): ?>
                          <option value="<?= e($g) ?>"><?= e($g) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div class="col-12 col-lg-6">
                      <div class="p-3 bg-white rounded-4">
                        <canvas id="chartMedia"></canvas>
                      </div>
                    </div>
                    <div class="col-12 col-lg-6">
                      <div class="p-3 bg-white rounded-4">
                        <canvas id="chartSimulados"></canvas>
                      </div>
                    </div>
                  </div>

                  <script>
                    window.__EVOLUTION__ = <?= json_encode($evo, JSON_UNESCAPED_UNICODE) ?>;
                  </script>

                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__.'/../partials/modal_avatar_picker.php'; ?>
<?php require __DIR__.'/../partials/modal_create_simulado.php'; ?>
<?php require __DIR__.'/../partials/footer.php'; ?>
