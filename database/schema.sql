-- Схема БД блога.
-- utf8mb4 чтобы не был опроблем с кириллицей, спец.символами и вообще Unicode.

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS posts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    description TEXT NOT NULL,
    body LONGTEXT NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,

    -- Дата публикации. В задании не описано, но без этого неудобно отсчитывать последние статьи и делать сортировку на странице категории.
	-- Можно, конечно и по id, но так ИМХО, лучше и привычнее
    published_at DATETIME NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_posts_slug (slug),

    -- Это на всякий, если вдруг несколько
    -- статей получат одинаковую дату или число просмотров.
    KEY idx_posts_published_at (published_at, id),
    KEY idx_posts_views (views, id)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS category_post (
    category_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,

    -- Это чтобы одна статья случайно не получила повторно ту же категорию
    PRIMARY KEY (category_id, post_id),

    KEY idx_category_post_post (post_id, category_id),

    CONSTRAINT fk_category_post_category
        FOREIGN KEY (category_id)
        REFERENCES categories (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_category_post_post
        FOREIGN KEY (post_id)
        REFERENCES posts (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
