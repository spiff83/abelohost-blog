<?php

declare(strict_types=1);

use App\Controller\CategoryController;
use App\Controller\ErrorController;
use App\Controller\HomeController;
use App\Repository\CategoryRepository;
use Smarty\Smarty;

try {
    /** @var array{pdo: PDO, smarty: Smarty} $application */
    $application = require dirname(__DIR__) . '/app/bootstrap.php';

    $pdo = $application['pdo'];
    $smarty = $application['smarty'];

    $categoryRepository = new CategoryRepository($pdo);
    $errorController = new ErrorController($smarty);

    /*
     * Для трёх публичных маршрутов отдельный универсальный Router
     * усложнил бы проект без практической пользы.
     */
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $parsedPath = parse_url($requestUri, PHP_URL_PATH);

    $path = is_string($parsedPath)
        ? '/' . trim($parsedPath, '/')
        : '/';

    if ($path === '/') {
        $controller = new HomeController(
            $categoryRepository,
            $smarty
        );

        $controller->index();

        return;
    }

    if (
        preg_match(
            '#^/category/([a-z0-9-]+)$#',
            $path,
            $matches
        ) === 1
    ) {
        $controller = new CategoryController(
            $categoryRepository,
            $errorController,
            $smarty
        );

        $controller->show($matches[1]);

        return;
    }

    $errorController->notFound();
} catch (Throwable $exception) {
    /*
     * Посетителю не показываем SQL, реквизиты подключения
     * и другие внутренние подробности приложения.
     */
    error_log($exception->__toString());

    http_response_code(500);

    echo 'Не удалось выполнить запрос. Подробности записаны в журнал.';
}
