<?php
$active = 'diagnostico';
$title = 'Diagnóstico';
require __DIR__.'/../partials/header.php';
require __DIR__.'/../partials/navbar.php';

$u = current_user();
$stats = stats_overview((int)$u['id']);
$evo   = evolution_by_group((int)$u['id']);

// NOVO: dados por área e disciplina (vem da tabela questions)
$diag_ts = diagnostico_timeseries((int)$u['id']);

// NOVO: radar detalhado (disciplina quando existe, senão área)
$radar_detalhado = radar_prioridades_detalhado((int)$u['id'], 24);

// Dropdown de grupos vindo do TS (mais correto)
$diag_groups = array_values(array_unique(array_map(fn($r) => $r['group_name'], $diag_ts)));
sort($diag_groups);

// Dropdown de dimensões: area|... e disc|...
$dimKeys = [];
foreach ($diag_ts as $r) {
  $k = $r['dim_type'].'|'.$r['dim'];
  $dimKeys[$k] = true;
}
$dimKeys = array_keys($dimKeys);
sort($dimKeys);

function dim_label(string $key): string {
  [$type, $name] = explode('|', $key, 2);
  return ($type === 'area' ? 'Área: ' : 'Disciplina: ') . $name;
}
?>
<div class="container py-4">
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <h4 class="fw-bold mb-1">Diagnóstico do aluno</h4>
              <div class="text-secondary">Evolução e dados consolidados dos seus simulados.</div>
            </div>
            <a class="btn btn-outline-primary rounded-pill" href="<?= e(url('simulados')) ?>">
              <span class="material-icons align-middle fs-6 me-1">assignment</span>
              Ver simulados
            </a>
          </div>

          <!-- NÃO MEXE -->
          <div class="row g-3 mt-2">
            <div class="col-12 col-md-3">
              <div class="kpi">
                <div class="kpi-label">Média geral</div>
                <div class="kpi-value"><?= e((string)$stats['media_geral']) ?>%</div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="kpi">
                <div class="kpi-label">Acertos conscientes</div>
                <div class="kpi-value"><?= e((string)$stats['acertos_conscientes']) ?></div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="kpi">
                <div class="kpi-label">Erros</div>
                <div class="kpi-value"><?= e((string)$stats['erros']) ?></div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="kpi">
                <div class="kpi-label">Acertos por chute</div>
                <div class="kpi-value"><?= e((string)$stats['chutes']) ?></div>
              </div>
            </div>
          </div>

          <!-- NÃO MEXE -->
          <div class="row g-3 mt-1">
            <div class="col-12 col-lg-6">
              <div class="card border-0 bg-light rounded-4">
                <div class="card-body">
                  <h6 class="fw-semibold mb-2">Acertos x Erros x Chutes</h6>
                  <div class="d-flex justify-center p-3 bg-white rounded-4">
                    <canvas class="chartPie" id="chartPie"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="card border-0 bg-light rounded-4">
                <div class="card-body">
                  <h6 class="fw-semibold mb-2">Evolução por grupo</h6>
                  <div class="p-3 bg-white rounded-4">
                    <canvas id="chartBars"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- EVOLUÇÃO + RADAR DETALHADO -->
          <div class="row g-3 mt-1">

            <!-- Evolução (linha) -->
            <div class="col-12 col-lg-8">
              <div class="card border-0 bg-light rounded-4">
                <div class="card-body">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <div>
                      <h6 class="fw-semibold mb-0">Evolução</h6>
                      <div class="text-secondary small">
                        Evolução considerando o total de questões dos simulados filtrados por grupo e área/disciplina.
                      </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <div class="d-flex align-items-center gap-2">
                        <span class="small text-secondary">Grupo</span>
                        <select class="form-select form-select-sm" id="diagGroupSelect" style="min-width:200px;">
                          <option value="__all">Todos</option>
                          <?php foreach ($diag_groups as $g): ?>
                            <option value="<?= e($g) ?>"><?= e($g) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <span class="small text-secondary">Disciplina/Área</span>
                        <select class="form-select form-select-sm" id="diagDimSelect" style="min-width:260px;">
                          <option value="__all">Tudo</option>
                          <?php foreach ($dimKeys as $k): ?>
                            <option value="<?= e($k) ?>"><?= e(dim_label($k)) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="p-3 bg-white rounded-4">
                    <canvas id="chartEvolucao"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Radar detalhado -->
            <div class="col-12 col-lg-4">
              <div class="card border-0 bg-light rounded-4 h-100">
                <div class="card-body">
                  <h6 class="fw-semibold mb-2">Radar de prioridades (detalhado)</h6>
                  <div class="small text-secondary mb-3">Erros + chutes por disciplina (ou área quando disciplina não foi informada).</div>

                  <?php if (empty($radar_detalhado)): ?>
                    <div class="empty-state small">
                      <span class="material-icons">radar</span>
                      <div class="mt-2">Sem dados ainda.</div>
                    </div>
                  <?php else: ?>
                    <div class="vstack gap-2 radar-scroll">
                      <?php foreach ($radar_detalhado as $r): ?>
                        <?php
                          $erros = (int)$r['erros'];
                          $chutes = (int)$r['chutes'];
                          $consc = (int)$r['conscientes'];
                          $total = (int)$r['total'];
                          $prio  = $erros + $chutes;
                          $pct   = $total > 0 ? round(($consc / $total) * 100, 1) : 0;
                        ?>
                        <div class="bg-white rounded-4 px-3 py-2">
                          <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="text-truncate">
                              <div class="fw-semibold small"><?= e($r['dim']) ?></div>
                              <div class="text-secondary small">
                                Total: <?= $total ?> · Conscientes: <?= $consc ?> · Acerto: <?= $pct ?>%
                              </div>
                              <div class="text-secondary small">
                                Erros: <?= $erros ?> · Chutes: <?= $chutes ?>
                              </div>
                            </div>
                            <div class="text-end">
                              <span class="badge bg-danger-subtle text-danger rounded-pill"><?= $prio ?></span>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

          </div>

          <script>
            window.__DIAG__ = <?= json_encode([
              'conscientes' => $stats['acertos_conscientes'],
              'erros' => $stats['erros'],
              'chutes' => $stats['chutes'],
              'evo' => $evo,
              'ts'  => $diag_ts
            ], JSON_UNESCAPED_UNICODE) ?>;
          </script>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__.'/../partials/footer.php'; ?>
