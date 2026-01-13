<?php
declare(strict_types=1);

function dd(...$vars): void {
  echo "<pre style='background:#111;color:#0f0;padding:12px;border-radius:10px;overflow:auto'>";
  foreach ($vars as $v) { var_dump($v); }
  echo "</pre>";
  exit;
}

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): void {
  // se alguém já imprimiu HTML sem querer, evita warning e tenta fallback por JS
  if (!headers_sent()) {
    header('Location: ' . $to);
    exit;
  }

  // fallback (quando headers já foram enviados)
  echo "<script>window.location.href=" . json_encode($to) . ";</script>";
  echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($to, ENT_QUOTES) . "'></noscript>";
  exit;
}


function url(string $page = 'landing', array $params = []): string {
  $q = array_merge(['page' => $page], $params);
  return '/index.php?' . http_build_query($q);
}

function flash_set(string $type, string $msg): void {
  $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array {
  if (!isset($_SESSION['flash'])) return null;
  $f = $_SESSION['flash'];
  unset($_SESSION['flash']);
  return $f;
}

function require_login(): void {
  if (!isset($_SESSION['user_id'])) {
    flash_set('warning', 'Faça login para continuar.');
    redirect(url('auth'));
  }
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function now_iso(): string {
  return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
}
