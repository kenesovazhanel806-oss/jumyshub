<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Вакансиялар — JumysHub';
$activePage = 'catalog';

// Сүзгі мәндерін $_GET арқылы алу
$query = input($_GET, 'q');
$selectedCategory = input($_GET, 'category');
$selectedCity = input($_GET, 'city');
$sort = input($_GET, 'sort');

$categories = db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$cities = db()->query('SELECT DISTINCT city FROM vacancies ORDER BY city')->fetchAll(PDO::FETCH_COLUMN);

// Сұрыптау: пайдаланушы мәні SQL-ге тікелей қосылмайды, тек осы тізімнен таңдалады
$sortOptions = [
    'new' => ['Жаңалары алдымен', 'v.created_at DESC'],
    'salary' => ['Жалақысы жоғары', 'v.salary DESC'],
    'title' => ['Атауы бойынша (А-Я)', 'v.title ASC'],
];
if (!isset($sortOptions[$sort])) {
    $sort = 'new';
}

// Сүзгілерді prepared statement параметрлері арқылы құру (SQL инъекциясынан қорғау)
$where = [];
$params = [];
if ($query !== '') {
    $where[] = '(v.title LIKE ? OR c.name LIKE ?)';
    $like = '%' . addcslashes($query, '%_\\') . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($selectedCategory !== '') {
    $where[] = 'cat.name = ?';
    $params[] = $selectedCategory;
}
if ($selectedCity !== '') {
    $where[] = 'v.city = ?';
    $params[] = $selectedCity;
}

$sql = vacancy_select_sql()
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY ' . $sortOptions[$sort][1];
$stmt = db()->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();

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
          <?php foreach ($sortOptions as $value => [$label]): ?>
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
