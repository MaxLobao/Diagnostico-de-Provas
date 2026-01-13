<?php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $dbPath = env('DB_DATABASE', 'storage/database.sqlite');
  $dbPathAbs = project_root() . DIRECTORY_SEPARATOR . $dbPath;

  $dir = dirname($dbPathAbs);
  if (!is_dir($dir)) mkdir($dir, 0777, true);

  $pdo = new PDO('sqlite:' . $dbPathAbs);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

  // SQLite: precisa habilitar foreign keys explicitamente
  $pdo->exec("PRAGMA foreign_keys = ON");

  return $pdo;
}

function project_root(): string {
  return dirname(__DIR__);
}

function migrate_if_needed(): void {
  $pdo = db();

  // ---------------------------
  // USERS
  // ---------------------------
  $pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    avatar_path TEXT DEFAULT NULL,
    created_at TEXT NOT NULL
  )");

  // ---------------------------
  // SIMULADOS
  // ---------------------------
  $pdo->exec("CREATE TABLE IF NOT EXISTS simulados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    applied_date TEXT NOT NULL,
    group_name TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente|concluido
    template_type TEXT NOT NULL, -- enem|fuvest|unicamp|custom
    config_json TEXT NOT NULL,
    total_questions INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
  )");

  // ---------------------------
  // QUESTIONS (caderno)
  // ---------------------------
  $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    simulado_id INTEGER NOT NULL,
    q_number INTEGER NOT NULL,
    area_label TEXT NOT NULL, -- Linguagens / Ciências Humanas / etc
    status TEXT NOT NULL DEFAULT 'consciente', -- consciente|erro|chute
    discipline TEXT DEFAULT NULL,
    reason TEXT DEFAULT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY(simulado_id) REFERENCES simulados(id) ON DELETE CASCADE
  )");

  // ---------------------------
  // Índices (performance)
  // ---------------------------
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_simulados_user ON simulados(user_id)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_simulados_user_group ON simulados(user_id, group_name)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_simulados_user_date ON simulados(user_id, applied_date)");

  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_questions_simulado ON questions(simulado_id)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_questions_simulado_status ON questions(simulado_id, status)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_questions_simulado_area ON questions(simulado_id, area_label)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_questions_simulado_disc ON questions(simulado_id, discipline)");

  // Opcional: evitar duplicidade de número de questão no mesmo simulado
  // (se preferir permitir repetição, pode remover)
  $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS uq_questions_simulado_qnum ON questions(simulado_id, q_number)");

  // ---------------------------
  // Seed demo user
  // ---------------------------
  $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
  if ($count === 0) {
    $email = env('DEMO_USER_EMAIL', 'aluno@teste.com');
    $pass  = env('DEMO_USER_PASSWORD', '123456');
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,avatar_path,created_at)
                           VALUES (?,?,?,?,?)");
    $stmt->execute(['Aluno Teste', $email, $hash, 'assets/img/avatar.png', now_iso()]);
  }
}
