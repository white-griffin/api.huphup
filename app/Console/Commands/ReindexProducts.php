<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class ReindexProducts extends Command
{
    protected $signature = 'search:reindex-products';
    protected $description = 'Rebuild the search index for Products';

    public function handle()
    {
        $this->info('Starting indexing products...');

        $tnt = new TNTSearch();
        $tnt->loadConfig(config('tntsearch'));

        $indexer = $tnt->createIndex('products.index');

        // کوئری مستقیم برای سرعت بالاتر در ایندکس‌های حجیم
        $indexer->query('SELECT id, name, sku, description FROM products;');
        $indexer->run();

        $this->info('Products indexed successfully!');
    }
}
