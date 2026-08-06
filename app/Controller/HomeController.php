<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryRepository;
use Smarty\Smarty;

/**
 * Формирует главную страницу блога.
 */
final class HomeController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly Smarty $smarty
    ) {
    }

    public function index(): void
    {
        $categories = $this->categoryRepository->findWithLatestPosts(3);

        $this->smarty->assign([
            'pageTitle' => 'AbeloHost Blog',
            'currentYear' => date('Y'),
            'categories' => $categories,
        ]);

        $this->smarty->display('home.tpl');
    }
}
