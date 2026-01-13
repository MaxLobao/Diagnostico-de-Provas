<?php
$active = '';
$title = 'Editar perfil';
require __DIR__.'/../partials/header.php';
require __DIR__.'/../partials/navbar.php';

$u = current_user();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $action = $_POST['action'] ?? '';

  // ==========================
  // 1) Atualizar AVATAR
  // ==========================
  if ($action === 'update_avatar') {

    $allowed = [
      'homem1.png','homem2.png','homem3.png','homem4.png',
      'mulher1.png','mulher2.png','mulher3.png','mulher4.png',
    ];

    $file = basename((string)($_POST['avatar_file'] ?? ''));

    if (!in_array($file, $allowed, true)) {
      flash_set('warning', 'Avatar inválido.');
      redirect(url('perfil'));
    }

    $path = 'assets/img/' . $file;

    $stmt = $pdo->prepare("UPDATE users SET avatar_path=? WHERE id=?");
    $stmt->execute([$path, (int)$u['id']]);

    // atualiza sessão para refletir imediatamente no menu/dashboard
    $_SESSION['user']['avatar_path'] = $path;

    flash_set('success', 'Foto do perfil atualizada!');
    redirect(url('perfil'));
  }

  // ==========================
  // 2) Atualizar NOME (default)
  // ==========================
  $name = trim($_POST['name'] ?? '');

  if (strlen($name) < 2) {
    flash_set('warning', 'Nome muito curto.');
    redirect(url('perfil'));
  }

  $stmt = $pdo->prepare("UPDATE users SET name=? WHERE id=?");
  $stmt->execute([$name, (int)$u['id']]);

  $_SESSION['user']['name'] = $name;

  flash_set('success', 'Perfil atualizado (protótipo).');
  redirect(url('perfil'));
}
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-1">Editar perfil</h4>
          <div class="text-secondary mb-4">
            Você pode editar o nome. A foto do perfil é escolhida clicando no avatar no menu superior.
          </div>

          <form method="post" class="row g-3">
            <div class="col-12 col-md-8">
              <label class="form-label">Nome</label>
              <input class="form-control" name="name" value="<?= e($u['name']) ?>" required>
            </div>
            <div class="col-12 col-md-4 d-grid">
              <label class="form-label invisible">.</label>
              <button class="btn btn-primary rounded-pill" type="submit">
                <span class="material-icons align-middle fs-6 me-1">save</span>
                Salvar
              </button>
            </div>
          </form>

          <hr class="my-4">

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <img
                src="/<?= e($u['avatar_path'] ?? 'assets/img/avatar.png') ?>"
                alt="Foto do perfil"
                style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb;"
              >
              <div>
                <div class="fw-bold">Foto do perfil</div>
                <div class="text-secondary small">Escolha uma das 8 opções disponíveis.</div>
              </div>
            </div>

            <button
              type="button"
              class="btn btn-outline-primary rounded-pill"
              data-bs-toggle="modal"
              data-bs-target="#modalAvatarPicker"
            >
              <span class="material-icons align-middle fs-6 me-1">photo_camera</span>
              Alterar foto
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__.'/../partials/footer.php'; ?>
