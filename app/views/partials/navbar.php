<?php $u = current_user(); ?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('dashboard')) ?>">
      <img src="/assets/img/logo.png" alt="logo" width="50" height="50" class="rounded-2">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="d-flex justify-center gap-8 flex-wrap collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav mx-auto">
        <div class="nav-center">
          <a class="nav-link <?= ($active==='dashboard'?'active':'') ?>" href="<?= url('dashboard') ?>">
            Dashboard
          </a>
          <a class="nav-link <?= ($active==='simulados'?'active':'') ?>" href="<?= url('simulados') ?>">
            Simulados
          </a>
          <a class="nav-link <?= ($active==='diagnostico'?'active':'') ?>" href="<?= url('diagnostico') ?>">
            Diagnóstico
          </a>
        </div>
      </ul>


      <div class="d-flex align-items-center gap-2">
        <span class="text-white-50 d-none d-lg-inline small">Olá,</span>
        <div class="dropdown">
          <div class="user-menu" data-bs-toggle="dropdown">
            <img src="/<?= e($u['avatar_path']) ?>">
            <span><?= e($u['name']) ?></span>
            <span class="material-icons" style="font-size:18px;">expand_more</span>
          </div>

          <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
            <li>
              <a class="dropdown-item" href="<?= url('perfil') ?>">
                Editar perfil
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= url('logout') ?>">
                Sair
              </a>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</nav>
<?php require_once __DIR__ . '/modal_avatar_picker.php'; ?>
