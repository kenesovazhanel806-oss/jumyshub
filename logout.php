<?php
require __DIR__ . '/includes/init.php';

// Шығу тек POST сұранысымен (CSRF токенімен) орындалады
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
verify_csrf();

logout_user();

// Жаңа сессия ашып, хабарлама қалдыру
session_start();
set_flash('success', 'Сіз жүйеден шықтыңыз.');
redirect('login.php');
