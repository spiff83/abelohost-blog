<?php

declare(strict_types=1);

use Smarty\Smarty;

try {
    /** @var array{pdo: PDO, smarty: Smarty} $application */
    $application = require dirname(__DIR__) . '/app/bootstrap.php';

    $pdo = $application['pdo'];
    $smarty = $application['smarty'];

    /*
     * Это пока только для проверки окружения.
     * Когда сделаю таблицы, главная будет брать из БД категории и последние статьи.
     */
    $mysqlVersion = $pdo
        ->query('SELECT VERSION()')
        ->fetchColumn();

    $smarty->assign([
        'pageTitle' => 'AbeloHost Blog — окружение готово',
        'phpVersion' => PHP_VERSION,
        'mysqlVersion' => (string) $mysqlVersion,
        'smartyVersion' => Smarty::SMARTY_VERSION,
        'currentYear' => date('Y'),
    ]);

    $smarty->display('home.tpl');
} catch (Throwable $exception) {

    /*
     * Все в лог!
     */
    error_log($exception->__toString());

    http_response_code(500);

    echo 'Не удалось запустить приложение. Подробности записаны в журнал.';
}
