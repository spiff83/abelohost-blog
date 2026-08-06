<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$pageTitle|default:'AbeloHost Blog'}</title>

	<meta
		name="description"
		content="{$pageDescription|default:'Практические статьи о PHP, MySQL, JavaScript и разработке веб-приложений.'}"
	>

    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a class="site-logo" href="/">AbeloHost Blog</a>

            <span class="site-header__description">
                PHP · MySQL · Smarty
            </span>
        </div>
    </header>

    <main class="container page-content">
        {block name='content'}{/block}
    </main>

    <footer class="site-footer">
        <div class="container">
            Тестовое задание · {$currentYear}
        </div>
    </footer>
</body>
</html>
