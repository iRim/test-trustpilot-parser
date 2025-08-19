<?php

namespace Database\Seeders;

use App\Models\Link;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $urls = [
            'https://www.trustpilot.com/review/wg.casino',
            'https://www.trustpilot.com/review/payments.astropay.com',
            'https://www.trustpilot.com/review/blockbets.casino',
            'https://www.trustpilot.com/review/bitspin365.com',
            'https://www.trustpilot.com/review/wazbee.casino'
        ];

        foreach ($urls as $url) {
            Link::query()
                ->create([
                    'url' => $url
                ]);
        }
    }
}
