<?php

declare(strict_types=1);

/*
 * Подключение к MySQL вынесено отдельно от веб-окружения.
 * CLI-команды могут работать с базой, не инициализируя Smarty.
 * Иначе возникают проблемы в правми у сидера при развертывании
 */
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST'),
    getenv('DB_PORT'),
    getenv('DB_DATABASE')
);

return new PDO(
    $dsn,
    (string) getenv('DB_USERNAME'),
    (string) getenv('DB_PASSWORD'),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
