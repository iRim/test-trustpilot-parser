<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Services\Resource;
use Illuminate\Console\Command;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start parser';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Link::query()
            ->get()
            ->map(function ($row) {
                $this->info('##################################################');
                $this->info('Старт ' . $row->url);
                Resource::parse($row)
                    ->onProgress(fn($msg) => $this->info($msg))
                    ->run();
                $this->info('Закінчено! Пауза 5 сек.');
                sleep(5);
            });
    }
}
