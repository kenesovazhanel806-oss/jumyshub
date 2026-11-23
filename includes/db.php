<?php
// MySQL дерекқорына PDO арқылы қосылу.
// Қосылу бір рет жасалып, кейін қайта қолданылады.
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = require __DIR__ . '/../config.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['db_host'],
            $config['db_port'],
            $config['db_name']
        );

        try {
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // қателерді Exception ретінде шығару
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // нәтиже — ассоциативті массив
                PDO::ATTR_EMULATE_PREPARES => false,               // нағыз prepared statements
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Дерекқорға қосылу мүмкін болмады. XAMPP-та MySQL іске қосылғанын және database/jumyshub.sql импортталғанын тексеріңіз.');
        }
    }

    return $pdo;
}
