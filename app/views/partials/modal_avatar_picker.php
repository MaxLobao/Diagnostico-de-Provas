<?php
$u = current_user();
$avatars = [
  'homem1.png','homem2.png','homem3.png','homem4.png',
  'mulher1.png','mulher2.png','mulher3.png','mulher4.png',
];

$current = basename($u['avatar_path'] ?? 'avatar.png');
?>
<div class="modal fade" id="modalAvatarPicker" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4 border-0 shadow-lg" method="post" action="<?= e(url('perfil')) ?>">
      <input type="hidden" name="action" value="update_avatar">
      <input type="hidden" name="avatar_file" id="avatar_file" value="<?= e($current) ?>">

      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Escolha sua foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body pt-0">
        <div class="row g-3">
          <?php foreach ($avatars as $file): ?>
            <div class="col-3">
              <button
                type="button"
                class="dp-avatar-option <?= $file === $current ? 'active' : '' ?>"
                data-file="<?= e($file) ?>"
              >
                <img src="/assets/img/<?= e($file) ?>" alt="<?= e($file) ?>">
              </button>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="small text-secondary mt-3">
          Clique em uma opção e depois em <b>Salvar</b>.
        </div>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary rounded-pill px-4">
          <span class="material-icons align-middle fs-6 me-1">check</span>
          Salvar
        </button>
      </div>
    </form>
  </div>
</div>
