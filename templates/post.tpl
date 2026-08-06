{extends file='layout.tpl'}

{block name='content'}
    <article class="article">
        <header class="article__header">
            <a class="back-link" href="/">
                ← На главную
            </a>

            {if $categories}
                <div class="article__categories">
                    {foreach $categories as $category}
                        <a
                            class="category-chip"
                            href="/category/{$category.slug}"
                        >
                            {$category.name}
                        </a>
                    {/foreach}
                </div>
            {/if}

            <h1>{$post.title}</h1>

            <p class="article__description">
                {$post.description}
            </p>

            <div class="article__meta">
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
        </header>

        <img
            class="article__image"
            src="{$post.image_path}"
            alt="Обложка статьи «{$post.title}»"
            width="1200"
            height="675"
        >

        <div class="article__body">
            {foreach $bodyParagraphs as $paragraph}
                <p>{$paragraph}</p>
            {/foreach}
        </div>
    </article>

    {if $relatedPosts}
        <section class="related-posts">
            <div class="related-posts__header">
                <p class="eyebrow">Продолжить чтение</p>
                <h2>Похожие статьи</h2>
            </div>

            <div class="post-grid">
                {foreach $relatedPosts as $relatedPost}
                    {include
                        file='partials/post-card.tpl'
                        post=$relatedPost
                    }
                {/foreach}
            </div>
        </section>
    {/if}
{/block}
