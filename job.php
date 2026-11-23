<?php
require __DIR__ . '/includes/init.php';

// Read — бір вакансияны id бойынша көрсету
$id = int_param($_GET, 'id');
$job = $id !== null ? find_vacancy($id) : null;

if (!$job) {
    http_response_code(404);
}

$user = current_user();
$isOwner = $job && $user && (int) $job['owner_id'] === (int) $user['id'];

$pageTitle = ($job ? $job['title'] : 'Вакансия табылмады') . ' — JumysHub';
$activePage = 'catalog';

include __DIR__ . '/includes/header.php';
?>
  <main>
    <div class="container section">
      <a href="catalog.php" class="back-link">← Барлық вакансиялар</a>

      <?php if (!$job): ?>
        <div class="panel empty-panel">
          <h1>Вакансия табылмады 😔</h1>
          <p>Ол жойылған немесе сілтеме қате болуы мүмкін.</p>
          <a href="catalog.php" class="btn">Вакансиялар тізіміне өту</a>
        </div>
      <?php else: ?>
        <article class="panel job-detail">
          <div class="job-detail-head">
            <div class="job-cover big" style="background:<?= e(category_color($job['category'])) ?>"><?= e(mb_substr($job['company'], 0, 1)) ?></div>
            <div>
              <span class="tag"><?= e($job['category']) ?></span>
              <h1><?= e($job['title']) ?></h1>
              <p class="job-author"><?= e($job['company']) ?> · <?= e($job['city']) ?> · <?= e($job['work_format']) ?></p>
            </div>
          </div>

          <div class="job-facts">
            <div><span>Жалақы</span><strong><?= e(format_salary($job['salary'])) ?></strong></div>
            <div><span>Қала</span><strong><?= e($job['city']) ?></strong></div>
            <div><span>Формат</span><strong><?= e($job['work_format']) ?></strong></div>
            <div><span>Жарияланды</span><strong><?= e(time_ago($job['created_at'])) ?></strong></div>
          </div>

          <h2>Сипаттама және талаптар</h2>
          <p class="job-description"><?= nl2br(e($job['description'])) ?></p>

          <div class="panel-actions">
            <?php if ($isOwner): ?>
              <a href="vacancy_form.php?id=<?= (int) $job['id'] ?>" class="btn">Өңдеу</a>
              <form action="vacancy_delete.php" method="post" class="inline-form delete-form" data-confirm="Бұл вакансияны жою керек пе?">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $job['id'] ?>">
                <button type="submit" class="btn btn-danger">Жою</button>
              </form>
            <?php elseif (!$user): ?>
              <a href="login.php?next=<?= urlencode('job.php?id=' . (int) $job['id']) ?>" class="btn">Өтініш беру үшін кіріңіз</a>
            <?php endif; ?>
            <button type="button" class="btn btn-outline fav-btn" data-id="<?= (int) $job['id'] ?>">🤍</button>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
