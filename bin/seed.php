<?php

declare(strict_types=1);

use Smarty\Smarty;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/** @var array{pdo: PDO, smarty: Smarty} $application */
$application = require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = $application['pdo'];
$options = getopt('', ['fresh']);
$fresh = array_key_exists('fresh', $options);

/*
 * Данные намеренно задал массивами.
 * Для небольшого проекта, думаю, так и проще и нагляднее,
 * чем отдельный Faker.
 */
$categories = [
    [
        'name' => 'PHP',
        'slug' => 'php',
        'description' => 'Практические материалы о PHP, архитектуре приложений и серверной разработке.',
    ],
    [
        'name' => 'JavaScript',
        'slug' => 'javascript',
        'description' => 'Клиентская разработка, работа с браузером, jQuery и современным JavaScript.',
    ],
    [
        'name' => 'Базы данных',
        'slug' => 'databases',
        'description' => 'Проектирование схем, SQL-запросы, индексы и практическая работа с MySQL.',
    ],
    [
        'name' => 'DevOps',
        'slug' => 'devops',
        'description' => 'Docker, веб-серверы, развёртывание приложений и организация окружения.',
    ],
    [
        'name' => 'Производительность',
        'slug' => 'web-performance',
        'description' => 'Оптимизация приложений, запросов, изображений и скорости загрузки страниц.',
    ],
];

