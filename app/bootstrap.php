<?php

declare(strict_types=1);

use Smarty\Smarty;

require dirname(__DIR__) . '/vendor/autoload.php';

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/app/helpers.php';

/*
 * Скомпилированные шаблоны и кэш буду хранить во временном каталоге, чтобы Apache мог свободно
 * править без проблем с правами на каталог.
 */
$smartyRuntimePath = sys_get_temp_dir() . '/abelohost-blog/smarty';
$smartyCompilePath = $smartyRuntimePath . '/templates_c';
$smartyCachePath = $smartyRuntimePath . '/cache';

foreach ([$smartyCompilePath, $smartyCachePath] as $directory) {
    if (
        !is_dir($directory)
        && !mkdir($directory, 0775, true)
        && !is_dir($directory)
    ) {
        throw new RuntimeException(
            sprintf('Не удалось создать служебный каталог Smarty: %s', $directory)
        );
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(
            sprintf('Каталог Smarty недоступен для записи: %s', $directory)
        );
    }
}

/*
 * Подключение к MySQL создаётся один раз при инициализации приложения.
 * Контроллеры и репозитории уже потом будут получать готовый PDO-объект.
 */
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

/*
 * Smarty отвечает только за представление.
 * Автоматическое экранирование сделаю для защиты вывода переменных
 * от всякой левой вставки HTML-кода.
 */
$smarty = new Smarty();

$smarty
    ->setTemplateDir($projectRoot . '/templates')
    ->setCompileDir($smartyCompilePath)
    ->setCacheDir($smartyCachePath)
    ->setEscapeHtml(true);

/*
 * Модификатор для склонения существительных сразу в шаблонах,
 */
$smarty->registerPlugin(
    'modifier',
    'plural_ru',
    static fn (
        mixed $number,
        string $one,
        string $few,
        string $many
    ): string => pluralizeRussian(
        (int) $number,
        $one,
        $few,
        $many
    )
);

return [
    'pdo' => $pdo,
    'smarty' => $smarty,
];
