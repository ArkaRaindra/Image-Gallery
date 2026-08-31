<?php

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;

class RecalculateTagCounts extends Command
{
    protected $signature = 'tags:recalculate-counts';

    protected $description = 'Recalculate the post_count column on all tags based on actual pivot data';

    public function handle(): int
    {
        Tag::recalculateAllPostCounts();

        $this->info('Tag post counts have been recalculated.');

        return self::SUCCESS;
    }
}