<?php

namespace App\Support;

class SearchNormalizer
{
    public static function normalize(string $text): string
    {
        $text = trim($text);

        // تبدیل عربی به فارسی
        $search  = ['ي', 'ك'];
        $replace = ['ی', 'ک'];
        $text = str_replace($search, $replace, $text);

        // حذف نیم فاصله
        $text = str_replace(['‌', '‍'], ' ', $text);

        // حذف فاصله‌های اضافی
        $text = preg_replace('/\s+/', ' ', $text);

        return mb_strtolower($text);
    }
}
