<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Smarty\Smarty;

/**
 * Формирует страницу отдельной статьи.
 */
final class PostController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly ErrorController $errorController,
        private readonly Smarty $smarty
    ) {
    }

    public function show(string $slug): void
    {
        $post = $this->postRepository->findBySlug($slug);

        if ($post === null) {
            $this->errorController->notFound();

            return;
        }

        $categories = $this->postRepository->findCategories(
            $post['id']
        );

        $relatedPosts = $this->postRepository->findRelatedPosts(
            $post['id'],
            3
        );

        /*
         * Сидер разделяет абзацы пустой строкой.
         * Передаём шаблону готовый массив, чтобы не выводить
         * полный текст через небезопасный модификатор nofilter.
         */
        $bodyParagraphs = preg_split(
            '/\R{2,}/u',
            trim($post['body'])
        );

        if ($bodyParagraphs === false) {
            $bodyParagraphs = [$post['body']];
        }

        /*
         * Счётчик увеличивается отдельным атомарным UPDATE.
         * В объекте статьи сразу отражаем новое значение,
         * чтобы посетитель видел актуальное число.
         */
        $this->postRepository->incrementViews($post['id']);
        $post['views']++;

        $this->smarty->assign([
            'pageTitle' => sprintf(
                '%s — AbeloHost Blog',
                $post['title']
            ),
            'pageDescription' => $post['description'],
            'currentYear' => date('Y'),
            'post' => $post,
            'bodyParagraphs' => $bodyParagraphs,
            'categories' => $categories,
            'relatedPosts' => $relatedPosts,
        ]);

        $this->smarty->display('post.tpl');
    }
}
