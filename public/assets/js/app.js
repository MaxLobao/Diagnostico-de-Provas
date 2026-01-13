(() => {
  // Bootstrap validation (used in auth)
  // Charts + evolution filters
  function safeGet(id){ return document.getElementById(id); }

  const evo = window.__EVOLUTION__ || null;
  if (evo && safeGet('chartMedia') && safeGet('chartSimulados')) {
    const ctx1 = safeGet('chartMedia');
    const ctx2 = safeGet('chartSimulados');

    const makeData = (rows) => ({
      labels: rows.map(r => r.group_name),
      medias: rows.map(r => Number(r.media || 0)),
      simulados: rows.map(r => Number(r.simulados || 0))
    });

    let current = makeData(evo);

    const chartMedia = new Chart(ctx1, {
      type: 'line',
      data: { labels: current.labels, datasets: [{ label: 'Média de acerto (%)', data: current.medias }] },
      options: { responsive:true, plugins:{ legend:{ display:true } }, scales:{ y:{ beginAtZero:true, max:100 } } }
    });

    const chartSim = new Chart(ctx2, {
      type: 'bar',
      data: { labels: current.labels, datasets: [{ label: 'Simulados feitos', data: current.simulados }] },
      options: { responsive:true, plugins:{ legend:{ display:true } }, scales:{ y:{ beginAtZero:true } } }
    });

    const groupSelect = safeGet('groupSelect');
    if (groupSelect) {
      groupSelect.addEventListener('change', () => {
        const v = groupSelect.value;
        const rows = (v === '__all') ? evo : evo.filter(r => r.group_name === v);
        const d = makeData(rows);
        chartMedia.data.labels = d.labels;
        chartMedia.data.datasets[0].data = d.medias;
        chartMedia.update();

        chartSim.data.labels = d.labels;
        chartSim.data.datasets[0].data = d.simulados;
        chartSim.update();
      });
    }
  }

  const diag = window.__DIAG__ || null;
  if (diag && safeGet('chartPie') && safeGet('chartBars')) {
    const ctxPie = safeGet('chartPie');
    const ctxBars = safeGet('chartBars');

    new Chart(ctxPie, {
      type: 'doughnut',
      data: {
        labels: ['Acertos conscientes', 'Erros', 'Acertos por chute'],
        datasets: [{ data: [Number(diag.conscientes||0), Number(diag.erros||0), Number(diag.chutes||0)] }]
      },
      options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
    });

    const evo = diag.evo || [];
    new Chart(ctxBars, {
      type: 'bar',
      data: {
        labels: evo.map(r => r.group_name),
        datasets: [{ label: 'Média (%)', data: evo.map(r => Number(r.media||0)) }]
      },
      options: { responsive:true, scales:{ y:{ beginAtZero:true, max:100 } } }
    });
  }

  // Modal custom toggle
  const templateSelect = document.getElementById('templateTypeSelect');
  const customBox = document.getElementById('customBox');
  if (templateSelect && customBox) {
    const sync = () => {
      if (templateSelect.value === 'custom') customBox.classList.remove('d-none');
      else customBox.classList.add('d-none');
    };
    templateSelect.addEventListener('change', sync);
    sync();
  }

  // Caderno: show/hide extra fields based on status selection inside dropdown
  document.addEventListener('change', (e) => {
    const el = e.target;
    if (!(el instanceof HTMLElement)) return;

    if (el.classList.contains('js-status-select')) {
      const menu = el.closest('.dropdown-menu');
      if (!menu) return;
      const extra = menu.querySelector('.js-extra-fields');
      if (!extra) return;

      if (el.value === 'consciente') extra.classList.add('d-none');
      else extra.classList.remove('d-none');
    }
  });

})(); 

