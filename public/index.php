<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$page = $_GET['page'] ?? 'landing';

if ($page === 'logout') {
  auth_logout();
  flash_set('success', 'Você saiu da sua conta.');
  redirect(url('landing'));
}

if ($page === 'auth') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
      $email = trim($_POST['email'] ?? '');
      $pass  = (string)($_POST['password'] ?? '');
      if (auth_attempt($email, $pass)) {
        flash_set('success', 'Bem-vindo!');
        redirect(url('dashboard'));
      } else {
        flash_set('warning', 'Email ou senha inválidos.');
        redirect(url('auth'));
      }
    }

    if ($action === 'register') {
      $res = auth_register(
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        (string)($_POST['password'] ?? '')
      );
      flash_set($res['ok'] ? 'success' : 'warning', $res['msg']);
      redirect(url('auth', ['tab'=>'login']));
    }
  }

  require __DIR__ . '/../app/views/pages/auth.php';
  exit;
}

// Public pages
if ($page === 'landing') {
  require __DIR__ . '/../app/views/pages/landing.php';
  exit;
}

// Protected
require_login();

switch ($page) {
  case 'dashboard':
    require __DIR__ . '/../app/views/pages/dashboard.php';
    break;
  case 'diagnostico':
    require __DIR__ . '/../app/views/pages/diagnostico.php';
    break;
  case 'simulados':
    require __DIR__ . '/../app/views/pages/simulados.php';
    break;
  case 'caderno':
    require __DIR__ . '/../app/views/pages/caderno.php';
    break;
  case 'perfil':
    require __DIR__ . '/../app/views/pages/perfil.php';
    break;
  default:
    flash_set('warning', 'Página não encontrada.');
    redirect(url('dashboard'));
}
