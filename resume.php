<?php
require __DIR__ . '/includes/init.php';

// Жабық бет: тек жұмыс іздеуші
$user = require_role('seeker');

$pageTitle = 'Резюме — JumysHub';
$activePage = 'resume';
$errors = [];

// Пайдаланушының бар резюмесін алу
$stmt = db()->prepare('SELECT * FROM resumes WHERE user_id = ?');
$stmt->execute([$user['id']]);
$resume = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Delete — резюмені жою
    if (($_POST['action'] ?? '') === 'delete') {
        $stmt = db()->prepare('DELETE FROM resumes WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        set_flash('success', 'Резюме жойылды.');
        redirect('resume.php');
    }

    $data = [
        'fullname' => input($_POST, 'fullname'),
        'position' => input($_POST, 'position'),
        'phone' => input($_POST, 'phone'),
        'experience' => input($_POST, 'experience'),
    ];
    $errors = validate_resume($data);

    if (!$errors) {
        if ($resume) {
            // Update — бар резюмені жаңарту
            $stmt = db()->prepare('UPDATE resumes SET full_name = ?, position = ?, phone = ?, experience = ? WHERE user_id = ?');
            $stmt->execute([$data['fullname'], $data['position'], $data['phone'], $data['experience'], $user['id']]);
            set_flash('success', 'Резюме жаңартылды.');
        } else {
            // Create — жаңа резюме қосу
            $stmt = db()->prepare('INSERT INTO resumes (user_id, full_name, position, phone, experience) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $data['fullname'], $data['position'], $data['phone'], $data['experience']]);
            set_flash('success', 'Резюме сақталды.');
        }
        redirect('profile.php');
    }
}

// Форма мәндері: POST қатесі болса — енгізілгені, әйтпесе — дерекқордағысы
$values = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ['fullname' => input($_POST, 'fullname'), 'position' => input($_POST, 'position'), 'phone' => input($_POST, 'phone'), 'experience' => input($_POST, 'experience')]
    : ['fullname' => $resume['full_name'] ?? $user['name'], 'position' => $resume['position'] ?? '', 'phone' => $resume['phone'] ?? '', 'experience' => $resume['experience'] ?? ''];

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1><?= $resume ? 'Резюмені өңдеу' : 'Резюме жасау' ?></h1>
      <p class="subtitle">Өзіңіз туралы негізгі ақпаратты толтырыңыз</p>

      <?php if ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="resume.php" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="fullname">Аты-жөні</label>
          <input type="text" id="fullname" name="fullname" value="<?= e($values['fullname']) ?>" class="<?= invalid_class($errors, 'fullname') ?>" placeholder="Аты-жөніңіз" maxlength="100">
          <p class="error-text"><?= field_error($errors, 'fullname') ?></p>
        </div>
        <div class="form-group">
          <label for="position">Қалаған лауазым</label>
          <input type="text" id="position" name="position" value="<?= e($values['position']) ?>" class="<?= invalid_class($errors, 'position') ?>" placeholder="Мысалы: Frontend әзірлеуші" maxlength="150">
          <p class="error-text"><?= field_error($errors, 'position') ?></p>
        </div>
        <div class="form-group">
          <label for="phone">Телефон</label>
          <input type="tel" id="phone" name="phone" value="<?= e($values['phone']) ?>" class="<?= invalid_class($errors, 'phone') ?>" placeholder="+7 700 000 00 00" maxlength="30">
          <p class="error-text"><?= field_error($errors, 'phone') ?></p>
        </div>
        <div class="form-group">
          <label for="experience">Тәжірибе және дағдылар</label>
          <textarea id="experience" name="experience" rows="5" class="<?= invalid_class($errors, 'experience') ?>" placeholder="Жұмыс тәжірибеңіз бен дағдыларыңыз"><?= e($values['experience']) ?></textarea>
          <p class="error-text"><?= field_error($errors, 'experience') ?></p>
        </div>
        <button type="submit" class="btn btn-block"><?= $resume ? 'Өзгерістерді сақтау' : 'Резюмені сақтау' ?></button>
      </form>

      <?php if ($resume): ?>
        <form action="resume.php" method="post" class="delete-form" data-confirm="Резюмені шынымен жою керек пе?">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="btn btn-danger btn-block">Резюмені жою</button>
        </form>
      <?php endif; ?>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
