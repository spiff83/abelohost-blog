<?php

declare(strict_types=1);

$databaseVersion = null;
$databaseError = null;

try {
    // Все реквизиты подключения приходят из Docker Compose.
    // Пароли и адреса базы не хранятся непосредственно в PHP-коде.
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST'),
        getenv('DB_PORT'),
        getenv('DB_DATABASE')
    );

    $pdo = new PDO(
        $dsn,
        (string) getenv('DB_USERNAME'),
        (string) getenv('DB_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $databaseVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
} catch (Throwable $exception) {
    http_response_code(500);
    $databaseError = $exception->getMessage();
}

/**
 * Экранирует текст перед выводом в HTML.
 */
function escape(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тест</title>
</head>
<body>
    <main>
        <h1>Проверка связи</h1>

        <p>PHP: <?= escape(PHP_VERSION) ?></p>

        <?php if ($databaseError === null): ?>
            <p>MySQL: <?= escape((string) $databaseVersion) ?></p>
            <p><strong>Есьб подключение к БД.</strong></p>
        <?php else: ?>
            <p><strong>Ошибка подключения к MySQL:</strong></p>
            <pre><?= escape($databaseError) ?></pre>
        <?php endif; ?>
    </main>
</body>
</html>
