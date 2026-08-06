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
 * Веб-приложение использует общее подключение к базе,
 * которое также доступно CLI-командам.
 */
$pdo = require $projectRoot . '/app/database.php';

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
