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
        <a href="resume.php" class="<?= $activePage === 'resume' ? 'active' : '' ?>">Резюме жасау</a>
        <div class="nav-buttons">
          <a href="login.php" class="btn btn-outline">Кіру</a>
          <a href="register.php" class="btn">Тіркелу</a>
        </div>
      </nav>
    </div>
  </header>
