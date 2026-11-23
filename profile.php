<?php
require __DIR__ . '/includes/init.php';

// Жабық бет: тек жүйеге кірген пайдаланушы көреді
$user = require_login();

$pageTitle = 'Жеке кабинет — JumysHub';
$activePage = 'profile';

if ($user['role'] === 'employer') {
    $stmt = db()->prepare('SELECT c.id, c.name, c.city, COUNT(v.id) AS vacancy_count
                           FROM companies c
                           LEFT JOIN vacancies v ON v.company_id = c.id
                           WHERE c.user_id = ?
                           GROUP BY c.id, c.name, c.city
                           ORDER BY c.name');
    $stmt->execute([$user['id']]);
    $companies = $stmt->fetchAll();
    $vacancyTotal = array_sum(array_column($companies, 'vacancy_count'));
} else {
    $stmt = db()->prepare('SELECT * FROM resumes WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $resume = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="page-header">
      <div class="container">
        <h1>Жеке кабинет</h1>
        <p>Аккаунт туралы ақпарат</p>
      </div>
    </section>

    <div class="container dashboard">
      <div class="panel">
        <h2>👤 <?= e($user['name']) ?></h2>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Рөлі:</strong> <?= $user['role'] === 'employer' ? 'Жұмыс беруші' : 'Жұмыс іздеуші' ?></p>
        <p><strong>Тіркелген күні:</strong> <?= e(date('d.m.Y', strtotime($user['created_at']))) ?></p>
      </div>

      <?php if ($user['role'] === 'employer'): ?>
        <div class="panel">
          <h2>🏢 Компанияларым</h2>
          <p>Барлығы <strong><?= (int) $vacancyTotal ?></strong> вакансия жарияланған.</p>
          <ul class="plain-list">
            <?php foreach ($companies as $company): ?>
              <li><?= e($company['name']) ?> — <?= (int) $company['vacancy_count'] ?> вакансия</li>
            <?php endforeach; ?>
          </ul>
          <div class="panel-actions">
            <a href="employer.php" class="btn">Вакансияларды басқару</a>
            <a href="vacancy_form.php" class="btn btn-outline">+ Жаңа вакансия</a>
          </div>
        </div>
      <?php else: ?>
        <div class="panel">
          <h2>📄 Менің резюмем</h2>
          <?php if ($resume): ?>
            <div class="resume-result">
              <p><strong>Аты-жөні:</strong> <?= e($resume['full_name']) ?></p>
              <p><strong>Лауазым:</strong> <?= e($resume['position']) ?></p>
              <p><strong>Телефон:</strong> <?= e($resume['phone']) ?></p>
              <p><strong>Тәжірибе:</strong> <?= nl2br(e($resume['experience'])) ?></p>
            </div>
            <a href="resume.php" class="btn">Резюмені өңдеу</a>
          <?php else: ?>
            <p>Сізде әлі резюме жоқ.</p>
            <a href="resume.php" class="btn">Резюме жасау</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
