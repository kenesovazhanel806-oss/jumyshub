<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'JumysHub — Жұмыс орындары мен резюме платформасы';
$activePage = 'home';

// Ең жаңа 4 вакансия — дерекқордан
$latestJobs = db()->query(vacancy_select_sql() . ' ORDER BY v.created_at DESC LIMIT 4')->fetchAll();

// Салалар және әр саладағы вакансия саны
$categories = db()->query('SELECT cat.name, cat.icon, COUNT(v.id) AS total
                           FROM categories cat
                           LEFT JOIN vacancies v ON v.category_id = cat.id
                           GROUP BY cat.id, cat.name, cat.icon
                           ORDER BY total DESC, cat.name
                           LIMIT 4')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <h1>Арманыңыздағы жұмысты табыңыз</h1>
        <p>Вакансияларды іздеңіз, резюме жасаңыз және жұмыс берушілерге бір жерден өтініш беріңіз</p>
        <form class="search-form" action="catalog.php" method="get">
          <input type="text" name="q" placeholder="Лауазым немесе компания...">
          <button type="submit" class="btn btn-accent">Іздеу</button>
        </form>
      </div>
    </section>

    <!-- Салалар -->
    <section class="section">
      <div class="container">
        <h2 class="section-title">Салалар</h2>
        <div class="categories">
          <?php foreach ($categories as $category): ?>
            <a href="catalog.php?category=<?= urlencode($category['name']) ?>" class="category-card">
              <div class="icon"><?= $category['icon'] ?></div>
              <h3><?= e($category['name']) ?></h3>
              <p class="muted"><?= (int) $category['total'] ?> вакансия</p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Жаңа вакансиялар -->
    <section class="section">
      <div class="container">
        <h2 class="section-title">Жаңа вакансиялар</h2>
        <div class="jobs-grid">
          <?php foreach ($latestJobs as $job): ?>
            <?php include __DIR__ . '/includes/job_card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Мүмкіндіктер -->
    <section class="section" id="about">
      <div class="container">
        <h2 class="section-title">Неге JumysHub?</h2>
        <div class="features">
          <div class="feature">
            <div class="icon">🔎</div>
            <h3>Жылдам іздеу</h3>
            <p>Вакансияны лауазым, сала немесе қала бойынша бірнеше секундта табыңыз.</p>
          </div>
          <div class="feature">
            <div class="icon">📄</div>
            <h3>Онлайн резюме</h3>
            <p>Резюмеңізді бір формада толтырып, оны жұмыс берушілерге ұсыныңыз.</p>
          </div>
          <div class="feature">
            <div class="icon">❤️</div>
            <h3>Сақталған вакансиялар</h3>
            <p>Ұнаған вакансияларды сақтап, кейін оларға қайта оралыңыз.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to action -->
    <section class="section">
      <div class="container">
        <div class="cta">
          <h2>Резюмеңіз дайын ба?</h2>
          <p>Бірнеше минутта резюме толтырып, жұмыс іздеуді бастаңыз.</p>
          <a href="resume.php" class="btn">Резюме жасау</a>
        </div>
      </div>
    </section>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
