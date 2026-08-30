<?php

namespace Database\Seeders;

use App\Models\ProductLabel;
use Illuminate\Database\Seeder;

class ProductLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $labels = [
            'CARPINTERIA',
            'SOLDADURA',
            'CAUCHO',
            'PINTURA',
        ];

        foreach ($labels as $nombre) {
            ProductLabel::firstOrCreate([
                'name' => $nombre,
            ]);
        }
    }
}