$posts = [
    [
        'image_path' => '/assets/images/posts/php.svg',
        'title' => 'Простая архитектура приложения на чистом PHP',
        'slug' => 'simple-php-architecture',
        'description' => 'Как разделить маршрутизацию, работу с данными и представление без лишних абстракций.',
        'body' => "Даже небольшому PHP-приложению полезно иметь понятные границы между обработкой запроса, SQL-запросами и HTML.\n\nПри этом структура должна соответствовать масштабу задачи. Для трёх публичных страниц не обязательно создавать полноценный фреймворк, контейнер зависимостей и множество промежуточных слоёв.",
        'views' => 184,
        'published_at' => '2026-08-05 09:00:00',
        'categories' => ['php'],
    ],
    [
        'image_path' => '/assets/images/posts/php.svg',
        'title' => 'PDO и подготовленные запросы в PHP',
        'slug' => 'pdo-prepared-statements',
        'description' => 'Безопасное подключение к MySQL и выполнение запросов с именованными параметрами.',
        'body' => "PDO предоставляет единый и достаточно компактный интерфейс для работы с базами данных в PHP.\n\nПодготовленные запросы отделяют SQL-код от пользовательских значений и помогают избежать SQL-инъекций. Режим исключений упрощает централизованную обработку ошибок.",
        'views' => 326,
        'published_at' => '2026-08-02 11:30:00',
        'categories' => ['php', 'databases'],
    ],
    [
        'image_path' => '/assets/images/posts/databases.svg',
        'title' => 'Связь многие ко многим в MySQL',
        'slug' => 'many-to-many-mysql',
        'description' => 'Разбираем промежуточную таблицу на примере статей, принадлежащих нескольким категориям.',
        'body' => "Связь многие ко многим реализуется через отдельную таблицу, которая хранит внешние ключи двух связанных сущностей.\n\nСоставной первичный ключ предотвращает повторное создание одной и той же связи, а внешние ключи обеспечивают целостность данных.",
        'views' => 271,
        'published_at' => '2026-07-30 15:00:00',
        'categories' => ['databases', 'php'],
    ],
    [
        'image_path' => '/assets/images/posts/databases.svg',
        'title' => 'Как выбирать индексы для таблиц MySQL',
        'slug' => 'mysql-indexes',
        'description' => 'Практический подход к индексам для сортировки, фильтрации и соединения таблиц.',
        'body' => "Индекс полезен тогда, когда соответствует реальным запросам приложения. Добавлять индекс на каждое поле заранее обычно не требуется.\n\nДля блога особенно важны индексы по slug, дате публикации, числу просмотров и внешним ключам промежуточной таблицы.",
        'views' => 412,
        'published_at' => '2026-07-27 10:15:00',
        'categories' => ['databases', 'web-performance'],
    ],
    [
        'image_path' => '/assets/images/posts/javascript.svg',
        'title' => 'Интерактивный интерфейс без большого фреймворка',
        'slug' => 'javascript-without-framework',
        'description' => 'Когда возможностей браузерного JavaScript достаточно для небольшого сайта.',
        'body' => "Не всякая интерактивная страница нуждается в React или другом крупном клиентском фреймворке.\n\nПереключатели, меню и небольшие формы часто проще реализовать стандартными средствами браузера, сохранив минимальный объём клиентского кода.",
        'views' => 153,
        'published_at' => '2026-07-24 13:20:00',
        'categories' => ['javascript'],
    ],
    [
        'image_path' => '/assets/images/posts/javascript.svg',
        'title' => 'Поддержка существующего проекта на jQuery',
        'slug' => 'maintaining-jquery-project',
        'description' => 'Как аккуратно развивать старый интерфейс и не переписывать работающую систему без необходимости.',
        'body' => "Во многих действующих проектах jQuery остаётся частью стабильного и проверенного интерфейса.\n\nПеред заменой технологии важно оценить стоимость переписывания, риски регрессий и реальную выгоду. Часто последовательный рефакторинг безопаснее полной замены.",
        'views' => 298,
        'published_at' => '2026-07-21 08:45:00',
        'categories' => ['javascript'],
    ],
    [
        'image_path' => '/assets/images/posts/performance.svg',
        'title' => 'Что замедляет страницу в браузере',
        'slug' => 'browser-page-performance',
        'description' => 'Изображения, блокирующие ресурсы и лишний JavaScript как основные источники задержек.',
        'body' => "Скорость страницы зависит не только от времени ответа сервера. Большие изображения и блокирующие ресурсы могут заметно задержать первый полезный вывод.\n\nОптимизация начинается с измерений: сначала нужно определить узкое место, а затем выбрать изменение, которое действительно влияет на результат.",
        'views' => 521,
        'published_at' => '2026-07-18 17:10:00',
        'categories' => ['javascript', 'web-performance'],
    ],
    [
        'image_path' => '/assets/images/posts/devops.svg',
        'title' => 'Локальная разработка PHP-проекта в Docker',
        'slug' => 'php-development-in-docker',
        'description' => 'Собираем воспроизводимое окружение с PHP, Apache, Composer и MySQL.',
        'body' => "Docker позволяет зафиксировать версии PHP, расширений и базы данных непосредственно в проекте.\n\nПроверяющему не придётся вручную настраивать веб-сервер: достаточно собрать контейнеры и выполнить несколько документированных команд.",
        'views' => 637,
        'published_at' => '2026-07-15 12:00:00',
        'categories' => ['devops', 'php'],
    ],
    [
        'image_path' => '/assets/images/posts/devops.svg',
        'title' => 'Docker Compose для PHP и MySQL',
        'slug' => 'docker-compose-php-mysql',
        'description' => 'Как связать приложение и базу данных внутренней сетью Docker Compose.',
        'body' => "Внутри Docker Compose сервисы обращаются друг к другу по именам, поэтому PHP может использовать имя db вместо фиксированного IP-адреса.\n\nНаружу следует публиковать только те порты, которые действительно нужны разработчику или пользователю.",
        'views' => 458,
        'published_at' => '2026-07-12 09:30:00',
        'categories' => ['devops', 'databases'],
    ],
    [
        'image_path' => '/assets/images/posts/devops.svg',
        'title' => 'Красивые URL с Apache mod_rewrite',
        'slug' => 'apache-mod-rewrite',
        'description' => 'Перенаправляем запросы в единую точку входа и сохраняем доступ к статическим файлам.',
        'body' => "Единая точка входа упрощает маршрутизацию небольшого PHP-приложения и позволяет использовать адреса без расширения .php.\n\nПравила перенаправления должны пропускать существующие файлы и каталоги, чтобы CSS, изображения и другие ресурсы обслуживались Apache напрямую.",
        'views' => 207,
        'published_at' => '2026-07-09 14:40:00',
        'categories' => ['devops', 'php'],
    ],
    [
        'image_path' => '/assets/images/posts/performance.svg',
        'title' => 'Кэширование без преждевременной оптимизации',
        'slug' => 'practical-caching',
        'description' => 'Какие данные стоит кэшировать и почему сначала нужно измерить производительность.',
        'body' => "Кэширование ускоряет повторное получение данных, но одновременно усложняет обновление и инвалидирование результата.\n\nСначала следует определить медленную операцию, а затем выбрать подходящий уровень кэша: браузер, шаблон, приложение или база данных.",
        'views' => 376,
        'published_at' => '2026-07-06 16:25:00',
        'categories' => ['web-performance', 'php'],
    ],
    [
        'image_path' => '/assets/images/posts/performance.svg',
        'title' => 'Когда приложению действительно нужен Redis',
        'slug' => 'when-to-use-redis',
        'description' => 'Сценарии использования Redis для кэша, сессий и временных данных.',
        'body' => "Redis полезен для быстро изменяющихся данных, очередей, счётчиков и общего кэша между несколькими экземплярами приложения.\n\nДля небольшого сайта добавлять его только ради наличия в технологическом стеке не требуется. Инфраструктура должна решать конкретную проблему.",
        'views' => 344,
        'published_at' => '2026-07-03 11:10:00',
        'categories' => ['web-performance', 'devops'],
    ],
    [
        'image_path' => '/assets/images/posts/databases.svg',
        'title' => 'Оптимизация SQL-запросов начинается с измерений',
        'slug' => 'sql-query-optimization',
        'description' => 'Используем план выполнения и фактические данные вместо случайного добавления индексов.',
        'body' => "Медленный запрос нужно сначала воспроизвести и изучить. План выполнения показывает порядок соединений, выбранные индексы и объём просмотренных строк.\n\nПосле изменения запрос следует измерить повторно: визуально более короткий SQL не всегда работает быстрее.",
        'views' => 489,
        'published_at' => '2026-06-30 10:00:00',
        'categories' => ['databases', 'web-performance'],
    ],
    [
        'image_path' => '/assets/images/posts/php.svg',
        'title' => 'Безопасный вывод данных в HTML',
        'slug' => 'safe-html-output',
        'description' => 'Почему пользовательские значения нужно экранировать непосредственно при выводе.',
        'body' => "Даже данные из собственной базы могут когда-нибудь содержать HTML-символы или введённый пользователем текст.\n\nАвтоматическое экранирование в шаблонизаторе снижает риск случайной XSS-уязвимости. Отключать его следует только для явно доверенного содержимого.",
        'views' => 263,
        'published_at' => '2026-06-27 09:20:00',
        'categories' => ['php', 'javascript'],
    ],
    [
        'image_path' => '/assets/images/posts/devops.svg',
        'title' => 'Проверка проекта перед развёртыванием',
        'slug' => 'deployment-checklist',
        'description' => 'Короткий список проверок перед публикацией новой версии приложения.',
        'body' => "Перед развёртыванием полезно проверить конфигурацию, зависимости, схему базы данных и отсутствие секретов в репозитории.\n\nОтдельный запуск проекта в чистом окружении помогает обнаружить скрытые зависимости от локальных файлов и настроек разработчика.",
        'views' => 194,
        'published_at' => '2026-06-24 18:00:00',
        'categories' => ['devops'],
    ],
    [
        'image_path' => '/assets/images/posts/performance.svg',
        'title' => 'Оптимизация изображений для веб-сайта',
        'slug' => 'web-image-optimization',
        'description' => 'Выбор формата, размеров и отложенной загрузки изображений.',
        'body' => "Изображение не должно загружаться в разрешении, которое значительно превышает размер его отображения на странице.\n\nСовременные форматы, корректные размеры и lazy loading уменьшают объём передаваемых данных и ускоряют первоначальную загрузку.",
        'views' => 431,
        'published_at' => '2026-06-21 12:30:00',
        'categories' => ['web-performance'],
    ],
    [
        'image_path' => '/assets/images/posts/javascript.svg',
        'title' => 'React-компонент внутри существующего сайта',
        'slug' => 'react-component-in-legacy-project',
        'description' => 'Как внедрить отдельный React-компонент без полного переписывания проекта.',
        'body' => "Новый интерфейсный блок можно реализовать как изолированный компонент и подключить к существующей странице.\n\nТакой подход позволяет постепенно модернизировать проект и одновременно ограничивает область возможных регрессий.",
        'views' => 238,
        'published_at' => '2026-06-18 14:15:00',
        'categories' => ['javascript'],
    ],
    [
        'image_path' => '/assets/images/posts/javascript.svg',
        'title' => 'Асинхронная интеграция стороннего API',
        'slug' => 'async-api-integration',
        'description' => 'Разделяем серверный запрос, обработку ошибок и обновление интерфейса.',
        'body' => "Интеграция API должна учитывать таймауты, ошибочные ответы и временную недоступность внешнего сервиса.\n\nСерверная часть отвечает за безопасное взаимодействие и нормализацию данных, а браузер показывает пользователю состояние загрузки и понятное сообщение об ошибке.",
        'views' => 352,
        'published_at' => '2026-06-15 10:50:00',
        'categories' => ['javascript', 'php'],
    ],
];

