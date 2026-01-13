<?php
$active = '';
$title = 'Caderno do simulado';
require __DIR__.'/../partials/header.php';
require __DIR__.'/../partials/navbar.php';

$u = current_user();
$simId = (int)($_GET['id'] ?? 0);
$sim = simulado_get((int)$u['id'], $simId);
if (!$sim) {
  flash_set('warning', 'Simulado não encontrado.');
  redirect(url('simulados'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'update_question') {
    $qId = (int)($_POST['q_id'] ?? 0);
    $res = question_update((int)$u['id'], $simId, $qId, $_POST);
    flash_set($res['ok'] ? 'success' : 'warning', $res['msg']);
    redirect(url('caderno', ['id' => $simId]));
  }
}

$questions = questions_for_simulado($simId);

// progress calc
$total = count($questions);
$consc = 0; $err = 0; $chute = 0;
foreach ($questions as $q) {
  if ($q['status'] === 'consciente') $consc++;
  if ($q['status'] === 'erro') $err++;
  if ($q['status'] === 'chute') $chute++;
}
?>
<div class="container py-4">
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="text-secondary small">Caderno do simulado</div>
              <h4 class="fw-bold mb-0"><?= e($sim['name']) ?></h4>
              <div class="text-secondary small">Grupo: <?= e($sim['group_name']) ?> · Data: <?= e($sim['applied_date']) ?></div>
            </div>
            <div class="d-flex gap-2">
              <a class="btn btn-light rounded-pill" href="<?= e(url('simulados')) ?>">
                <span class="material-icons align-middle fs-6 me-1">arrow_back</span>
                Voltar
              </a>
              <a class="btn btn-primary rounded-pill" href="<?= e(url('diagnostico')) ?>">
                <span class="material-icons align-middle fs-6 me-1">save</span>
                Salvar e ver diagnóstico
              </a>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-12 col-lg-8">
              <?php
                // group by area_label
                $areas = [];
                foreach ($questions as $q) {
                  $areas[$q['area_label']][] = $q;
                }
                $areaIndex = 0;
              ?>
              <div class="accordion" id="areasAccordion">
                <?php foreach ($areas as $label => $qs): $areaIndex++; ?>
                  <div class="accordion-item rounded-4 overflow-hidden mb-2 border-0 shadow-sm">
                    <h2 class="accordion-header" id="heading<?= $areaIndex ?>">
                      <button class="accordion-button <?= $areaIndex===1?'':'collapsed' ?> fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $areaIndex ?>" aria-expanded="<?= $areaIndex===1?'true':'false' ?>" aria-controls="collapse<?= $areaIndex ?>">
                        <?= e($label) ?> · <?= count($qs) ?> questões
                      </button>
                    </h2>
                    <div id="collapse<?= $areaIndex ?>" class="accordion-collapse collapse <?= $areaIndex===1?'show':'' ?>" aria-labelledby="heading<?= $areaIndex ?>" data-bs-parent="#areasAccordion">
                      <div class="accordion-body">
                        <div class="row g-2">
                          <?php foreach ($qs as $q):
                            $cls = 'q-card q-ok';
                            if ($q['status'] === 'erro') $cls = 'q-card q-err';
                            if ($q['status'] === 'chute') $cls = 'q-card q-chute';
                          ?>
                            <div class="col-12 col-md-6">
                              <div class="<?= e($cls) ?>">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                  <div>
                                    <div class="fw-semibold">Questão <?= e((string)$q['q_number']) ?></div>
                                    <div class="small text-secondary">Status: <?= e($q['status']) ?></div>
                                  </div>

                                  <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-pill" data-bs-toggle="dropdown">
                                      <span class="material-icons fs-6">more_vert</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 rounded-4" style="min-width:320px;">
                                      <form method="post" class="vstack gap-2">
                                        <input type="hidden" name="action" value="update_question">
                                        <input type="hidden" name="q_id" value="<?= (int)$q['id'] ?>">

                                        <div>
                                          <label class="form-label small mb-1">Marcar como</label>
                                          <select name="status" class="form-select form-select-sm js-status-select" data-qid="<?= (int)$q['id'] ?>">
                                            <option value="consciente" <?= $q['status']==='consciente'?'selected':'' ?>>Acerto consciente (verde)</option>
                                            <option value="erro" <?= $q['status']==='erro'?'selected':'' ?>>Erro (vermelho)</option>
                                            <option value="chute" <?= $q['status']==='chute'?'selected':'' ?>>Acerto por chute (azul)</option>
                                          </select>
                                        </div>

                                        <div class="js-extra-fields <?= ($q['status']==='consciente')?'d-none':'' ?>">
                                          <div>
                                            <label class="form-label small mb-1">Disciplina</label>
                                            <input name="discipline" class="form-control form-control-sm" value="<?= e((string)($q['discipline'] ?? '')) ?>" placeholder="Ex: Matemática">
                                          </div>
                                          <div>
                                            <label class="form-label small mb-1">Motivo do erro/chute</label>
                                            <input name="reason" class="form-control form-control-sm" value="<?= e((string)($q['reason'] ?? '')) ?>" placeholder="Ex: Falta de atenção, conteúdo fraco...">
                                          </div>
                                        </div>

                                        <button class="btn btn-primary btn-sm rounded-pill mt-2" type="submit">
                                          <span class="material-icons align-middle fs-6 me-1">check</span>
                                          Salvar
                                        </button>
                                      </form>
                                    </div>
                                  </div>

                                </div>

                                <?php if ($q['status'] !== 'consciente'): ?>
                                  <div class="small mt-2">
                                    <span class="badge bg-dark-subtle text-dark rounded-pill"><?= e($q['discipline'] ?? '—') ?></span>
                                    <span class="text-secondary ms-1"><?= e($q['reason'] ?? '') ?></span>
                                  </div>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>

                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

            </div>

            <div class="col-12 col-lg-4">
              <div class="sticky-top" style="top:84px;">
                <div class="card border-0 bg-light rounded-4">
                  <div class="card-body">
                    <h6 class="fw-semibold mb-2">Progresso da análise</h6>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div class="small text-secondary">Pontuação total (acertos conscientes)</div>
                      <span class="badge bg-success rounded-pill"><?= e((string)$consc) ?>/<?= e((string)$total) ?></span>
                    </div>

                    <div class="progress rounded-pill mb-3" style="height:10px;">
                      <div class="progress-bar" role="progressbar" style="width: <?= $total>0?($consc/$total*100):0 ?>%;" aria-valuenow="<?= e((string)$consc) ?>" aria-valuemin="0" aria-valuemax="<?= e((string)$total) ?>"></div>
                    </div>

                    <div class="row g-2">
                      <div class="col-6">
                        <div class="mini-kpi bg-white rounded-4 p-3">
                          <div class="text-secondary small">Erros</div>
                          <div class="fw-bold"><?= e((string)$err) ?></div>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="mini-kpi bg-white rounded-4 p-3">
                          <div class="text-secondary small">Chutes</div>
                          <div class="fw-bold"><?= e((string)$chute) ?></div>
                        </div>
                      </div>
                    </div>

                    <hr class="my-3">

                    <div class="small text-secondary">
                      Dica: use o menu de cada questão para marcar <b>erro</b> ou <b>chute</b> e preencher disciplina + motivo.
                      Isso alimenta o radar e a tela de diagnóstico.
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__.'/../partials/footer.php'; ?>
