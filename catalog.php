<?php
require __DIR__ . '/includes/init.php';

$pageTitle = 'Вакансиялар — JumysHub';
$activePage = 'catalog';

// 13-апта: іздеу, сүзгі, сұрыптау және пагинация толығымен серверде (SQL) орындалады

// 1. Сүзгі мәндерін $_GET арқылы алу
$query = input($_GET, 'q');
$selectedCategory = input($_GET, 'category');
$selectedCity = input($_GET, 'city');
$selectedFormat = input($_GET, 'format');
$period = input($_GET, 'period');
$salaryMin = int_param($_GET, 'salary_min');
$sort = input($_GET, 'sort');
$page = max(1, int_param($_GET, 'page'));
$perPage = 6;

$categories = db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$cities = db()->query('SELECT DISTINCT city FROM vacancies ORDER BY city')->fetchAll(PDO::FETCH_COLUMN);

// Жарияланған күні бойынша сүзгі: мән => [атауы, күн саны]
$periodOptions = [
    '1' => 'Соңғы 24 сағат',
    '7' => 'Соңғы 7 күн',
    '30' => 'Соңғы 30 күн',
];
if (!isset($periodOptions[$period])) {
    $period = '';
}
if (!in_array($selectedFormat, work_formats(), true)) {
    $selectedFormat = '';
}

// 2. Сұрыптау: пайдаланушы мәні SQL-ге тікелей қосылмайды, тек осы тізімнен таңдалады
$sortOptions = [
    'new' => ['Жаңалары алдымен', 'v.created_at DESC, v.id DESC'],
    'old' => ['Ескілері алдымен', 'v.created_at ASC, v.id ASC'],
    'salary' => ['Жалақысы жоғары', 'v.salary DESC, v.id DESC'],
    'salary_asc' => ['Жалақысы төмен', 'v.salary ASC, v.id ASC'],
    'title' => ['Атауы бойынша (А-Я)', 'v.title ASC, v.id ASC'],
];
if (!isset($sortOptions[$sort])) {
    $sort = 'new';
}

// 3. WHERE шарттарын prepared statement параметрлері арқылы құру (SQL инъекциясынан қорғау)
$where = [];
$params = [];
if ($query !== '') {
    $where[] = '(v.title LIKE ? OR c.name LIKE ? OR v.description LIKE ?)';
    $like = '%' . addcslashes($query, '%_\\') . '%';
    array_push($params, $like, $like, $like);
}
if ($selectedCategory !== '') {
    $where[] = 'cat.name = ?';
    $params[] = $selectedCategory;
}
if ($selectedCity !== '') {
    $where[] = 'v.city = ?';
    $params[] = $selectedCity;
}
if ($selectedFormat !== '') {
    $where[] = 'v.work_format = ?';
    $params[] = $selectedFormat;
}
if ($period !== '') {
    $where[] = 'v.created_at >= NOW() - INTERVAL ? DAY';
    $params[] = (int) $period;
}
if ($salaryMin > 0) {
    $where[] = 'v.salary >= ?';
    $params[] = $salaryMin;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// 4. Жалпы санын есептеу (пагинация үшін)
$countStmt = db()->prepare(
    'SELECT COUNT(*) FROM vacancies v
     JOIN companies c ON c.id = v.company_id
     JOIN categories cat ON cat.id = v.category_id' . $whereSql
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// 5. Тек ағымдағы беттің жазбаларын алу: LIMIT ? OFFSET ?
$stmt = db()->prepare(vacancy_select_sql() . $whereSql
    . ' ORDER BY ' . $sortOptions[$sort][1]
    . ' LIMIT ? OFFSET ?');
$i = 1;
foreach ($params as $value) {
    $stmt->bindValue($i++, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($i, $offset, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll();

// Сүзгілерді сақтай отырып, басқа беттің сілтемесін құру
$filters = array_filter([
    'q' => $query,
    'category' => $selectedCategory,
    'city' => $selectedCity,
    'format' => $selectedFormat,
    'period' => $period,
    'salary_min' => $salaryMin ?: '',
    'sort' => $sort === 'new' ? '' : $sort,
], fn($v) => $v !== '');

function page_url($filters, $page)
{
    if ($page > 1) {
        $filters['page'] = $page;
    }
    return 'catalog.php' . ($filters ? '?' . http_build_query($filters) : '');
}

$from = $total ? $offset + 1 : 0;
$to = $offset + count($result);

include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="page-header">
      <div class="container">
        <h1>Вакансиялар</h1>
        <p>Жұмысты іздеңіз, сала, қала және күн бойынша сүзгілеңіз, ұнағанын сақтаңыз</p>
      </div>
    </section>

    <div class="container catalog-layout">
      <!-- Сүзгілер: $_GET арқылы серверге жіберіледі -->
      <form class="filters" action="catalog.php" method="get">
        <h3>Сүзгілер</h3>
        <label for="search">Іздеу</label>
        <input type="text" id="search" name="q" value="<?= e($query) ?>" placeholder="Лауазым, компания, сипаттама">

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

        <label for="format">Жұмыс форматы</label>
        <select id="format" name="format">
          <option value="">Барлығы</option>
          <?php foreach (work_formats() as $format): ?>
            <option value="<?= e($format) ?>" <?= $format === $selectedFormat ? 'selected' : '' ?>><?= e($format) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="period">Жарияланған күні</label>
        <select id="period" name="period">
          <option value="">Кез келген уақыт</option>
          <?php foreach ($periodOptions as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= (string) $value === $period ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="salary_min">Жалақы, кемінде (₸)</label>
        <input type="number" id="salary_min" name="salary_min" min="0" step="50000" value="<?= $salaryMin ?: '' ?>" placeholder="Мысалы, 400000">

        <label for="sort">Сұрыптау</label>
        <select id="sort" name="sort">
          <?php foreach ($sortOptions as $value => [$label]): ?>
            <option value="<?= e($value) ?>" <?= $value === $sort ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-block">Қолдану</button>
        <?php if ($filters): ?>
          <a href="catalog.php" class="btn btn-outline btn-block">Тазалау</a>
        <?php endif; ?>
      </form>

      <!-- Вакансиялар -->
      <section>
        <div class="catalog-top">
          <span>Табылды: <b><?= $total ?></b> вакансия<?= $total ? " · {$from}–{$to} көрсетілген" : '' ?></span>
          <?php if ($totalPages > 1): ?><span><?= $page ?> / <?= $totalPages ?> бет</span><?php endif; ?>
        </div>
        <div class="jobs-grid">
          <?php foreach ($result as $job): ?>
            <?php include __DIR__ . '/includes/job_card.php'; ?>
          <?php endforeach; ?>
        </div>
        <?php if (!$result): ?>
          <p class="empty" style="display:block">Вакансия табылмады 😔</p>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
          <!-- Пагинация: сүзгілер сілтемеде сақталады -->
          <nav class="pagination" aria-label="Беттер">
            <?php if ($page > 1): ?>
              <a href="<?= e(page_url($filters, $page - 1)) ?>">← Алдыңғы</a>
            <?php else: ?>
              <span class="disabled">← Алдыңғы</span>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <?php if ($p === $page): ?>
                <span class="current" aria-current="page"><?= $p ?></span>
              <?php else: ?>
                <a href="<?= e(page_url($filters, $p)) ?>"><?= $p ?></a>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
              <a href="<?= e(page_url($filters, $page + 1)) ?>">Келесі →</a>
            <?php else: ?>
              <span class="disabled">Келесі →</span>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      </section>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
