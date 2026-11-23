<?php
require __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$pageTitle = 'Тіркелу — JumysHub';
$activePage = 'register';
$errors = [];

// Форманы $_POST арқылы өңдеу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $data = [
        'name' => input($_POST, 'name'),
        'email' => mb_strtolower(input($_POST, 'email')),
        'role' => input($_POST, 'role'),
        'company' => input($_POST, 'company'),
        'password' => is_string($password) ? $password : '',
        'confirm' => is_string($confirm) ? $confirm : '',
    ];
    $errors = validate_register($data);

    // Email бос па — дерекқордан тексеру (prepared statement)
    if (!isset($errors['email'])) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Бұл email бұрын тіркелген';
        }
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();

        // Құпиясөз ашық күйінде емес, password_hash() арқылы хэш түрінде сақталады
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), $data['role']]);
        $userId = (int) $pdo->lastInsertId();

        // Жұмыс берушіге компания бірден құрылады
        if ($data['role'] === 'employer') {
            $stmt = $pdo->prepare('INSERT INTO companies (user_id, name) VALUES (?, ?)');
            $stmt->execute([$userId, $data['company']]);
        }

        $pdo->commit();

        login_user(['id' => $userId]);
        set_flash('success', 'Құттықтаймыз, ' . $data['name'] . '! Аккаунт сәтті құрылды.');
        redirect($data['role'] === 'employer' ? 'employer.php' : 'resume.php');
    }
}

$selectedRole = input($_POST, 'role') ?: 'seeker';

include __DIR__ . '/includes/header.php';
?>
  <main class="auth-page">
    <div class="auth-card">
      <h1>Тіркелу</h1>
      <p class="subtitle">Жаңа аккаунт жасаңыз</p>

      <?php if ($errors): ?>
        <div class="message error">Қателерді түзетіңіз</div>
      <?php endif; ?>

      <form action="register.php" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="name">Аты-жөні</label>
          <input type="text" id="name" name="name" value="<?= old('name') ?>" class="<?= invalid_class($errors, 'name') ?>" placeholder="Атыңыз" maxlength="100">
          <p class="error-text"><?= field_error($errors, 'name') ?></p>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= old('email') ?>" class="<?= invalid_class($errors, 'email') ?>" placeholder="example@mail.com" maxlength="150">
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
        <div class="form-group" id="company-group">
          <label for="company">Компания атауы <span class="hint">(тек жұмыс беруші үшін)</span></label>
          <input type="text" id="company" name="company" value="<?= old('company') ?>" class="<?= invalid_class($errors, 'company') ?>" placeholder="Мысалы: TechNova" maxlength="150">
          <p class="error-text"><?= field_error($errors, 'company') ?></p>
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
