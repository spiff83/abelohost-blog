<article class="post-card">
    <a
        class="post-card__image-link"
        href="/post/{$post.slug}"
        aria-label="Открыть статью «{$post.title}»"
    >
        <img
            class="post-card__image"
            src="{$post.image_path}"
            alt="Обложка статьи «{$post.title}»"
            width="1200"
            height="675"
            loading="lazy"
        >
    </a>

    <div class="post-card__content">
        <div class="post-card__meta">
            <time
                datetime="{$post.published_at|date_format:'%Y-%m-%d'}"
            >
                {$post.published_at|date_format:'%d.%m.%Y'}
            </time>

            <span>
				{$post.views} 
				{$post.views|plural_ru:'просмотр':'просмотра':'просмотров'}
			</span>
        </div>

        <h3 class="post-card__title">
            <a href="/post/{$post.slug}">
                {$post.title}
            </a>
        </h3>

        <p class="post-card__description">
            {$post.description}
        </p>

        <a class="post-card__more" href="/post/{$post.slug}">
            Читать статью
        </a>
    </div>
</article>
