<?php
declare(strict_types=1);

function templates(): array {
  return [
    'enem' => [
      'label' => 'ENEM (135 questões no protótipo)',
      'areas' => [
        ['label' => 'Linguagens', 'from' => 1, 'to' => 45],
        ['label' => 'Ciências Humanas', 'from' => 46, 'to' => 90],
        ['label' => 'Ciências da Natureza', 'from' => 91, 'to' => 135],
      ],
      'total' => 135,
      'dias' => 2
    ],
    'fuvest' => [
      'label' => 'FUVEST (90 questões no protótipo)',
      'areas' => [
        ['label' => 'Prova', 'from' => 1, 'to' => 90],
      ],
      'total' => 90,
      'dias' => 1
    ],
    'unicamp' => [
      'label' => 'UNICAMP (72 questões no protótipo)',
      'areas' => [
        ['label' => 'Prova', 'from' => 1, 'to' => 72],
      ],
      'total' => 72,
      'dias' => 1
    ],
  ];
}

function simulado_create(int $userId, array $data): array {
  $pdo = db();

  $name = trim($data['name'] ?? '');
  $applied_date = trim($data['applied_date'] ?? '');
  $group_name = trim($data['group_name'] ?? '');
  $template_type = trim($data['template_type'] ?? 'enem');

  if ($name === '') {
  return ['ok'=>false,'msg'=>'Preencha o nome do simulado.'];
  }

  if ($applied_date === '') {
    $applied_date = date('Y-m-d'); // se não informar, assume hoje
  }

  if ($group_name === '') {
    $group_name = 'Geral'; // grupo opcional (como no layout)
  }

  $config = [
    'mode' => $template_type,
    'custom' => [
      'total_questions' => (int)($data['custom_total_questions'] ?? 0),
      'dias' => (int)($data['custom_dias'] ?? 1),
      'areas_json' => trim($data['custom_areas_json'] ?? ''),
      'assuntos' => trim($data['custom_assuntos'] ?? ''),
    ]
  ];

  $templates = templates();
  if ($template_type !== 'custom' && !isset($templates[$template_type])) {
    $template_type = 'enem';
  }

  $total = 0;
  $areas = [];

  if ($template_type === 'custom') {
    $total = max(1, (int)($data['custom_total_questions'] ?? 20));
    $areasJson = trim($data['custom_areas_json'] ?? '');
    $areas = [];
    if ($areasJson !== '') {
      $decoded = json_decode($areasJson, true);
      if (is_array($decoded)) {
        $cursor = 1;
        foreach ($decoded as $row) {
          $label = trim((string)($row['label'] ?? 'Área'));
          $qty = (int)($row['qty'] ?? 0);
          if ($qty <= 0) continue;
          $areas[] = ['label'=>$label, 'from'=>$cursor, 'to'=>$cursor + $qty - 1];
          $cursor += $qty;
        }
      }
    }
    if (empty($areas)) {
      // fallback: área única
      $areas = [['label'=>'Prova', 'from'=>1, 'to'=>$total]];
    } else {
      // ajusta total para caber nas áreas definidas
      $total = (int)end($areas)['to'];
    }
  } else {
    $total = (int)$templates[$template_type]['total'];
    $areas = $templates[$template_type]['areas'];
  }

  $stmt = $pdo->prepare("INSERT INTO simulados
    (user_id,name,applied_date,group_name,status,template_type,config_json,total_questions,created_at)
    VALUES (?,?,?,?,?,?,?,?,?)");
  $stmt->execute([
    $userId,
    $name,
    $applied_date,
    $group_name,
    'pendente',
    $template_type,
    json_encode($config, JSON_UNESCAPED_UNICODE),
    $total,
    now_iso()
  ]);
  $simuladoId = (int)$pdo->lastInsertId();

  // create questions with default status = consciente (green)
  $qStmt = $pdo->prepare("INSERT INTO questions (simulado_id,q_number,area_label,status,discipline,reason,updated_at)
    VALUES (?,?,?,?,?,?,?)");

  foreach ($areas as $area) {
    for ($i = (int)$area['from']; $i <= (int)$area['to']; $i++) {
      $qStmt->execute([$simuladoId, $i, $area['label'], 'consciente', null, null, now_iso()]);
    }
  }

  return ['ok'=>true,'id'=>$simuladoId,'msg'=>'Simulado criado.'];
}

function simulados_for_user(int $userId): array {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM simulados WHERE user_id = ? ORDER BY created_at DESC");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function simulado_get(int $userId, int $simuladoId): ?array {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM simulados WHERE id = ? AND user_id = ?");
  $stmt->execute([$simuladoId, $userId]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function questions_for_simulado(int $simuladoId): array {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM questions WHERE simulado_id = ? ORDER BY q_number ASC");
  $stmt->execute([$simuladoId]);
  return $stmt->fetchAll();
}

function question_update(int $userId, int $simuladoId, int $qId, array $data): array {
  // safety: ensure question belongs to simulado and simulado belongs to user
  $pdo = db();
  $s = simulado_get($userId, $simuladoId);
  if (!$s) return ['ok'=>false,'msg'=>'Simulado não encontrado.'];

  $stmt = $pdo->prepare("SELECT * FROM questions WHERE id=? AND simulado_id=?");
  $stmt->execute([$qId, $simuladoId]);
  $q = $stmt->fetch();
  if (!$q) return ['ok'=>false,'msg'=>'Questão não encontrada.'];

  $status = $data['status'] ?? 'consciente';
  if (!in_array($status, ['consciente','erro','chute'], true)) $status = 'consciente';

  $discipline = trim((string)($data['discipline'] ?? ''));
  $reason = trim((string)($data['reason'] ?? ''));

  if ($status === 'consciente') {
    $discipline = null;
    $reason = null;
  } else {
    if ($discipline === '') $discipline = 'Não informado';
    if ($reason === '') $reason = 'Não informado';
  }

  $u = $pdo->prepare("UPDATE questions SET status=?, discipline=?, reason=?, updated_at=? WHERE id=?");
  $u->execute([$status, $discipline, $reason, now_iso(), $qId]);

  // mark simulado as concluido if any update + optional rule: when all questions have some status (always true) => conclude on first update
  $pdo->prepare("UPDATE simulados SET status='concluido' WHERE id=?")->execute([$simuladoId]);

  return ['ok'=>true,'msg'=>'Atualizado.'];
}

function simulado_delete(int $userId, int $simuladoId): array {
  $pdo = db();
  $s = simulado_get($userId, $simuladoId);
  if (!$s) return ['ok'=>false,'msg'=>'Simulado não encontrado.'];

  $pdo->prepare("DELETE FROM questions WHERE simulado_id=?")->execute([$simuladoId]);
  $pdo->prepare("DELETE FROM simulados WHERE id=?")->execute([$simuladoId]);

  return ['ok'=>true,'msg'=>'Simulado excluído.'];
}

function stats_overview(int $userId): array {
  $pdo = db();

  $totSim = (int)$pdo->query("SELECT COUNT(*) AS c FROM simulados WHERE user_id = {$userId}")->fetch()['c'];
  $done = (int)$pdo->query("SELECT COUNT(*) AS c FROM simulados WHERE user_id = {$userId} AND status='concluido'")->fetch()['c'];
  $pend = $totSim - $done;

  $q = $pdo->prepare("SELECT
      SUM(CASE WHEN q.status='consciente' THEN 1 ELSE 0 END) AS conscientes,
      SUM(CASE WHEN q.status='erro' THEN 1 ELSE 0 END) AS erros,
      SUM(CASE WHEN q.status='chute' THEN 1 ELSE 0 END) AS chutes,
      COUNT(*) AS total
    FROM questions q
    JOIN simulados s ON s.id=q.simulado_id
    WHERE s.user_id=?");
  $q->execute([$userId]);
  $row = $q->fetch() ?: ['conscientes'=>0,'erros'=>0,'chutes'=>0,'total'=>0];

  $consc = (int)($row['conscientes'] ?? 0);
  $totalQ = (int)($row['total'] ?? 0);
  $media = $totalQ > 0 ? round(($consc / $totalQ) * 100, 1) : 0.0;

  return [
    'total_simulados' => $totSim,
    'concluidos' => $done,
    'pendentes' => $pend,
    'total_questoes' => $totalQ,
    'acertos_conscientes' => $consc,
    'erros' => (int)($row['erros'] ?? 0),
    'chutes' => (int)($row['chutes'] ?? 0),
    'media_geral' => $media
  ];
}

function radar_prioridades(int $userId, int $limit = 8): array {
  $pdo = db();
  // rank by (erros + chutes) grouped by discipline
  $stmt = $pdo->prepare("
    SELECT
      COALESCE(discipline,'Não informado') AS discipline,
      SUM(CASE WHEN q.status='erro' THEN 1 ELSE 0 END) AS erros,
      SUM(CASE WHEN q.status='chute' THEN 1 ELSE 0 END) AS chutes
    FROM questions q
    JOIN simulados s ON s.id=q.simulado_id
    WHERE s.user_id=? AND q.status IN ('erro','chute')
    GROUP BY discipline
    ORDER BY (erros + chutes) DESC
    LIMIT {$limit}
  ");
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function evolution_by_group(int $userId): array {
  $pdo = db();
  $stmt = $pdo->prepare("
    SELECT
      COALESCE(s.group_name, 'Geral') AS group_name,
      COUNT(DISTINCT s.id) AS simulados,
      SUM(CASE WHEN q.status='consciente' THEN 1 ELSE 0 END) AS acertos,
      COUNT(q.id) AS total_questions
    FROM simulados s
    LEFT JOIN questions q ON q.simulado_id = s.id
    WHERE s.user_id = ?
    GROUP BY COALESCE(s.group_name, 'Geral')
    ORDER BY COALESCE(s.group_name, 'Geral') ASC
  ");
  $stmt->execute([$userId]);
  $rows = $stmt->fetchAll();

  foreach ($rows as &$r) {
    $total = (int)($r['total_questions'] ?? 0);
    $acertos = (int)($r['acertos'] ?? 0);
    $r['media'] = $total > 0 ? round(($acertos / $total) * 100, 1) : 0.0;

    // compat: se alguma tela antiga ainda usa esses nomes:
    $r['conscientes'] = $acertos;
    $r['total'] = $total;
  }
  unset($r);

  return $rows;
}

