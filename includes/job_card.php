<article class="job-card">
  <a href="job.php?id=<?= (int) $job['id'] ?>" class="job-cover" style="background:<?= e(category_color($job['category'])) ?>"><?= e(mb_substr($job['company'], 0, 1)) ?></a>
  <div class="job-info">
    <span class="tag"><?= e($job['category']) ?></span>
    <h3><a href="job.php?id=<?= (int) $job['id'] ?>"><?= e($job['title']) ?></a></h3>
    <p class="job-author"><?= e($job['company']) ?> · <?= e($job['city']) ?> · <?= e($job['work_format']) ?></p>
    <div class="job-meta">
      <span class="salary"><?= e(format_salary($job['salary'])) ?></span>
      <button type="button" class="fav-btn" data-id="<?= (int) $job['id'] ?>" title="Вакансияны сақтау">🤍</button>
    </div>
  </div>
</article>
