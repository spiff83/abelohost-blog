<?php

declare(strict_types=1);

namespace App\Controller;

use Smarty\Smarty;

/**
 * Отображает стандартные страницы ошибок.
 */
final class ErrorController
{
    public function __construct(
        private readonly Smarty $smarty
    ) {
    }

    public function notFound(): void
    {
        http_response_code(404);

        $this->smarty->assign([
            'pageTitle' => 'Страница не найдена — AbeloHost Blog',
            'currentYear' => date('Y'),
        ]);

        $this->smarty->display('404.tpl');
    }
}
