{if $pagination.totalPages > 1}
    <nav class="pagination" aria-label="Страницы категории">
        {if $pagination.previousUrl}
            <a
                class="pagination__direction"
                href="{$pagination.previousUrl}"
                rel="prev"
            >
                ← Назад
            </a>
        {else}
            <span
                class="pagination__direction pagination__direction--disabled"
                aria-disabled="true"
            >
                ← Назад
            </span>
        {/if}

        <div class="pagination__pages">
            {foreach $pagination.pages as $page}
                {if $page.is_current}
                    <span
                        class="pagination__page pagination__page--current"
                        aria-current="page"
                    >
                        {$page.number}
                    </span>
                {else}
                    <a
                        class="pagination__page"
                        href="{$page.url}"
                    >
                        {$page.number}
                    </a>
                {/if}
            {/foreach}
        </div>

        {if $pagination.nextUrl}
            <a
                class="pagination__direction"
                href="{$pagination.nextUrl}"
                rel="next"
            >
                Вперёд →
            </a>
        {else}
            <span
                class="pagination__direction pagination__direction--disabled"
                aria-disabled="true"
            >
                Вперёд →
            </span>
        {/if}
    </nav>
{/if}
