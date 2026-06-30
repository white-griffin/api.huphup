<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use TeamTNT\TNTSearch\TNTSearch;

trait SearchableByTNT
{
    public static function bootSearchableByTNT(): void
    {
        static::saved(function (Model $model): void {
            if (method_exists($model, 'updateSearchIndex')) {
                $model->updateSearchIndex();
            }
        });

        static::deleted(function (Model $model): void {
            if (method_exists($model, 'deleteFromSearchIndex')) {
                $model->deleteFromSearchIndex();
            }
        });
    }

    public function updateSearchIndex(): void
    {
        $tnt = new TNTSearch();
        $tnt->loadConfig(config('tntsearch'));

        $indexName = $this->searchableIndexName();
        $tnt->selectIndex($indexName);

        $index = $tnt->getIndex();
        $index->update($this->getKey(), $this->toSearchableArray());
    }

    public function deleteFromSearchIndex(): void
    {
        $tnt = new TNTSearch();
        $tnt->loadConfig(config('tntsearch'));

        $indexName = $this->searchableIndexName();
        $tnt->selectIndex($indexName);

        $index = $tnt->getIndex();
        $index->delete($this->getKey());
    }


    public function searchableIndexName(): string
    {
        return $this->getTable() . '.index';
    }

    public function toSearchableArray(): array
    {
        return $this->toArray();
    }
}
