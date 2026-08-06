{extends file='layout.tpl'}

{block name='content'}
    <section class="environment-check">
        <p class="environment-check__label">
            Окружение пашет
        </p>

        <h1>Блог на чистом PHP</h1>

        <p class="environment-check__intro">
            Smarty работает.
            Подключение к MySQL тоже работает.
        </p>

        <dl class="environment-list">
            <div class="environment-list__item">
                <dt>PHP</dt>
                <dd>{$phpVersion}</dd>
            </div>

            <div class="environment-list__item">
                <dt>MySQL</dt>
                <dd>{$mysqlVersion}</dd>
            </div>

            <div class="environment-list__item">
                <dt>Smarty</dt>
                <dd>{$smartyVersion}</dd>
            </div>
        </dl>
    </section>
{/block}
