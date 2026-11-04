<?php
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Тіркелу — JumysHub';
$activePage = 'register';
$errors = [];
$success = false;
$registeredName = '';

// Форманы $_POST арқылы өңдеу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $data = [
        'name' => input($_POST, 'name'),
        'email' => input($_POST, 'email'),
        'role' => input($_POST, 'role'),
        'password' => is_string($password) ? $password : '',
        'confirm' => is_string($confirm) ? $confirm : '',
    ];
    $errors = validate_register($data);
    $success = !$errors;
    if ($success) {
        $registeredName = $data['name'];
        $_POST = []; // Сәтті болса, форма тазарады
    }
}

$selectedRole = input($_POST, 'role');

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1>Тіркелу</h1>
      <p class="subtitle">Жаңа аккаунт жасаңыз</p>

      <?php if ($success): ?>
        <div class="message success">Құттықтаймыз, <?= e($registeredName) ?>! Деректер серверде тексерілді. Аккаунт дерекқор қосылғанда сақталады.</div>
      <?php elseif ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="register.php" method="post" novalidate>
        <div class="form-group">
          <label for="name">Аты-жөні</label>
          <input type="text" id="name" name="name" value="<?= old('name') ?>" class="<?= invalid_class($errors, 'name') ?>" placeholder="Атыңыз">
          <p class="error-text"><?= field_error($errors, 'name') ?></p>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= old('email') ?>" class="<?= invalid_class($errors, 'email') ?>" placeholder="example@mail.com">
          <p class="error-text"><?= field_error($errors, 'email') ?></p>
        </div>
        <div class="form-group">
          <label for="role">Рөлі</label>
          <select id="role" name="role" class="<?= invalid_class($errors, 'role') ?>">
            <option value="seeker" <?= $selectedRole === 'seeker' ? 'selected' : '' ?>>Жұмыс іздеуші</option>
            <option value="employer" <?= $selectedRole === 'employer' ? 'selected' : '' ?>>Жұмыс беруші</option>
          </select>
          <p class="error-text"><?= field_error($errors, 'role') ?></p>
        </div>
        <div class="form-group">
          <label for="password">Құпиясөз</label>
          <input type="password" id="password" name="password" class="<?= invalid_class($errors, 'password') ?>" placeholder="Кемінде 6 таңба">
          <p class="error-text"><?= field_error($errors, 'password') ?></p>
        </div>
        <div class="form-group">
          <label for="confirm">Құпиясөзді қайталаңыз</label>
          <input type="password" id="confirm" name="confirm" class="<?= invalid_class($errors, 'confirm') ?>" placeholder="••••••">
          <p class="error-text"><?= field_error($errors, 'confirm') ?></p>
        </div>
        <button type="submit" class="btn btn-block">Тіркелу</button>
      </form>

      <p class="form-footer">Аккаунтыңыз бар ма? <a href="login.php">Кіру</a></p>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
