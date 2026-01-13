<?php
$title = 'Login / Cadastro';
require __DIR__.'/../partials/header.php';
$tab = $_GET['tab'] ?? 'login';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm rounded-5 overflow-hidden">
        <div class="card-header bg-white text-black p-4">
          <div class="d-flex align-items-center gap-2">
            <img src="/assets/img/logo.png" width="70" height="50" class="rounded-2" alt="logo">
            <div>
              <div class="fw-semibold">Diagnóstico de Provas 7.1</div>
              <div class="small text-black-50">Entre ou crie sua conta</div>
            </div>
          </div>
        </div>
        <div class="card-body p-4 p-md-5">
          <ul class="nav nav-pills nav-fill mb-4">
            <li class="nav-item">
              <a class="nav-link <?= $tab==='login'?'active':'' ?>" href="<?= e(url('auth',['tab'=>'login'])) ?>">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $tab==='register'?'active':'' ?>" href="<?= e(url('auth',['tab'=>'register'])) ?>">Cadastro</a>
            </li>
          </ul>

          <?php if ($tab === 'register'): ?>
            <form method="post" action="<?= e(url('auth',['tab'=>'register'])) ?>" class="needs-validation" novalidate>
              <input type="hidden" name="action" value="register">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input required name="name" class="form-control" placeholder="Seu nome">
                <div class="invalid-feedback">Informe seu nome.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input required type="email" name="email" class="form-control" placeholder="seuemail@exemplo.com">
                <div class="invalid-feedback">Informe um email válido.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Senha</label>
                <input required type="password" name="password" minlength="6" class="form-control" placeholder="Mín. 6 caracteres">
                <div class="invalid-feedback">Senha mínima de 6 caracteres.</div>
              </div>

              <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg rounded-pill" type="submit">
                  <span class="material-icons align-middle me-1">person_add</span>
                  Criar conta
                </button>
              </div>

              <div class="mt-3 small text-secondary">
                <span class="material-icons fs-6 align-middle">payments</span>
                (Futuro) Integração Asaas para pagamento.
              </div>
            </form>
          <?php else: ?>
            <form method="post" action="<?= e(url('auth')) ?>" class="needs-validation" novalidate>
              <input type="hidden" name="action" value="login">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input required type="email" name="email" class="form-control" placeholder="aluno@teste.com">
                <div class="invalid-feedback">Informe um email válido.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Senha</label>
                <input required type="password" name="password" class="form-control" placeholder="123456">
                <div class="invalid-feedback">Informe sua senha.</div>
              </div>
              <div class="d-grid gap-2">
                <button class="btn btn-primary rounded-pill" type="submit">
                  <span class="material-icons align-middle me-1">login</span>
                  Entrar
                </button>
                <a class="btn btn-outline-secondary rounded-pill" href="<?= e(url('landing')) ?>">Voltar</a>
              </div>
            </form>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>

<?php require __DIR__.'/../partials/footer.php'; ?>
