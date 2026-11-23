<?php
// Сессия, аутентификация және CSRF-қорғаныс

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,   // JavaScript cookie-ді оқи алмайды
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

// Жүйеге кірген пайдаланушы (немесе null)
function current_user(): ?array
{
    static $user = false;

    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

// Сәтті кіруден кейін сессияға жазу
function login_user(array $user): void
{
    session_regenerate_id(true); // сессияны ұрлаудан қорғау
    $_SESSION['user_id'] = (int) $user['id'];
}

// Жүйеден шығу: сессияны толық жою
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Жабық бет: тек жүйеге кірген пайдаланушыға
function require_login(): array
{
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Бұл бетті көру үшін жүйеге кіріңіз.');
        redirect('login.php?next=' . urlencode(basename($_SERVER['REQUEST_URI'])));
    }
    return $user;
}

// Жабық бет: тек белгілі рөлге (seeker / employer)
function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        set_flash('error', $role === 'employer'
            ? 'Бұл бет тек жұмыс берушілерге арналған.'
            : 'Бұл бет тек жұмыс іздеушілерге арналған.');
        redirect('profile.php');
    }
    return $user;
}

// ===== CSRF-қорғаныс: әр POST формасына жасырын токен қосылады =====
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Сұраныс жарамсыз (CSRF токені сәйкес емес). Бетті жаңартып, қайталап көріңіз.');
    }
}
