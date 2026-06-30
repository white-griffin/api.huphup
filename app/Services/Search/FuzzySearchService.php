<?php

namespace App\Services\Search;

use App\Support\SearchNormalizer;
use TeamTNT\TNTSearch\TNTSearch;

class FuzzySearchService
{
    protected TNTSearch $tnt;

    public function __construct()
    {
        $this->tnt = new TNTSearch();
        $this->tnt->loadConfig(config('tntsearch'));
    }

    /**
     * اجرای جستجوی فازی روی یک ایندکس خاص
     */
    public function search(string $indexName, string $keyword, int $limit = 100): array
    {
        $this->tnt->selectIndex($indexName);

        $keyword = SearchNormalizer::normalize($keyword);

        $results = $this->tnt->search($keyword, $limit);

        return $results['ids'] ?? [];
    }

    /**
     * دسترسی مستقیم به آبجکت اصلی برای کارهای پیشرفته
     */
    public function getEngine(): TNTSearch
    {
        return $this->tnt;
    }
}
