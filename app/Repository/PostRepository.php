<?php

declare(strict_types=1);

namespace App\Repository;

use InvalidArgumentException;
use PDO;

/**
 * Выполняет запросы, связанные с отдельными статьями.
 */
final class PostRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /**
     * Ищет опубликованную статью по её адресу.
     *
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                image_path,
                title,
                slug,
                description,
                body,
                views,
                published_at
             FROM posts
             WHERE slug = :slug
               AND published_at <= NOW()
             LIMIT 1'
        );

        $statement->execute([
            'slug' => $slug,
        ]);

        $post = $statement->fetch();

        if ($post === false) {
            return null;
        }

        return $this->mapPost($post);
    }

    /**
     * Атомарно увеличивает счётчик просмотров статьи.
     *
     * Выражение views = views + 1 выполняется непосредственно в MySQL,
     * поэтому параллельные запросы не перезаписывают результат друг друга.
     */
    public function incrementViews(int $postId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE posts
             SET views = views + 1
             WHERE id = :post_id'
        );

        $statement->execute([
            'post_id' => $postId,
        ]);
    }

    /**
     * Возвращает категории выбранной статьи.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     slug: string
     * }>
     */
    public function findCategories(int $postId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                c.id,
                c.name,
                c.slug
             FROM category_post AS cp
             INNER JOIN categories AS c
                ON c.id = cp.category_id
             WHERE cp.post_id = :post_id
             ORDER BY c.name ASC'
        );

        $statement->execute([
            'post_id' => $postId,
        ]);

        $categories = [];

        while ($category = $statement->fetch()) {
            $categories[] = [
                'id' => (int) $category['id'],
                'name' => (string) $category['name'],
                'slug' => (string) $category['slug'],
            ];
        }

        return $categories;
    }

    /**
     * Возвращает статьи, имеющие общие категории с текущей.
     *
     * Сначала идут публикации с наибольшим количеством общих
     * категорий, затем более свежие статьи.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findRelatedPosts(
        int $postId,
        int $limit = 3
    ): array {
        if ($limit < 1) {
            throw new InvalidArgumentException(
                'Количество похожих статей должно быть больше нуля.'
            );
        }

        $statement = $this->pdo->prepare(
            'SELECT
                p.id,
                p.image_path,
                p.title,
                p.slug,
                p.description,
                p.views,
                p.published_at,
                COUNT(DISTINCT candidate_relation.category_id)
                    AS shared_categories
             FROM category_post AS current_relation
             INNER JOIN category_post AS candidate_relation
                ON candidate_relation.category_id =
                    current_relation.category_id
                AND candidate_relation.post_id <>
                    current_relation.post_id
             INNER JOIN posts AS p
                ON p.id = candidate_relation.post_id
             WHERE current_relation.post_id = :post_id
               AND p.published_at <= NOW()
             GROUP BY
                p.id,
                p.image_path,
                p.title,
                p.slug,
                p.description,
                p.views,
                p.published_at
             ORDER BY
                shared_categories DESC,
                p.published_at DESC,
                p.id DESC
             LIMIT :limit'
        );

        $statement->bindValue(
            ':post_id',
            $postId,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $statement->execute();

        $posts = [];

        while ($post = $statement->fetch()) {
            $posts[] = $this->mapPost($post);
        }

        return $posts;
    }

    /**
     * Приводит значения PDO к ожидаемым типам PHP.
     *
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    private function mapPost(array $post): array
    {
        return [
            'id' => (int) $post['id'],
            'image_path' => (string) $post['image_path'],
            'title' => (string) $post['title'],
            'slug' => (string) $post['slug'],
            'description' => (string) $post['description'],
            'body' => isset($post['body'])
                ? (string) $post['body']
                : '',
            'views' => (int) $post['views'],
            'published_at' => (string) $post['published_at'],
        ];
    }
}