try {
    $pdo->beginTransaction();

    if ($fresh) {
        /*
         * Удаляем записи в порядке, обратном внешним ключам.
         * DELETE остаётся в транзакции, чтобы при ошибке все изменения можно откатить полностью .
         */
        $pdo->exec('DELETE FROM category_post');
        $pdo->exec('DELETE FROM posts');
        $pdo->exec('DELETE FROM categories');
    } else {
        $existingRows = (int) $pdo
            ->query(
                'SELECT
                    (SELECT COUNT(*) FROM categories)
                    + (SELECT COUNT(*) FROM posts)'
            )
            ->fetchColumn();

        if ($existingRows > 0) {
            throw new RuntimeException(
                'База уже содержит данные. Для повторного заполнения используйте параметр --fresh.'
            );
        }
    }

    $categoryStatement = $pdo->prepare(
        'INSERT INTO categories (name, slug, description)
         VALUES (:name, :slug, :description)'
    );

    $categoryIds = [];

    foreach ($categories as $category) {
        $categoryStatement->execute($category);
        $categoryIds[$category['slug']] = (int) $pdo->lastInsertId();
    }

    $postStatement = $pdo->prepare(
        'INSERT INTO posts (
            image_path,
            title,
            slug,
            description,
            body,
            views,
            published_at
        ) VALUES (
            :image_path,
            :title,
            :slug,
            :description,
            :body,
            :views,
            :published_at
        )'
    );

    $relationStatement = $pdo->prepare(
        'INSERT INTO category_post (category_id, post_id)
         VALUES (:category_id, :post_id)'
    );

    foreach ($posts as $post) {
        $postCategories = $post['categories'];
        unset($post['categories']);

        $postStatement->execute($post);
        $postId = (int) $pdo->lastInsertId();

        foreach ($postCategories as $categorySlug) {
            if (!isset($categoryIds[$categorySlug])) {
                throw new RuntimeException(
                    sprintf(
                        'Для статьи "%s" указана неизвестная категория "%s".',
                        $post['title'],
                        $categorySlug
                    )
                );
            }

            $relationStatement->execute([
                'category_id' => $categoryIds[$categorySlug],
                'post_id' => $postId,
            ]);
        }
    }

    $pdo->commit();

    printf(
        "Сидирование завершено: категорий — %d, статей — %d.\n",
        count($categories),
        count($posts)
    );
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(
        STDERR,
        sprintf("Ошибка сидирования: %s\n", $exception->getMessage())
    );

    exit(1);
}
