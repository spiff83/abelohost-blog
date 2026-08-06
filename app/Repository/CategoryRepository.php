<?php

declare(strict_types=1);

namespace App\Repository;

use InvalidArgumentException;
use PDO;

/**
 * Выполняет запросы, связанные с категориями блога.
 */
final class CategoryRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /**
     * Возвращает категории и несколько последних статей каждой категории.
     *
     * Категории без опубликованных статей в результат не попадают.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     description: string,
     *     posts: array<int, array{
     *         id: int,
     *         image_path: string,
     *         title: string,
     *         slug: string,
     *         description: string,
     *         views: int,
     *         published_at: string
     *     }>
     * }>
     */
    public function findWithLatestPosts(int $postsPerCategory = 3): array
    {
        if ($postsPerCategory < 1) {
            throw new InvalidArgumentException(
                'Количество статей на категорию должно быть больше нуля.'
            );
        }

        /*
         * ROW_NUMBER нумерует статьи отдельно внутри каждой категории.
         * Благодаря этому одним запросом получаем по несколько последних
         * публикаций и не выполняем отдельный запрос для каждой категории.
         */
        $sql = <<<'SQL'
            SELECT
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                c.description AS category_description,
                ranked_posts.id AS post_id,
                ranked_posts.image_path,
                ranked_posts.title AS post_title,
                ranked_posts.slug AS post_slug,
                ranked_posts.description AS post_description,
                ranked_posts.views,
                ranked_posts.published_at
            FROM categories AS c
            INNER JOIN (
                SELECT
                    cp.category_id,
                    p.id,
                    p.image_path,
                    p.title,
                    p.slug,
                    p.description,
                    p.views,
                    p.published_at,
                    ROW_NUMBER() OVER (
                        PARTITION BY cp.category_id
                        ORDER BY p.published_at DESC, p.id DESC
                    ) AS post_rank
                FROM category_post AS cp
                INNER JOIN posts AS p
                    ON p.id = cp.post_id
                WHERE p.published_at <= NOW()
            ) AS ranked_posts
                ON ranked_posts.category_id = c.id
                AND ranked_posts.post_rank <= :posts_per_category
            ORDER BY
                c.id ASC,
                ranked_posts.published_at DESC,
                ranked_posts.id DESC
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(
            ':posts_per_category',
            $postsPerCategory,
            PDO::PARAM_INT
        );
        $statement->execute();

        /*
         * SQL возвращает плоский набор строк. Группируем его в PHP,
         * чтобы шаблон получил естественную структуру:
         * категория → массив статей.
         */
        $categories = [];

        while ($row = $statement->fetch()) {
            $categoryId = (int) $row['category_id'];

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'id' => $categoryId,
                    'name' => (string) $row['category_name'],
                    'slug' => (string) $row['category_slug'],
                    'description' => (string) $row['category_description'],
                    'posts' => [],
                ];
            }

            $categories[$categoryId]['posts'][] = [
                'id' => (int) $row['post_id'],
                'image_path' => (string) $row['image_path'],
                'title' => (string) $row['post_title'],
                'slug' => (string) $row['post_slug'],
                'description' => (string) $row['post_description'],
                'views' => (int) $row['views'],
                'published_at' => (string) $row['published_at'],
            ];
        }

        return array_values($categories);
    }
}
