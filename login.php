<?php
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Кіру — JumysHub';
$activePage = 'login';
$errors = [];
$success = false;

// Форманы $_POST арқылы өңдеу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'email' => input($_POST, 'email'),
        'password' => $_POST['password'] ?? '',
    ];
    if (!is_string($data['password'])) {
        $data['password'] = '';
    }
    $errors = validate_login($data);
    $success = !$errors;
}

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1>Жүйеге кіру</h1>
      <p class="subtitle">Аккаунтыңызға кіріңіз</p>

      <?php if ($success): ?>
        <div class="message success">Деректер серверде тексерілді. Аккаунтты тексеру дерекқор қосылғанда іске асады.</div>
      <?php elseif ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="login.php" method="post" novalidate>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= old('email') ?>" class="<?= invalid_class($errors, 'email') ?>" placeholder="example@mail.com">
          <p class="error-text"><?= field_error($errors, 'email') ?></p>
        </div>
        <div class="form-group">
          <label for="password">Құпиясөз</label>
          <input type="password" id="password" name="password" class="<?= invalid_class($errors, 'password') ?>" placeholder="••••••">
          <p class="error-text"><?= field_error($errors, 'password') ?></p>
        </div>
        <button type="submit" class="btn btn-block">Кіру</button>
      </form>

      <p class="form-footer">Аккаунтыңыз жоқ па? <a href="register.php">Тіркелу</a></p>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
