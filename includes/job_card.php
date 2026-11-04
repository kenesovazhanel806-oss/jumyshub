<article class="job-card">
  <div class="job-cover" style="background:<?= e($job['color']) ?>"><?= e(mb_substr($job['company'], 0, 1)) ?></div>
  <div class="job-info">
    <span class="tag"><?= e($job['category']) ?></span>
    <h3><?= e($job['title']) ?></h3>
    <p class="job-author"><?= e($job['company']) ?> · <?= e($job['city']) ?> · <?= e($job['format']) ?></p>
    <div class="job-meta">
      <span class="salary"><?= e(format_salary($job['salary'])) ?></span>
      <button type="button" class="fav-btn" data-id="<?= (int) $job['id'] ?>" title="Вакансияны сақтау">🤍</button>
    </div>
  </div>
</article>
