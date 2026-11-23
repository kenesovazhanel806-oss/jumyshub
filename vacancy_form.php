<?php
require __DIR__ . '/includes/init.php';

// Жабық бет: тек жұмыс беруші
$user = require_role('employer');

// ?id= болса — өңдеу (Update), болмаса — жаңа вакансия (Create)
$id = int_param($_GET, 'id');
$vacancy = null;

if ($id !== null) {
    $vacancy = find_vacancy($id);
    // Басқа жұмыс берушінің вакансиясын өңдеуге тыйым салынады
    if (!$vacancy || (int) $vacancy['owner_id'] !== (int) $user['id']) {
        set_flash('error', 'Вакансия табылмады немесе оны өңдеуге рұқсатыңыз жоқ.');
        redirect('employer.php');
    }
}

$isEdit = $vacancy !== null;
$pageTitle = ($isEdit ? 'Вакансияны өңдеу' : 'Жаңа вакансия') . ' — JumysHub';
$activePage = 'employer';
$errors = [];

// Таңдау тізімдері
$stmt = db()->prepare('SELECT id, name FROM companies WHERE user_id = ? ORDER BY name');
$stmt->execute([$user['id']]);
$companies = $stmt->fetchAll();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'title' => input($_POST, 'title'),
        'company_id' => input($_POST, 'company_id'),
        'category_id' => input($_POST, 'category_id'),
        'city' => input($_POST, 'city'),
        'work_format' => input($_POST, 'work_format'),
        'salary' => str_replace(' ', '', input($_POST, 'salary')),
        'description' => input($_POST, 'description'),
    ];
    $errors = validate_vacancy(
        $data,
        array_map('intval', array_column($companies, 'id')),
        array_map('intval', array_column($categories, 'id'))
    );

    if (!$errors) {
        $params = [
            (int) $data['company_id'],
            (int) $data['category_id'],
            $data['title'],
            $data['description'],
            (int) $data['salary'],
            $data['city'],
            $data['work_format'],
        ];

        if ($isEdit) {
            // Update — prepared statement арқылы жаңарту
            $stmt = db()->prepare('UPDATE vacancies
                                   SET company_id = ?, category_id = ?, title = ?, description = ?, salary = ?, city = ?, work_format = ?
                                   WHERE id = ?');
            $stmt->execute([...$params, $vacancy['id']]);
            set_flash('success', 'Вакансия жаңартылды.');
        } else {
            // Create — prepared statement арқылы қосу
            $stmt = db()->prepare('INSERT INTO vacancies (company_id, category_id, title, description, salary, city, work_format)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute($params);
            set_flash('success', 'Вакансия қосылды.');
        }
        redirect('employer.php');
    }
}

// Форма мәндері
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = $data;
} elseif ($isEdit) {
    $values = [
        'title' => $vacancy['title'],
        'company_id' => (string) $vacancy['company_id'],
        'category_id' => (string) $vacancy['category_id'],
        'city' => $vacancy['city'],
        'work_format' => $vacancy['work_format'],
        'salary' => (string) $vacancy['salary'],
        'description' => $vacancy['description'],
    ];
} else {
    $values = ['title' => '', 'company_id' => '', 'category_id' => '', 'city' => '', 'work_format' => 'Кеңседе', 'salary' => '', 'description' => ''];
}

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card wide">
      <h1><?= $isEdit ? 'Вакансияны өңдеу' : 'Жаңа вакансия' ?></h1>
      <p class="subtitle"><?= $isEdit ? 'Өзгерістерді енгізіп, сақтаңыз' : 'Вакансия туралы ақпаратты толтырыңыз' ?></p>

      <?php if ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="vacancy_form.php<?= $isEdit ? '?id=' . (int) $vacancy['id'] : '' ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="title">Лауазым</label>
          <input type="text" id="title" name="title" value="<?= e($values['title']) ?>" class="<?= invalid_class($errors, 'title') ?>" placeholder="Мысалы: Frontend әзірлеуші" maxlength="150">
          <p class="error-text"><?= field_error($errors, 'title') ?></p>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="company_id">Компания</label>
            <select id="company_id" name="company_id" class="<?= invalid_class($errors, 'company_id') ?>">
              <option value="">— Таңдаңыз —</option>
              <?php foreach ($companies as $company): ?>
                <option value="<?= (int) $company['id'] ?>" <?= (string) $company['id'] === (string) $values['company_id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="error-text"><?= field_error($errors, 'company_id') ?></p>
          </div>
          <div class="form-group">
            <label for="category_id">Сала</label>
            <select id="category_id" name="category_id" class="<?= invalid_class($errors, 'category_id') ?>">
              <option value="">— Таңдаңыз —</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (string) $category['id'] === (string) $values['category_id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="error-text"><?= field_error($errors, 'category_id') ?></p>
          </div>
          <div class="form-group">
            <label for="city">Қала</label>
            <input type="text" id="city" name="city" value="<?= e($values['city']) ?>" class="<?= invalid_class($errors, 'city') ?>" placeholder="Мысалы: Алматы" maxlength="60">
            <p class="error-text"><?= field_error($errors, 'city') ?></p>
          </div>
          <div class="form-group">
            <label for="work_format">Жұмыс форматы</label>
            <select id="work_format" name="work_format" class="<?= invalid_class($errors, 'work_format') ?>">
              <?php foreach (work_formats() as $format): ?>
                <option value="<?= e($format) ?>" <?= $format === $values['work_format'] ? 'selected' : '' ?>><?= e($format) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="error-text"><?= field_error($errors, 'work_format') ?></p>
          </div>
        </div>

        <div class="form-group">
          <label for="salary">Жалақы (₸)</label>
          <input type="number" id="salary" name="salary" value="<?= e($values['salary']) ?>" class="<?= invalid_class($errors, 'salary') ?>" placeholder="Мысалы: 400000" min="50000" max="10000000" step="1000">
          <p class="error-text"><?= field_error($errors, 'salary') ?></p>
        </div>
        <div class="form-group">
          <label for="description">Сипаттама және талаптар</label>
          <textarea id="description" name="description" rows="6" class="<?= invalid_class($errors, 'description') ?>" placeholder="Міндеттер, талаптар, жұмыс шарттары"><?= e($values['description']) ?></textarea>
          <p class="error-text"><?= field_error($errors, 'description') ?></p>
        </div>

        <div class="form-actions">
          <a href="employer.php" class="btn btn-outline">Болдырмау</a>
          <button type="submit" class="btn"><?= $isEdit ? 'Өзгерістерді сақтау' : 'Вакансияны жариялау' ?></button>
        </div>
      </form>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
