<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Repository\CategoryRepository;
use Smarty\Smarty;

try {
    /** @var array{pdo: PDO, smarty: Smarty} $application */
    $application = require dirname(__DIR__) . '/app/bootstrap.php';

    $pdo = $application['pdo'];
    $smarty = $application['smarty'];

    /*
     * Пока в приложении реализована только главная страница.
     * На следующем этапе здесь появится простая маршрутизация
     * страниц категорий и отдельных статей.
     */
    $controller = new HomeController(
        new CategoryRepository($pdo),
        $smarty
    );

    $controller->index();
} catch (Throwable $exception) {
    /*
     * Посетителю не показываем реквизиты подключения, SQL
     * и другие внутренние подробности приложения.
     */
    error_log($exception->__toString());

    http_response_code(500);

    echo 'Не удалось выполнить запрос. Подробности записаны в журнал.';
}
