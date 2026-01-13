<?php
$templates = templates();
$disciplines = [
  'Português','Geografia','Física','Inglês','Educação Física',
  'Matemática','Filosofia','Química','Literatura','Língua Estrangeira',
  'História','Sociologia','Biologia','Artes','Tecnologias da Informação'
];
?>
<div class="modal fade" id="modalCreateSimulado" tabindex="-1" aria-labelledby="modalCreateSimuladoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered dp-modal-fixed">
    <form class="modal-content dp-modal border-0 shadow-lg" method="post" action="<?= e(url('simulados')) ?>">
      <input type="hidden" name="action" value="create_simulado">

      <!-- ✅ fonte de verdade do modo -->
      <input type="hidden" name="template_mode" id="templateMode" value="official">

      <!-- ✅ campo espelho: em modo custom forçamos template_type=custom -->
      <input type="hidden" name="template_type_custom" id="templateTypeCustom" value="custom">

      <!-- Header -->
      <div class="modal-header dp-modal__header border-0">
        <button type="button" class="btn dp-icon-btn" data-bs-dismiss="modal" aria-label="Voltar">
          <span class="material-icons">arrow_back</span>
        </button>

        <div class="ms-2">
          <div class="dp-modal__title" id="modalCreateSimuladoLabel">Criar Novo Simulado</div>
          <div class="dp-modal__subtitle">Configure seu simulado para análise do Método Clínico</div>
        </div>

        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <!-- Body -->
      <div class="modal-body dp-modal__body">
        <!-- INFORMAÇÕES BÁSICAS -->
        <div class="dp-section card border-0 mb-3">
          <div class="card-body">
            <div class="dp-section__head">
              <div class="dp-section__icon">
                <span class="material-icons">inventory_2</span>
              </div>
              <div>
                <div class="dp-section__title">Informações Básicas</div>
                <div class="dp-section__desc">Defina o nome, data e grupo do seu simulado</div>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-12 col-lg-6">
                <label class="form-label">Nome do Simulado <span class="text-danger">*</span></label>
                <input required name="name" class="form-control dp-input" placeholder="Ex: Simulado ENEM 1 - Junho/2024">
              </div>

              <div class="col-12 col-lg-6">
                <label class="form-label">Data de Aplicação</label>
                <div class="input-group">
                  <span class="input-group-text bg-white">
                    <span class="material-icons">calendar_month</span>
                  </span>
                  <input type="date" name="applied_date" class="form-control dp-input" placeholder="Selecione a data">
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Grupo (opcional)</label>
                <div class="d-flex gap-2">
                  <select name="group_name" id="groupSelectModal" class="form-select dp-input">
                    <option value="">Selecione um grupo</option>
                    <option value="ENEM 2026">ENEM 2026</option>
                    <option value="FUVEST 2026">FUVEST 2026</option>
                  </select>

                  <button type="button" class="btn dp-plus-btn" id="btnAddGroup" title="Adicionar grupo">
                    <span class="material-icons">add</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TIPO DE SIMULADO -->
        <div class="dp-section card border-0">
          <div class="card-body">
            <div class="dp-section__head">
              <div class="dp-section__icon">
                <span class="material-icons">tune</span>
              </div>
              <div>
                <div class="dp-section__title">Tipo de Simulado</div>
                <div class="dp-section__desc">Escolha entre um template oficial ou configure manualmente</div>
              </div>
            </div>

            <!-- Cards de escolha -->
            <div class="row g-3 mt-2">
              <div class="col-12 col-md-6">
                <button type="button" class="dp-choice active" data-mode="official" id="choiceOfficial">
                  <div class="dp-choice__icon dp-choice__icon--blue">
                    <span class="material-icons">menu_book</span>
                  </div>
                  <div class="dp-choice__content">
                    <div class="dp-choice__title">Template Oficial</div>
                    <div class="dp-choice__desc">ENEM, Fuvest, Unicamp, etc. com recorrência automática</div>
                  </div>
                </button>
              </div>

              <div class="col-12 col-md-6">
                <button type="button" class="dp-choice" data-mode="custom" id="choiceCustom">
                  <div class="dp-choice__icon dp-choice__icon--green">
                    <span class="material-icons">settings</span>
                  </div>
                  <div class="dp-choice__content">
                    <div class="dp-choice__title">Personalizado</div>
                    <div class="dp-choice__desc">Configure manualmente questões e disciplinas</div>
                  </div>
                </button>
              </div>
            </div>

            <!-- OFFICIAL BOX -->
            <div id="officialBox" class="dp-subbox mt-3">
              <label class="form-label mb-2">Escolha o template</label>

              <!-- ✅ importante: id pra JS -->
              <!-- este hidden é o que o backend vai usar sempre -->
              <input type="hidden" name="template_type" id="templateTypeHidden" value="enem">

              <!-- select oficial vira "template_type_select" (só pra UI) -->
              <select class="form-select dp-input" name="template_type_select" id="templateTypeSelect">

                <?php foreach ($templates as $key => $t): ?>
                  <option value="<?= e($key) ?>"><?= e($t['label']) ?></option>
                <?php endforeach; ?>
              </select>

              <div class="form-text mt-2">
                No protótipo: ENEM = 135 questões (Linguagens 1–45, Humanas 46–90, Natureza 91–135).
              </div>
            </div>

            <!-- CUSTOM BOX -->
            <div id="customBox" class="dp-subbox mt-3 d-none">
              <div class="row g-3">
                <div class="col-12 col-lg-4">
                  <label class="form-label">Total de Questões <span class="text-danger">*</span></label>
                  <input type="number" min="1" name="custom_total_questions" class="form-control dp-input" placeholder="Ex: 180" value="90">
                </div>

                <div class="col-12 col-lg-8">
                  <label class="form-label d-block">Opções</label>
                  <div class="dp-checkline">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="chkAreas" name="custom_dividir_areas" value="1">
                      <label class="form-check-label" for="chkAreas">Dividir por área do conhecimento</label>
                    </div>
                  </div>
                  <div class="dp-checkline">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="chkTwoDays" name="custom_dois_dias" value="1">
                      <label class="form-check-label" for="chkTwoDays">Simulado aplicado em dois dias</label>
                    </div>
                  </div>
                </div>

                <!-- ✅ Campo de numeração do dia 2 -->
                <div class="col-12 d-none" id="twoDaysStartWrap">
                  <label class="form-label small text-secondary mb-1">Numeração do segundo dia (início)</label>
                  <input type="number" min="1" name="custom_day2_start" class="form-control dp-input" placeholder="Ex: 91" value="91">
                </div>

                <!-- ✅ Seção Áreas (Dia 1 / Dia 2) -->
                <div class="col-12 d-none" id="areasWrap">

                  <div class="small text-uppercase text-secondary fw-semibold mb-2">Áreas do conhecimento</div>

                  <!-- Dia 1 -->
                  <div class="dp-area-day" id="day1Wrap">
                    <div class="small fw-semibold mb-2">Dia 1</div>

                    <div class="dp-area-list vstack gap-2" id="day1Areas">
                      <div class="card border-0 bg-light rounded-4 p-3 dp-area-item">
                        <div class="row g-2 align-items-center">
                          <div class="col-12 col-md-8">
                            <input class="form-control dp-input" name="day1_area_name[]" placeholder="Nome da área">
                          </div>
                          <div class="col-12 col-md-4">
                            <input class="form-control dp-input" name="day1_area_questions[]" placeholder="Questões" type="number" min="0">
                          </div>
                        </div>

                        <div class="mt-2 small fw-semibold">Disciplinas desta área:</div>
                        <div class="row g-2 mt-1">
                          <?php foreach ($disciplines as $d): ?>
                            <div class="col-6 col-md-4">
                              <label class="form-check small">
                                <input class="form-check-input" type="checkbox" name="day1_area_disciplines[0][]" value="<?= e($d) ?>">
                                <span class="form-check-label"><?= e($d) ?></span>
                              </label>
                            </div>
                          <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                          <button type="button" class="btn btn-sm btn-outline-danger rounded-pill dp-remove-area" title="Remover área">
                            <span class="material-icons align-middle fs-6">delete</span>
                          </button>
                        </div>
                      </div>
                    </div>

                    <button type="button" class="btn dp-addline-btn mt-2" id="btnAddAreaDay1">
                      <span class="material-icons">add</span>
                      Adicionar Área
                    </button>
                  </div>

                  <!-- Dia 2 -->
                  <div class="dp-area-day mt-3 d-none" id="day2Wrap">
                    <div class="small fw-semibold mb-2">Dia 2</div>

                    <div class="dp-area-list vstack gap-2" id="day2Areas"></div>

                    <button type="button" class="btn dp-addline-btn mt-2" id="btnAddAreaDay2">
                      <span class="material-icons">add</span>
                      Adicionar Área
                    </button>
                  </div>
                </div>

                <!-- Divisão por Disciplinas (se você quiser manter) -->
                <div class="col-12">
                  <div class="dp-divider-title">Divisão por Disciplinas</div>
                  <div class="small text-secondary mb-2">Disciplinas</div>

                  <div id="disciplineList" class="vstack gap-2">
                    <div class="dp-row">
                      <select class="form-select dp-input" name="disciplines[]">
                        <option value="">Disciplina</option>
                        <option>Matemática</option>
                        <option>Português</option>
                        <option>Física</option>
                        <option>Química</option>
                        <option>Biologia</option>
                        <option>História</option>
                        <option>Geografia</option>
                        <option>Inglês</option>
                        <option>Filosofia</option>
                        <option>Sociologia</option>
                      </select>

                      <input type="number" min="1" class="form-control dp-input" name="disc_start[]" placeholder="Início">
                      <input type="number" min="1" class="form-control dp-input" name="disc_end[]" placeholder="Fim">

                      <button type="button" class="btn dp-trash-btn" title="Remover linha" onclick="window.dpRemoveDisciplineRow(this)">
                        <span class="material-icons">delete</span>
                      </button>
                    </div>
                  </div>

                  <button type="button" class="btn dp-addline-btn mt-2" id="btnAddDiscipline">
                    <span class="material-icons">add</span>
                    Adicionar Disciplina
                  </button>

                  <input type="hidden" name="custom_areas_json" id="customAreasJson" value="">
                  <input type="hidden" name="custom_assuntos" id="customAssuntos" value="">
                </div>

              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer dp-modal__footer border-0">
        <button type="button" class="btn dp-foot-btn" data-bs-dismiss="modal">Cancelar</button>

        <button type="button" class="btn dp-foot-btn">
          <span class="material-icons">description</span>
          Salvar Template
        </button>

        <button type="submit" class="btn dp-foot-primary">
          <span class="material-icons">check</span>
          Criar Simulado
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // ===== Modal Criar Simulado: modo official/custom + campos condicionais =====
  (() => {
    const modeInput = document.getElementById('templateMode');
    const officialBox = document.getElementById('officialBox');
    const customBox = document.getElementById('customBox');
    const choiceOfficial = document.getElementById('choiceOfficial');
    const choiceCustom = document.getElementById('choiceCustom');

    const templateSelect = document.getElementById('templateTypeSelect'); // select oficial

    function setMode(mode){
      if (!modeInput || !officialBox || !customBox || !choiceOfficial || !choiceCustom) return;

      modeInput.value = mode;

      if (mode === 'custom'){
        choiceCustom.classList.add('active');
        choiceOfficial.classList.remove('active');
        customBox.classList.remove('d-none');
        officialBox.classList.add('d-none');

        // ✅ força template_type=custom (evita cair no ENEM)
        if (templateSelect) templateSelect.value = 'custom';

        // ✅ desabilita campos oficiais
        if (templateSelect) templateSelect.disabled = true;

        // ✅ habilita campos do custom
        customBox.querySelectorAll('input,select,textarea,button').forEach(el => {
          if (el.closest('#customBox')) el.disabled = false;
        });

      } else {
        choiceOfficial.classList.add('active');
        choiceCustom.classList.remove('active');
        officialBox.classList.remove('d-none');
        customBox.classList.add('d-none');

        // ✅ habilita select oficial
        if (templateSelect) templateSelect.disabled = false;

        // ✅ desabilita inputs do custom (pra não “poluir” o POST)
        customBox.querySelectorAll('input,select,textarea,button').forEach(el => {
          if (el.id === 'choiceCustom' || el.id === 'choiceOfficial') return;
          // não desabilita botões do header/footer
          el.disabled = true;
        });
      }
    }

    if (choiceOfficial) choiceOfficial.addEventListener('click', () => setMode('official'));
    if (choiceCustom) choiceCustom.addEventListener('click', () => setMode('custom'));

    // ===== Custom: dividir por áreas / dois dias =====
    const chkAreas = document.getElementById('chkAreas');
    const chkTwoDays = document.getElementById('chkTwoDays');
    const areasWrap = document.getElementById('areasWrap');
    const day2Wrap = document.getElementById('day2Wrap');
    const twoDaysStartWrap = document.getElementById('twoDaysStartWrap');

    const day1Areas = document.getElementById('day1Areas');
    const day2Areas = document.getElementById('day2Areas');
    const btnAdd1 = document.getElementById('btnAddAreaDay1');
    const btnAdd2 = document.getElementById('btnAddAreaDay2');

    function syncCustomVisibility(){
      if (!chkAreas || !chkTwoDays || !areasWrap || !day2Wrap || !twoDaysStartWrap) return;

      const showAreas = chkAreas.checked;
      areasWrap.classList.toggle('d-none', !showAreas);

      const twoDays = chkTwoDays.checked;
      twoDaysStartWrap.classList.toggle('d-none', !twoDays);

      // dia 2 aparece somente se dividir áreas + dois dias
      day2Wrap.classList.toggle('d-none', !(showAreas && twoDays));
    }

    function reindexDisc(listEl, dayKey){
      const items = Array.from(listEl.querySelectorAll('.dp-area-item'));
      items.forEach((item, idx) => {
        item.querySelectorAll('input[type="checkbox"][name^="'+dayKey+'_area_disciplines"]').forEach(cb => {
          cb.name = `${dayKey}_area_disciplines[${idx}][]`;
        });
      });
    }

    function cloneArea(fromList, toList, dayKey){
      const first = fromList.querySelector('.dp-area-item');
      if (!first) return;

      const clone = first.cloneNode(true);
      clone.querySelectorAll('input[type="text"], input[type="number"]').forEach(i => i.value = '');
      clone.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
      toList.appendChild(clone);
      reindexDisc(toList, dayKey);
    }

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.dp-remove-area');
      if (!btn) return;
      const item = btn.closest('.dp-area-item');
      if (!item) return;

      const list = item.parentElement;
      item.remove();

      if (list && list.id === 'day1Areas') reindexDisc(list, 'day1');
      if (list && list.id === 'day2Areas') reindexDisc(list, 'day2');
    });

    if (btnAdd1 && day1Areas) btnAdd1.addEventListener('click', () => cloneArea(day1Areas, day1Areas, 'day1'));
    if (btnAdd2 && day2Areas) btnAdd2.addEventListener('click', () => {
      if (day2Areas.querySelectorAll('.dp-area-item').length === 0) {
        cloneArea(day1Areas, day2Areas, 'day2');
      } else {
        cloneArea(day2Areas, day2Areas, 'day2');
      }
    });

    if (chkAreas) chkAreas.addEventListener('change', syncCustomVisibility);
    if (chkTwoDays) chkTwoDays.addEventListener('change', syncCustomVisibility);

    // default
    setMode('official');
    syncCustomVisibility();
  })();
</script>
