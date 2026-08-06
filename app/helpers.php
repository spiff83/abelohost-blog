<?php

declare(strict_types=1);

/**
 * Выбирает правильную форму русского существительного после числа.
 *
 * Примеры:
 * 1 просмотр
 * 2 просмотра
 * 5 просмотров
 * 11 просмотров
 * 21 просмотр
 *
 * @param string $one  Форма для чисел, оканчивающихся на 1.
 * @param string $few  Форма для чисел, оканчивающихся на 2–4.
 * @param string $many Форма для остальных чисел.
 */
function pluralizeRussian(
    int $number,
    string $one,
    string $few,
    string $many
): string {
    $absoluteNumber = abs($number);
    $lastTwoDigits = $absoluteNumber % 100;

    /*
     * Числа от 11 до 19 всегда используют множественную форму,
     * независимо от последней цифры: 11 просмотров, 14 просмотров.
     */
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
        return $many;
    }

    return match ($absoluteNumber % 10) {
        1 => $one,
        2, 3, 4 => $few,
        default => $many,
    };
}
