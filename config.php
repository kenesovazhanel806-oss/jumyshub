<?php
// Дерекқор баптаулары (XAMPP әдепкі мәндері)
$config = [
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'jumyshub',
    'db_user' => 'root',
    'db_pass' => '',
];

// Жергілікті компьютерге арналған өзгерістер (мысалы, басқа порт) config.local.php файлында сақталады.
// Бұл файл GitHub-қа жүктелмейді (.gitignore).
if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_merge($config, require __DIR__ . '/config.local.php');
}

return $config;
