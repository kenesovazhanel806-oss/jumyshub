<?php
require __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$pageTitle = 'Кіру — JumysHub';
$activePage = 'login';
$errors = [];
$loginFailed = false;
$next = safe_next($_GET['next'] ?? ($_POST['next'] ?? ''));

// Форманы $_POST арқылы өңдеу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $password = $_POST['password'] ?? '';
    $data = [
        'email' => mb_strtolower(input($_POST, 'email')),
        'password' => is_string($password) ? $password : '',
    ];
    $errors = validate_login($data);

    if (!$errors) {
        $stmt = db()->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        // password_verify() енгізілген құпиясөзді хэшпен салыстырады
        if ($user && password_verify($data['password'], $user['password_hash'])) {
            login_user($user);
            set_flash('success', 'Қош келдіңіз, ' . $user['name'] . '!');
            redirect($next);
        }

        // Email немесе құпиясөздің қайсысы қате екенін айтпаймыз (қауіпсіздік үшін)
        $loginFailed = true;
    }
}

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1>Жүйеге кіру</h1>
      <p class="subtitle">Аккаунтыңызға кіріңіз</p>

      <?php if ($loginFailed): ?>
        <div class="message error">Email немесе құпиясөз қате</div>
      <?php elseif ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="login.php" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
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
      <p class="form-footer demo-hint">Демо: employer@jumyshub.kz / seeker@jumyshub.kz, құпиясөзі <b>demo123</b></p>
    </div>
  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
