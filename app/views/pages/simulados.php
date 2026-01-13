<?php
$active = 'simulados';
$title = 'Simulados';
require __DIR__.'/../partials/header.php';
require __DIR__.'/../partials/navbar.php';

$u = current_user();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // ============================
  // CREATE SIMULADO
  // ============================
  if ($action === 'create_simulado') {

    // ✅ Normalização para evitar cair sempre no ENEM
    $mode = $_POST['template_mode'] ?? 'official';

    if ($mode === 'custom') {
      // quando custom, força template_type=custom (mesmo que o select oficial tenha sido disabled)
      $_POST['template_type'] = 'custom';
    } else {
      // official: se por algum motivo não veio, define default
      if (empty($_POST['template_type'])) {
        $_POST['template_type'] = 'enem';
      }
    }

    $res = simulado_create((int)$u['id'], $_POST);

    if ($res['ok']) {
      flash_set('success', 'Simulado criado! Abra o caderno para analisar.');
      redirect(url('caderno', ['id' => (int)$res['id']]));
    } else {
      flash_set('warning', $res['msg']);
      redirect(url('simulados'));
    }
  }

  // ============================
  // DELETE SIMULADO
  // ============================
  if ($action === 'delete_simulado') {
    $id = (int)($_POST['id'] ?? 0);
    $res = simulado_delete((int)$u['id'], $id);
    flash_set($res['ok'] ? 'success' : 'warning', $res['msg']);
    redirect(url('simulados'));
  }
}

$stats = stats_overview((int)$u['id']);
$simulados = simulados_for_user((int)$u['id']);

// group them
$byGroup = [];
foreach ($simulados as $s) {
  $g = $s['group_name'] ?: 'Geral';
  $byGroup[$g][] = $s;
}
?>
<div class="container py-4">
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <h4 class="fw-bold mb-1">Simulados</h4>
              <div class="text-secondary">Organize por grupos, acompanhe pendentes e conclua no caderno.</div>
            </div>
            <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCreateSimulado">
              <span class="material-icons align-middle fs-6 me-1">add</span>
              Criar simulado
            </button>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-12 col-md-4">
              <div class="kpi">
                <div class="kpi-label">Total de simulados</div>
                <div class="kpi-value"><?= e((string)$stats['total_simulados']) ?></div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="kpi">
                <div class="kpi-label">Concluídos</div>
                <div class="kpi-value"><?= e((string)$stats['concluidos']) ?></div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="kpi">
                <div class="kpi-label">Pendentes</div>
                <div class="kpi-value"><?= e((string)$stats['pendentes']) ?></div>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <?php if (empty($simulados)): ?>
              <div class="empty-state">
                <span class="material-icons">assignment</span>
                <div class="fw-semibold mt-2">Nenhum simulado cadastrado</div>
                <div class="text-secondary small">Clique em “Criar simulado” para começar.</div>
              </div>
            <?php else: ?>
              <?php foreach ($byGroup as $group => $items): ?>
                <div class="card border-0 bg-light rounded-4 mb-3">
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                      <div class="fw-semibold">
                        <span class="material-icons align-middle me-1 text-primary">folder</span>
                        <?= e($group) ?>
                      </div>
                      <span class="badge bg-primary-subtle text-primary rounded-pill">
                        <?= count($items) ?> simulados
                      </span>
                    </div>

                    <div class="list-group list-group-flush mt-2">
                      <?php foreach ($items as $s): ?>
                        <div class="list-group-item bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 rounded-3">
                          <div class="me-auto">
                            <div class="fw-semibold"><?= e($s['name']) ?></div>
                            <div class="small text-secondary">
                              Data: <?= e($s['applied_date']) ?>
                              · Template: <?= e(strtoupper($s['template_type'])) ?>
                              · Questões: <?= e((string)$s['total_questions']) ?>
                            </div>
                          </div>

                          <div class="d-flex align-items-center gap-2">
                            <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= e(url('caderno',['id'=>(int)$s['id']])) ?>">
                              <span class="material-icons align-middle">menu_book</span>
                              Caderno
                            </a>
                            <form method="post" class="m-0" onsubmit="return confirm('Excluir este simulado?');">
                              <input type="hidden" name="action" value="delete_simulado">
                              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                              <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">
                                <span class="material-icons align-middle">delete</span>
                              </button>
                            </form>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__.'/../partials/modal_create_simulado.php'; ?>
<?php require __DIR__.'/../partials/footer.php'; ?>
