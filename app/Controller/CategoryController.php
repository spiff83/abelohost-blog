<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CategoryRepository;
use Smarty\Smarty;

/**
 * Формирует страницу выбранной категории.
 */
final class CategoryController
{
    private const POSTS_PER_PAGE = 6;

    /**
     * Здесь находятся только ключи и названия для интерфейса.
     * Реальные SQL-фрагменты остаются внутри репозитория.
     */
    private const SORT_OPTIONS = [
        [
            'value' => 'date_desc',
            'label' => 'Сначала новые',
        ],
        [
            'value' => 'date_asc',
            'label' => 'Сначала старые',
        ],
        [
            'value' => 'views_desc',
            'label' => 'Сначала популярные',
        ],
        [
            'value' => 'views_asc',
            'label' => 'Сначала менее популярные',
        ],
    ];

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ErrorController $errorController,
        private readonly Smarty $smarty
    ) {
    }

    public function show(string $slug): void
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if ($category === null) {
            $this->errorController->notFound();

            return;
        }

        $sort = $this->resolveSort(
            (string) ($_GET['sort'] ?? 'date_desc')
        );

        /*
         * Отрицательные и нулевые значения приводим к первой странице.
         * Слишком большой номер страницы считаем отсутствующим адресом.
         */
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $totalPosts = $this->categoryRepository->countPosts(
            $category['id']
        );

        $totalPages = max(
            1,
            (int) ceil($totalPosts / self::POSTS_PER_PAGE)
        );

        if ($page > $totalPages) {
            $this->errorController->notFound();

            return;
        }

        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $posts = $this->categoryRepository->findPosts(
            $category['id'],
            $sort,
            self::POSTS_PER_PAGE,
            $offset
        );

        $this->smarty->assign([
            'pageTitle' => sprintf(
                '%s — AbeloHost Blog',
                $category['name']
            ),
            'currentYear' => date('Y'),
            'category' => $category,
            'posts' => $posts,
            'totalPosts' => $totalPosts,
            'sortOptions' => self::SORT_OPTIONS,
            'currentSort' => $sort,
            'pagination' => $this->buildPagination(
                $category['slug'],
                $sort,
                $page,
                $totalPages
            ),
        ]);

        $this->smarty->display('category.tpl');
    }

    private function resolveSort(string $requestedSort): string
    {
        foreach (self::SORT_OPTIONS as $option) {
            if ($option['value'] === $requestedSort) {
                return $requestedSort;
            }
        }

        return 'date_desc';
    }

    /**
     * Формирует готовые ссылки, чтобы Smarty занимался только выводом.
     *
     * @return array<string, mixed>
     */
    private function buildPagination(
        string $categorySlug,
        string $sort,
        int $currentPage,
        int $totalPages
    ): array {
        $pages = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $pages[] = [
                'number' => $page,
                'url' => $this->buildCategoryUrl(
                    $categorySlug,
                    $sort,
                    $page
                ),
                'is_current' => $page === $currentPage,
            ];
        }

        return [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'previousUrl' => $currentPage > 1
                ? $this->buildCategoryUrl(
                    $categorySlug,
                    $sort,
                    $currentPage - 1
                )
                : null,
            'nextUrl' => $currentPage < $totalPages
                ? $this->buildCategoryUrl(
                    $categorySlug,
                    $sort,
                    $currentPage + 1
                )
                : null,
            'pages' => $pages,
        ];
    }

    private function buildCategoryUrl(
        string $categorySlug,
        string $sort,
        int $page
    ): string {
        $parameters = [
            'sort' => $sort,
        ];

        /*
         * Первую страницу оставляем без page=1:
         * адрес получается немного чище.
         */
        if ($page > 1) {
            $parameters['page'] = $page;
        }

        return sprintf(
            '/category/%s?%s',
            rawurlencode($categorySlug),
            http_build_query($parameters)
        );
    }
}
