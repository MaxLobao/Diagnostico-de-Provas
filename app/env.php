<?php
declare(strict_types=1);

/**
 * Mini dotenv (sem composer):
 * - lê .env na raiz do projeto
 * - suporta KEY=VALUE e KEY="VALUE"
 */
function env_load(string $rootPath): array {
  $envFile = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
  $vars = [];
  if (!is_file($envFile)) return $vars;

  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;

    $pos = strpos($line, '=');
    if ($pos === false) continue;

    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));

    // remove aspas
    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
      $val = substr($val, 1, -1);
    }

    $vars[$key] = $val;
    $_ENV[$key] = $val;
  }
  return $vars;
}

function env(string $key, $default = null) {
  return $_ENV[$key] ?? $default;
}
