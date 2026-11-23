<?php $currentUser = current_user(); ?>
<!DOCTYPE html>
<html lang="kk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <a href="index.php" class="logo">💼 JumysHub</a>
      <button class="burger" aria-label="Мәзір">☰</button>
      <nav class="nav">
        <a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Басты бет</a>
        <a href="catalog.php" class="<?= $activePage === 'catalog' ? 'active' : '' ?>">Вакансиялар</a>
        <?php if ($currentUser && $currentUser['role'] === 'employer'): ?>
          <a href="employer.php" class="<?= $activePage === 'employer' ? 'active' : '' ?>">Менің вакансияларым</a>
        <?php else: ?>
          <a href="resume.php" class="<?= $activePage === 'resume' ? 'active' : '' ?>">Резюме</a>
        <?php endif; ?>
        <div class="nav-buttons">
          <?php if ($currentUser): ?>
            <a href="profile.php" class="user-link <?= $activePage === 'profile' ? 'active' : '' ?>">👤 <?= e($currentUser['name']) ?></a>
            <form action="logout.php" method="post" class="inline-form">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-outline">Шығу</button>
            </form>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline">Кіру</a>
            <a href="register.php" class="btn">Тіркелу</a>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>

  <?php if ($flash = get_flash()): ?>
    <div class="container">
      <div class="message <?= e($flash['type']) ?> flash"><?= e($flash['message']) ?></div>
    </div>
  <?php endif; ?>
