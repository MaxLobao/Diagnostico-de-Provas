<?php
declare(strict_types=1);

function diagnostico_timeseries(int $userId): array {
  $pdo = db();

  // Série por ÁREA (sempre existe)
  $sqlArea = "
    SELECT
      s.id AS simulado_id,
      s.name AS simulado_name,
      s.group_name,
      s.applied_date AS date,
      'area' AS dim_type,
      q.area_label AS dim,
      COUNT(*) AS total,
      SUM(CASE WHEN q.status='consciente' THEN 1 ELSE 0 END) AS correct
    FROM simulados s
    JOIN questions q ON q.simulado_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id, q.area_label
  ";

  // Série por DISCIPLINA (só quando discipline foi preenchida)
  $sqlDisc = "
    SELECT
      s.id AS simulado_id,
      s.name AS simulado_name,
      s.group_name,
      s.applied_date AS date,
      'disc' AS dim_type,
      q.discipline AS dim,
      COUNT(*) AS total,
      SUM(CASE WHEN q.status='consciente' THEN 1 ELSE 0 END) AS correct
    FROM simulados s
    JOIN questions q ON q.simulado_id = s.id
    WHERE s.user_id = ?
      AND q.discipline IS NOT NULL
      AND TRIM(q.discipline) <> ''
    GROUP BY s.id, q.discipline
  ";

  $stmt1 = $pdo->prepare($sqlArea);
  $stmt1->execute([$userId]);
  $rows1 = $stmt1->fetchAll();

  $stmt2 = $pdo->prepare($sqlDisc);
  $stmt2->execute([$userId]);
  $rows2 = $stmt2->fetchAll();

  $all = array_merge($rows1, $rows2);

  usort($all, function($a, $b){
    $c = strcmp((string)$a['date'], (string)$b['date']);
    if ($c !== 0) return $c;
    return ((int)$a['simulado_id']) <=> ((int)$b['simulado_id']);
  });

  return $all;
}

function radar_prioridades_detalhado(int $userId, int $limit = 20): array {
  $pdo = db();

  $sql = "
    SELECT
      COALESCE(NULLIF(TRIM(q.discipline), ''), q.area_label) AS dim,
      SUM(CASE WHEN q.status='erro' THEN 1 ELSE 0 END) AS erros,
      SUM(CASE WHEN q.status='chute' THEN 1 ELSE 0 END) AS chutes,
      SUM(CASE WHEN q.status='consciente' THEN 1 ELSE 0 END) AS conscientes,
      COUNT(*) AS total
    FROM simulados s
    JOIN questions q ON q.simulado_id = s.id
    WHERE s.user_id = ?
    GROUP BY COALESCE(NULLIF(TRIM(q.discipline), ''), q.area_label)
    ORDER BY (erros + chutes) DESC, total DESC
    LIMIT ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$userId, $limit]);
  return $stmt->fetchAll();
}

function build_custom_areas_from_post(): array {
  $twoDays = !empty($_POST['custom_dois_dias']);
  $day2Start = (int)($_POST['custom_day2_start'] ?? 91);

  $day1Names = $_POST['day1_area_name'] ?? [];
  $day1Qs    = $_POST['day1_area_questions'] ?? [];
  $day1Disc  = $_POST['day1_area_disciplines'] ?? [];

  $areas = [];

  // Dia 1
  for ($i=0; $i<count($day1Names); $i++){
    $name = trim((string)($day1Names[$i] ?? ''));
    $qtd  = (int)($day1Qs[$i] ?? 0);
    if ($name === '' || $qtd <= 0) continue;

    $areas[] = [
      'day' => 1,
      'name' => $name,
      'questions' => $qtd,
      'disciplines' => array_values(array_map('trim', (array)($day1Disc[$i] ?? [])))
    ];
  }

  // Dia 2 (se existir)
  if ($twoDays) {
    $day2Names = $_POST['day2_area_name'] ?? [];
    $day2Qs    = $_POST['day2_area_questions'] ?? [];
    $day2Disc  = $_POST['day2_area_disciplines'] ?? [];

    for ($i=0; $i<count($day2Names); $i++){
      $name = trim((string)($day2Names[$i] ?? ''));
      $qtd  = (int)($day2Qs[$i] ?? 0);
      if ($name === '' || $qtd <= 0) continue;

      $areas[] = [
        'day' => 2,
        'name' => $name,
        'questions' => $qtd,
        'disciplines' => array_values(array_map('trim', (array)($day2Disc[$i] ?? [])))
      ];
    }
  }

  return [
    'two_days' => $twoDays,
    'day2_start' => $day2Start,
    'areas' => $areas
  ];
}

function seed_questions_custom(PDO $pdo, int $simuladoId, array $config): void {
  $now = now_iso();

  $areas = $config['areas'] ?? [];
  $twoDays = !empty($config['two_days']);
  $day2Start = (int)($config['day2_start'] ?? 91);

  // numeração começa em 1 (dia 1)
  $qNumber = 1;

  // separa dia 1 e dia 2
  $day1 = array_values(array_filter($areas, fn($a) => (int)$a['day'] === 1));
  $day2 = array_values(array_filter($areas, fn($a) => (int)$a['day'] === 2));

  $insert = $pdo->prepare("
    INSERT INTO questions (simulado_id, q_number, area_label, status, discipline, reason, updated_at)
    VALUES (?,?,?,?,?,?,?)
  ");

  // dia 1
  foreach ($day1 as $a) {
    $label = (string)$a['name'];
    $count = (int)$a['questions'];
    for ($i=0; $i<$count; $i++){
      $insert->execute([$simuladoId, $qNumber, $label, 'consciente', null, null, $now]);
      $qNumber++;
    }
  }

  // dia 2 (numeração começa no day2_start)
  if ($twoDays && !empty($day2)) {
    $qNumber = max($qNumber, $day2Start);
    foreach ($day2 as $a) {
      $label = (string)$a['name'];
      $count = (int)$a['questions'];
      for ($i=0; $i<$count; $i++){
        $insert->execute([$simuladoId, $qNumber, $label, 'consciente', null, null, $now]);
        $qNumber++;
      }
    }
  }
}
