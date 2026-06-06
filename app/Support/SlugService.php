<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class SlugService
{
    public function generate(Model $model, string $field = 'name'): string
    {
        $base = $this->slugify($model->{$field});
        $slug = $base;
        $i = 1;

        while (
        $model->newQuery()
            ->where('business_id', business()->id)
            ->where('slug', $slug)
            ->when($model->exists, fn ($q) => $q->where('id', '!=', $model->id))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function slugify(string $text): string
    {
        $text = trim($text);

        // حذف کاراکترهای غیر مجاز (فقط فارسی، انگلیسی، عدد، فاصله)
        $text = preg_replace('/[^\\p{Arabic}\\p{L}\\p{N}\\s-]+/u', '', $text);

        // تبدیل فاصله به dash
        $text = preg_replace('/[\\s]+/u', '-', $text);

        // حذف dash های اضافی
        $text = preg_replace('/-+/', '-', $text);

        return mb_strtolower($text);
    }
}
