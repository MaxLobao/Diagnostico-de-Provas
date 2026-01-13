<?php
$f = flash_get();
$title = $title ?? env('APP_NAME', 'Diagnóstico de Provas 7.1');
?><!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($title) ?></title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <link href="/assets/css/navbar.css" rel="stylesheet">

  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-surface">
<?php if ($f): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100">
  <div
    class="toast align-items-center text-bg-<?= e($f['type']) ?> border-0 show dp-toast"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    data-bs-delay="4000"
  >
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <span class="material-icons fs-6">
          <?= $f['type']==='success'?'check_circle':
              ($f['type']==='warning'?'warning':
              ($f['type']==='danger'?'error':'info')) ?>
        </span>
        <?= e($f['msg']) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<?php endif; ?>

