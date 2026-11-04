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
