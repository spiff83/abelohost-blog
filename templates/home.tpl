{extends file='layout.tpl'}

{block name='content'}
    <section class="page-intro">
        <p class="eyebrow">Практический блог</p>

        <h1>Разработка, базы данных и инфраструктура</h1>

        <p class="page-intro__description">
            Статьи о создании и поддержке веб-приложений:
            от чистого PHP и SQL до Docker и клиентской разработки.
        </p>
    </section>

    {if $categories}
        <div class="category-list">
            {foreach $categories as $category}
                <section class="category-section">
                    <div class="category-section__header">
                        <div class="category-section__heading">
                            <p class="eyebrow">Категория</p>

                            <h2>{$category.name}</h2>

                            <p>{$category.description}</p>
                        </div>

                        <a
                            class="button"
                            href="/category/{$category.slug}"
                        >
                            Все статьи
                        </a>
                    </div>

                    <div class="post-grid">
                        {foreach $category.posts as $post}
                            {include
                                file='partials/post-card.tpl'
                                post=$post
                            }
                        {/foreach}
                    </div>
                </section>
            {/foreach}
        </div>
    {else}
        <div class="empty-state">
            <h2>Статей пока нет</h2>
            <p>После добавления публикаций они появятся на этой странице.</p>
        </div>
    {/if}
{/block}
