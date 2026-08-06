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
    /**
     * SQL-фрагменты выбираются только из этого внутреннего списка.
     * Значение GET-параметра никогда не подставляется в запрос напрямую.
     */
    private const SORT_ORDERS = [
        'date_desc' => 'p.published_at DESC, p.id DESC',
        'date_asc' => 'p.published_at ASC, p.id ASC',
        'views_desc' => 'p.views DESC, p.id DESC',
        'views_asc' => 'p.views ASC, p.id ASC',
    ];

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /**
     * Возвращает категории и несколько последних статей каждой категории.
     *
     * Категории без опубликованных статей в результат не попадают.
     *
     * @return array<int, array<string, mixed>>
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
         * чтобы шаблон получил структуру категория -> массив статей.
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

            $categories[$categoryId]['posts'][] = $this->mapPost($row);
        }

        return array_values($categories);
    }

    /**
     * Поиск категорию по её адресу.
     *
     * @return array{id: int, name: string, slug: string, description: string}|null
     */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                slug,
                description
             FROM categories
             WHERE slug = :slug
             LIMIT 1'
        );

        $statement->execute(['slug' => $slug]);

        $category = $statement->fetch();

        if ($category === false) {
            return null;
        }

        return [
            'id' => (int) $category['id'],
            'name' => (string) $category['name'],
            'slug' => (string) $category['slug'],
            'description' => (string) $category['description'],
        ];
    }

    /**
     * Возвращается количество опубликованных статей категории.
     */
    public function countPosts(int $categoryId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM category_post AS cp
             INNER JOIN posts AS p
                ON p.id = cp.post_id
             WHERE cp.category_id = :category_id
               AND p.published_at <= NOW()'
        );

        $statement->execute([
            'category_id' => $categoryId,
        ]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Возвращается одну страницу статей категории.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findPosts(
        int $categoryId,
        string $sort,
        int $limit,
        int $offset
    ): array {
        if ($limit < 1 || $offset < 0) {
            throw new InvalidArgumentException(
                'Некорректные параметры пагинации.'
            );
        }

        /*
         * В ORDER BY нельзя передать имя поля через параметр PDO.
         * Поэтому используем только заранее заданный белый список.
         */
        $orderBy = self::SORT_ORDERS[$sort]
            ?? self::SORT_ORDERS['date_desc'];

        $sql = sprintf(
            'SELECT
                p.id,
                p.image_path,
                p.title,
                p.slug,
                p.description,
                p.views,
                p.published_at
             FROM category_post AS cp
             INNER JOIN posts AS p
                ON p.id = cp.post_id
             WHERE cp.category_id = :category_id
               AND p.published_at <= NOW()
             ORDER BY %s
             LIMIT :limit
             OFFSET :offset',
            $orderBy
        );

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(
            ':category_id',
            $categoryId,
            PDO::PARAM_INT
        );
        $statement->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );
        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );
        $statement->execute();

        $posts = [];

        while ($row = $statement->fetch()) {
            $posts[] = $this->mapPost($row);
        }

        return $posts;
    }

    /**
     * Приведение данных статьи из PDO к предсказуемым типам.
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function mapPost(array $row): array
    {
        return [
            'id' => (int) ($row['post_id'] ?? $row['id']),
            'image_path' => (string) $row['image_path'],
            'title' => (string) ($row['post_title'] ?? $row['title']),
            'slug' => (string) ($row['post_slug'] ?? $row['slug']),
            'description' => (string) (
                $row['post_description'] ?? $row['description']
            ),
            'views' => (int) $row['views'],
            'published_at' => (string) $row['published_at'],
        ];
    }
}
