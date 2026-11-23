<?php
require __DIR__ . '/includes/init.php';

// Жабық бет: тек жұмыс беруші
$user = require_role('employer');

$pageTitle = 'Менің вакансияларым — JumysHub';
$activePage = 'employer';

// Read — тек осы жұмыс берушінің компанияларына тиесілі вакансиялар
$stmt = db()->prepare(vacancy_select_sql() . ' WHERE c.user_id = ? ORDER BY v.created_at DESC');
$stmt->execute([$user['id']]);
$vacancies = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="page-header">
      <div class="container page-header-row">
        <div>
          <h1>Менің вакансияларым</h1>
          <p>Вакансияларды қосыңыз, өңдеңіз және жойыңыз</p>
        </div>
        <a href="vacancy_form.php" class="btn">+ Жаңа вакансия</a>
      </div>
    </section>

    <div class="container section">
      <?php if (!$vacancies): ?>
        <div class="panel empty-panel">
          <p>Сізде әлі вакансия жоқ.</p>
          <a href="vacancy_form.php" class="btn">Алғашқы вакансияны қосу</a>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Лауазым</th>
                <th>Компания</th>
                <th>Сала</th>
                <th>Қала</th>
                <th>Жалақы</th>
                <th>Жарияланды</th>
                <th>Әрекеттер</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vacancies as $i => $vacancy): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><a href="job.php?id=<?= (int) $vacancy['id'] ?>"><?= e($vacancy['title']) ?></a></td>
                  <td><?= e($vacancy['company']) ?></td>
                  <td><?= e($vacancy['category']) ?></td>
                  <td><?= e($vacancy['city']) ?></td>
                  <td class="nowrap"><?= e(format_salary($vacancy['salary'])) ?></td>
                  <td class="nowrap"><?= e(date('d.m.Y', strtotime($vacancy['created_at']))) ?></td>
                  <td class="actions">
                    <a href="vacancy_form.php?id=<?= (int) $vacancy['id'] ?>" class="btn btn-small btn-outline">Өңдеу</a>
                    <form action="vacancy_delete.php" method="post" class="inline-form delete-form" data-confirm="«<?= e($vacancy['title']) ?>» вакансиясын жою керек пе?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int) $vacancy['id'] ?>">
                      <button type="submit" class="btn btn-small btn-danger">Жою</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