// ===== Modal Criar Simulado (layout novo) =====
(() => {
  const modeInput = document.getElementById('templateMode');
  const officialBox = document.getElementById('officialBox');
  const customBox = document.getElementById('customBox');
  const choiceOfficial = document.getElementById('choiceOfficial');
  const choiceCustom = document.getElementById('choiceCustom');

  function setMode(mode){
  if (!modeInput || !officialBox || !customBox || !choiceOfficial || !choiceCustom) return;

  modeInput.value = mode;

  const templateSelect = document.getElementById('templateTypeSelect'); // oficial
  const hiddenTemplate = document.querySelector('input[name="template_type_hidden"]'); // opcional (se existir)

  if (mode === 'custom'){
    choiceCustom.classList.add('active');
    choiceOfficial.classList.remove('active');
    customBox.classList.remove('d-none');
    officialBox.classList.add('d-none');

    // ✅ força o backend a entender "custom"
    if (templateSelect) templateSelect.value = 'custom';

    // se você usa hidden (melhor), também seta nele:
    if (hiddenTemplate) hiddenTemplate.value = 'custom';
  } else {
    choiceOfficial.classList.add('active');
    choiceCustom.classList.remove('active');
    officialBox.classList.remove('d-none');
    customBox.classList.add('d-none');

    // ✅ garante que o oficial vai como enem/fuvest/unicamp (não "custom")
    if (templateSelect && templateSelect.value === 'custom') templateSelect.value = 'enem';
    if (hiddenTemplate && hiddenTemplate.value === 'custom') hiddenTemplate.value = 'enem';
  }
}


  // Add discipline row
  const btnAddDiscipline = document.getElementById('btnAddDiscipline');
  const disciplineList = document.getElementById('disciplineList');

  window.dpRemoveDisciplineRow = (btn) => {
    const row = btn.closest('.dp-row');
    if (!row) return;
    if (disciplineList && disciplineList.querySelectorAll('.dp-row').length <= 1) {
      // mantém pelo menos uma linha
      row.querySelectorAll('input,select').forEach(el => el.value = '');
      return;
    }
    row.remove();
  };

  if (btnAddDiscipline && disciplineList) {
    btnAddDiscipline.addEventListener('click', () => {
      const first = disciplineList.querySelector('.dp-row');
      if (!first) return;

      const clone = first.cloneNode(true);
      clone.querySelectorAll('input').forEach(i => i.value = '');
      clone.querySelectorAll('select').forEach(s => s.value = '');
      disciplineList.appendChild(clone);
    });
  }

  // Add group (quick prompt)
  const btnAddGroup = document.getElementById('btnAddGroup');
  const groupSelect = document.getElementById('groupSelectModal');

  if (btnAddGroup && groupSelect) {
    btnAddGroup.addEventListener('click', () => {
      const name = prompt('Nome do novo grupo (ex: ENEM 2026 / FUVEST 2026):');
      if (!name) return;

      const opt = document.createElement('option');
      opt.value = name.trim();
      opt.textContent = name.trim();
      groupSelect.appendChild(opt);
      groupSelect.value = name.trim();
    });
  }

  // default
  setMode('official');
})();

// ===== Toast global auto-init =====
(() => {
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, {
      delay: 4000,
      autohide: true
    });
    toast.show();
  });
})();


(() => {
  const d = window.__DIAG__;
  const canvas = document.getElementById('chartEvolucao');
  if (!d || !canvas) return;

  const ts = d.ts || [];
  const selGroup = document.getElementById('diagGroupSelect');
  const selDim = document.getElementById('diagDimSelect');

  let chart;

  function filterRows() {
    const g = selGroup ? selGroup.value : '__all';
    const k = selDim ? selDim.value : '__all'; // "__all" ou "area|..." ou "disc|..."

    return ts.filter(r => {
      const okG = (g === '__all' || r.group_name === g);
      const okD = (k === '__all' || (r.dim_type + '|' + r.dim) === k);
      return okG && okD;
    });
  }

  function buildPoints(rows) {
    // 1 ponto por simulado
    const bySim = new Map();
    rows.forEach(r => {
      const key = String(r.simulado_id);
      const cur = bySim.get(key) || {
        simulado_id: r.simulado_id,
        date: r.date,
        name: r.simulado_name,
        total: 0,
        correct: 0
      };
      cur.total += Number(r.total || 0);
      cur.correct += Number(r.correct || 0);
      bySim.set(key, cur);
    });

    const points = Array.from(bySim.values()).sort((a,b) => {
      const c = String(a.date).localeCompare(String(b.date));
      if (c !== 0) return c;
      return Number(a.simulado_id) - Number(b.simulado_id);
    });

    const labels = points.map(p => `${p.date} • ${p.name}`);
    const values = points.map(p => p.total > 0 ? Number(((p.correct / p.total) * 100).toFixed(1)) : 0);

    return { labels, values };
  }

  function render() {
    const rows = filterRows();
    const { labels, values } = buildPoints(rows);

    const ctx = canvas.getContext('2d');
    if (chart) chart.destroy();

    chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Taxa de acerto (%)',
          data: values,
          tension: 0.35,
          borderWidth: 2,
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
        }
      }
    });
  }

  if (selGroup) selGroup.addEventListener('change', render);
  if (selDim) selDim.addEventListener('change', render);
  render();
})();

// ===== Avatar Picker =====
(() => {
  const modal = document.getElementById('modalAvatarPicker');
  if (!modal) return;

  const input = modal.querySelector('#avatar_file');
  const options = modal.querySelectorAll('.dp-avatar-option');

  if (!input || !options.length) return;

  options.forEach(btn => {
    btn.addEventListener('click', () => {
      options.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const file = btn.getAttribute('data-file') || '';
      input.value = file;
    });
  });
})();
