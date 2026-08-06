{extends file='layout.tpl'}

{block name='content'}
    <section class="category-page-header">
        <a class="back-link" href="/">
            ← На главную
        </a>

        <p class="eyebrow">Категория</p>

        <h1>{$category.name}</h1>

        <p class="category-page-header__description">
            {$category.description}
        </p>
    </section>

    <div class="category-toolbar">
        <p class="category-toolbar__count">
            Найдено:
            <strong>
                {$totalPosts}
                {$totalPosts|plural_ru:'статья':'статьи':'статей'}
            </strong>
        </p>

        <form
            class="sort-form"
            action="/category/{$category.slug}"
            method="get"
        >
            <label for="sort">Сортировка</label>

            <select id="sort" name="sort">
                {foreach $sortOptions as $option}
                    <option
                        value="{$option.value}"
                        {if $option.value == $currentSort}selected{/if}
                    >
                        {$option.label}
                    </option>
                {/foreach}
            </select>

            <button class="button" type="submit">
                Применить
            </button>
        </form>
    </div>

    {if $posts}
        <div class="post-grid">
            {foreach $posts as $post}
                {include
                    file='partials/post-card.tpl'
                    post=$post
                }
            {/foreach}
        </div>

        {include
            file='partials/pagination.tpl'
            pagination=$pagination
        }
    {else}
        <div class="empty-state">
            <h2>В этой категории пока нет статей</h2>
            <p>Новые материалы появятся здесь после публикации.</p>
        </div>
    {/if}
{/block}
