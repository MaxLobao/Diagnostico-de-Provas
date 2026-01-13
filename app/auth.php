<?php
declare(strict_types=1);

function auth_attempt(string $email, string $password): bool {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if (!$user) return false;
  if (!password_verify($password, $user['password_hash'])) return false;

  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'avatar_path' => $user['avatar_path'] ?: 'assets/img/avatar.png'
  ];
  return true;
}

function auth_register(string $name, string $email, string $password): array {
  $pdo = db();
  // naive validation
  if (strlen($name) < 2) return ['ok'=>false,'msg'=>'Nome muito curto.'];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok'=>false,'msg'=>'Email inválido.'];
  if (strlen($password) < 6) return ['ok'=>false,'msg'=>'Senha deve ter no mínimo 6 caracteres.'];

  $hash = password_hash($password, PASSWORD_DEFAULT);
  try {
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,avatar_path,created_at) VALUES (?,?,?,?,?)");
    $stmt->execute([$name, $email, $hash, 'assets/img/avatar.png', now_iso()]);
    return ['ok'=>true,'msg'=>'Cadastro realizado. Faça login.'];
  } catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'UNIQUE')) {
      return ['ok'=>false,'msg'=>'Este email já está cadastrado.'];
    }
    return ['ok'=>false,'msg'=>'Erro ao cadastrar.'];
  }
}

function auth_logout(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}
