<?php
// HTML-ге шығарылатын мәтінді қауіпсіз ету (XSS-тен қорғау)
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// $_POST немесе $_GET мәнін тазартылған жол ретінде алу
function input($source, $field)
{
    $value = $source[$field] ?? '';
    return is_string($value) ? trim($value) : '';
}

// Форма қайта ашылғанда POST мәнін сақтау
function old($field)
{
    return e(input($_POST, $field));
}

// Өріс қатесін шығару
function field_error($errors, $field)
{
    return isset($errors[$field]) ? e($errors[$field]) : '';
}

// Қате болса, өріске invalid класын қосу
function invalid_class($errors, $field)
{
    return isset($errors[$field]) ? 'invalid' : '';
}

function format_salary($value)
{
    return number_format($value, 0, '', ' ') . ' ₸';
}

// Басқа бетке бағыттау
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Бір реттік хабарлама (келесі бетте бір рет көрсетіледі)
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Кіруден кейін тек сайт ішіндегі бетке қайтару (сыртқы сілтемеге бағыттаудан қорғау)
function safe_next($next, $default = 'profile.php')
{
    return is_string($next) && preg_match('/^[a-z_]+\.php(\?[\w=&%.-]*)?$/', $next) ? $next : $default;
}

// URL-дегі id параметрін бүтін санға айналдыру
function int_param($source, $field)
{
    $value = filter_var($source[$field] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? null : $value;
}

// Сала бойынша карточка түсі
function category_color($category)
{
    $colors = [
        'IT' => '#2f5d50',
        'Қаржы' => '#8e5a2b',
        'Білім беру' => '#6a3d5a',
        'Медицина' => '#2e8b57',
        'Маркетинг' => '#a03c5a',
        'Басқару' => '#c98d42',
    ];
    return $colors[$category] ?? '#34495e';
}

function work_formats()
{
    return ['Кеңседе', 'Гибрид', 'Қашықтан'];
}

// Вакансия қашан жарияланғанын адамға түсінікті түрде шығару
function time_ago($datetime)
{
    $days = (int) floor((time() - strtotime($datetime)) / 86400);
    if ($days <= 0) {
        return 'Бүгін';
    }
    return $days === 1 ? 'Кеше' : $days . ' күн бұрын';
}

// Вакансиялар тізімі үшін ортақ SELECT бөлігі
function vacancy_select_sql()
{
    return 'SELECT v.id, v.title, v.description, v.salary, v.city, v.work_format, v.created_at, v.updated_at,
                   c.id AS company_id, c.name AS company, c.user_id AS owner_id,
                   cat.id AS category_id, cat.name AS category
            FROM vacancies v
            JOIN companies c ON c.id = v.company_id
            JOIN categories cat ON cat.id = v.category_id';
}

function find_vacancy($id)
{
    $stmt = db()->prepare(vacancy_select_sql() . ' WHERE v.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function validate_vacancy($data, $companyIds, $categoryIds)
{
    $errors = [];
    $titleLength = mb_strlen($data['title']);
    if ($titleLength < 3 || $titleLength > 150) {
        $errors['title'] = 'Лауазым атауы 3–150 таңба болуы керек';
    }
    if (!in_array((int) $data['company_id'], $companyIds, true)) {
        $errors['company_id'] = 'Компанияны таңдаңыз';
    }
    if (!in_array((int) $data['category_id'], $categoryIds, true)) {
        $errors['category_id'] = 'Саланы таңдаңыз';
    }
    if (mb_strlen($data['city']) < 2 || mb_strlen($data['city']) > 60) {
        $errors['city'] = 'Қаланы енгізіңіз';
    }
    if (!in_array($data['work_format'], work_formats(), true)) {
        $errors['work_format'] = 'Жұмыс форматын таңдаңыз';
    }
    $salary = filter_var($data['salary'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 50000, 'max_range' => 10000000]]);
    if ($salary === false) {
        $errors['salary'] = 'Жалақы 50 000 – 10 000 000 ₸ аралығындағы бүтін сан болуы керек';
    }
    if (mb_strlen($data['description']) < 20) {
        $errors['description'] = 'Сипаттама кемінде 20 таңбадан тұруы керек';
    }
    return $errors;
}

// ===== Формаларды серверде тексеру =====
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_login($data)
{
    $errors = [];
    if (!validate_email($data['email'])) {
        $errors['email'] = 'Дұрыс email енгізіңіз';
    }
    if (mb_strlen($data['password']) < 6) {
        $errors['password'] = 'Құпиясөз кемінде 6 таңбадан тұруы керек';
    }
    return $errors;
}

function validate_register($data)
{
    $errors = validate_login($data);
    if (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Атыңызды енгізіңіз';
    }
    if (!in_array($data['role'], ['seeker', 'employer'], true)) {
        $errors['role'] = 'Рөлді таңдаңыз';
    }
    if ($data['role'] === 'employer' && mb_strlen($data['company']) < 2) {
        $errors['company'] = 'Жұмыс беруші компания атауын енгізуі керек';
    }
    if ($data['confirm'] === '' || $data['confirm'] !== $data['password']) {
        $errors['confirm'] = 'Құпиясөздер сәйкес келмейді';
    }
    return $errors;
}

function validate_resume($data)
{
    $errors = [];
    if (mb_strlen($data['fullname']) < 2) {
        $errors['fullname'] = 'Аты-жөніңізді енгізіңіз';
    }
    if ($data['position'] === '') {
        $errors['position'] = 'Қалаған лауазымды енгізіңіз';
    }
    if (!preg_match('/^\+?[\d\s()-]{10,}$/', $data['phone'])) {
        $errors['phone'] = 'Дұрыс телефон нөмірін енгізіңіз';
    }
    if (mb_strlen($data['experience']) < 10) {
        $errors['experience'] = 'Тәжірибеңізді қысқаша жазыңыз';
    }
    return $errors;
}
