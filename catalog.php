<?php
require __DIR__ . '/includes/functions.php';
$jobs = require __DIR__ . '/data/jobs.php';

$pageTitle = 'Вакансиялар — JumysHub';
$activePage = 'catalog';

// Сүзгі мәндерін $_GET арқылы алу
$query = input($_GET, 'q');
$selectedCategory = input($_GET, 'category');
$selectedCity = input($_GET, 'city');
$sort = input($_GET, 'sort');

$categories = array_unique(array_column($jobs, 'category'));
$cities = array_unique(array_column($jobs, 'city'));
$sortOptions = [
    'new' => 'Жаңалары алдымен',
    'salary' => 'Жалақысы жоғары',
    'title' => 'Атауы бойынша (А-Я)',
];
if (!isset($sortOptions[$sort])) {
    $sort = 'new';
}

// Вакансияларды серверде сүзу
$result = array_filter($jobs, function ($job) use ($query, $selectedCategory, $selectedCity) {
    $search = mb_strtolower($query);
    $matchText = $search === ''
        || mb_strpos(mb_strtolower($job['title']), $search) !== false
        || mb_strpos(mb_strtolower($job['company']), $search) !== false;
    $matchCategory = $selectedCategory === '' || $job['category'] === $selectedCategory;
    $matchCity = $selectedCity === '' || $job['city'] === $selectedCity;
    return $matchText && $matchCategory && $matchCity;
});

// Сұрыптау
usort($result, function ($a, $b) use ($sort) {
    if ($sort === 'salary') {
        return $b['salary'] <=> $a['salary'];
    }
    if ($sort === 'title') {
        return strcmp($a['title'], $b['title']);
    }
    return $a['posted'] <=> $b['posted'];
});

include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="page-header">
      <div class="container">
        <h1>Вакансиялар</h1>
        <p>Жұмысты іздеңіз, сала мен қала бойынша сүзгілеңіз және ұнағанын сақтаңыз</p>
      </div>
    </section>

    <div class="container catalog-layout">
      <!-- Сүзгілер: $_GET арқылы жіберіледі -->
      <form class="filters" action="catalog.php" method="get">
        <h3>Сүзгілер</h3>
        <label for="search">Іздеу</label>
        <input type="text" id="search" name="q" value="<?= e($query) ?>" placeholder="Лауазым немесе компания">

        <label for="category">Сала</label>
        <select id="category" name="category">
          <option value="">Барлығы</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= e($category) ?>" <?= $category === $selectedCategory ? 'selected' : '' ?>><?= e($category) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="city">Қала</label>
        <select id="city" name="city">
          <option value="">Барлығы</option>
          <?php foreach ($cities as $city): ?>
            <option value="<?= e($city) ?>" <?= $city === $selectedCity ? 'selected' : '' ?>><?= e($city) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="sort">Сұрыптау</label>
        <select id="sort" name="sort">
          <?php foreach ($sortOptions as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $value === $sort ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-block">Қолдану</button>
      </form>

      <!-- Вакансиялар -->
      <section>
        <div class="catalog-top">
          <span>Табылды: <?= count($result) ?> вакансия</span>
        </div>
        <div class="jobs-grid">
          <?php foreach ($result as $job): ?>
            <?php include __DIR__ . '/includes/job_card.php'; ?>
          <?php endforeach; ?>
        </div>
        <?php if (!$result): ?>
          <p class="empty" style="display:block">Вакансия табылмады 😔</p>
        <?php endif; ?>
      </section>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
