<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SlugService
{


    public function generate(Model $model, string $field = 'name'): string
    {
        $base = $this->slugify($model->{$field});
        $slug = $base;
        $i = 1;

        $query = $model->newQuery()
            ->where('slug', $slug);

        if (Schema::hasColumn($model->getTable(), 'business_id')) {
            $query->where('business_id', business()->id);
        }

        while (
        $query->when($model->exists, fn ($q) => $q->where('id', '!=', $model->id))
            ->exists()
        ) {

            $slug = "{$base}-{$i}";
            $i++;

            $query = $model->newQuery()
                ->where('slug', $slug);

            if (Schema::hasColumn($model->getTable(), 'business_id')) {
                $query->where('business_id', business()->id);
            }
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
