<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DivisiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('divisi')->delete();
        
        \DB::table('divisi')->insert(array (
            0 => 
            array (
                'divisi_id' => 1,
                'divisi_code' => 'PI',
                'divisi' => 'PT PAL INDONESIA (PERSERO)',
                'created_at' => now(),
                'updated_at' => now()
            ),
            1 => [ 
                'divisi_id' => 2,
                'divisi_code' => 'LN',
                'divisi' => 'PT. LEN',
                'created_at' => now(),
                'updated_at' => now()
            ],
            2 => [ 
                'divisi_id' => 3,
                'divisi_code' => 'DI',
                'divisi' => 'PT. DIRGANTARA INDONESIA',
                'created_at' => now(),
                'updated_at' => now()
            ],
            3 => [ 
                'divisi_id' => 4,
                'divisi_code' => 'DH',
                'divisi' => 'PT. DAHANA',
                'created_at' => now(),
                'updated_at' => now()
            ],
            4 => [ 
                'divisi_id' => 5,
                'divisi_code' => 'PD',
                'divisi' => 'PT. PINDAD',
                'created_at' => now(),
                'updated_at' => now()
            ],
            5 => [ 
                'divisi_id' => 6,
                'divisi_code' => 'PI',
                'divisi' => 'PT PAL INDONESIA (PERSERO)',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ));
    }
}
