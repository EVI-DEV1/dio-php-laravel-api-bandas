<?php

namespace Database\Seeders;

use App\Models\Band;
use Illuminate\Database\Seeder;

class BandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['id' => 1, 'name' => 'Dream Theater', 'gender' => 'progressivo'],
            ['id' => 2, 'name' => 'Megadeth', 'gender' => 'thrash metal'],
            ['id' => 3, 'name' => 'Dio', 'gender' => 'heavy metal'],
            ['id' => 4, 'name' => 'Metallica', 'gender' => 'heavy metal'],
            ['id' => 5, 'name' => 'Barões da Pisadinha', 'gender' => 'tecno forró'],
        ] as $band) {
            Band::query()->updateOrCreate(['id' => $band['id']], [
                'name' => $band['name'],
                'gender' => $band['gender'],
            ]);
        }
    }
}
