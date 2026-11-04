<?php
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Резюме жасау — JumysHub';
$activePage = 'resume';
$errors = [];
$resume = null;

// Форманы $_POST арқылы өңдеу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'fullname' => input($_POST, 'fullname'),
        'position' => input($_POST, 'position'),
        'phone' => input($_POST, 'phone'),
        'experience' => input($_POST, 'experience'),
    ];
    $errors = validate_resume($data);
    if (!$errors) {
        $resume = $data;
    }
}

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1>Резюме жасау</h1>
      <p class="subtitle">Өзіңіз туралы негізгі ақпаратты толтырыңыз</p>

      <?php if ($resume): ?>
        <div class="message success">Резюме серверде тексерілді. Ол дерекқор қосылғанда сақталады.</div>
        <div class="resume-result">
          <p><strong>Аты-жөні:</strong> <?= e($resume['fullname']) ?></p>
          <p><strong>Лауазым:</strong> <?= e($resume['position']) ?></p>
          <p><strong>Телефон:</strong> <?= e($resume['phone']) ?></p>
          <p><strong>Тәжірибе:</strong> <?= nl2br(e($resume['experience'])) ?></p>
        </div>
      <?php elseif ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="resume.php" method="post" novalidate>
        <div class="form-group">
          <label for="fullname">Аты-жөні</label>
          <input type="text" id="fullname" name="fullname" value="<?= old('fullname') ?>" class="<?= invalid_class($errors, 'fullname') ?>" placeholder="Аты-жөніңіз">
          <p class="error-text"><?= field_error($errors, 'fullname') ?></p>
        </div>
        <div class="form-group">
          <label for="position">Қалаған лауазым</label>
          <input type="text" id="position" name="position" value="<?= old('position') ?>" class="<?= invalid_class($errors, 'position') ?>" placeholder="Мысалы: Frontend әзірлеуші">
          <p class="error-text"><?= field_error($errors, 'position') ?></p>
        </div>
        <div class="form-group">
          <label for="phone">Телефон</label>
          <input type="tel" id="phone" name="phone" value="<?= old('phone') ?>" class="<?= invalid_class($errors, 'phone') ?>" placeholder="+7 700 000 00 00">
          <p class="error-text"><?= field_error($errors, 'phone') ?></p>
        </div>
        <div class="form-group">
          <label for="experience">Тәжірибе және дағдылар</label>
          <textarea id="experience" name="experience" rows="5" class="<?= invalid_class($errors, 'experience') ?>" placeholder="Жұмыс тәжірибеңіз бен дағдыларыңыз"><?= old('experience') ?></textarea>
          <p class="error-text"><?= field_error($errors, 'experience') ?></p>
        </div>
        <button type="submit" class="btn btn-block">Резюмені тексеру</button>
      </form>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
