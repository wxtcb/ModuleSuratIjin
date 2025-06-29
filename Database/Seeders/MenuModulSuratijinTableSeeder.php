<?php

namespace Modules\SuratIjin\Database\Seeders;

use App\Models\Core\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MenuModulSuratijinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Menu::where('modul', 'SuratIjin')->delete();
        $menu = Menu::create([
            'modul' => 'SuratIjin',
            'label' => 'Data Ijin',
            'url' => 'suratijin',
            'can' => serialize(['admin', 'kajur', 'dosen', 'pegawai', 'direktur', 'kaunit']),
            'icon' => 'fas fa-calendar-plus',
            'urut' => 1,
            'parent_id' => 0,
            'active' => serialize(['suratijin']),
        ]);
        if ($menu) {
            Menu::create([
                'modul' => 'SuratIjin',
                'label' => 'Terlambat & Pulang Awal',
                'url' => 'suratijin/terlambat',
                'can' => serialize(['admin', 'kajur', 'dosen', 'pegawai', 'direktur', 'kaunit']),
                'icon' => 'far fa-circle',
                'urut' => 2,
                'parent_id' => $menu->id,
                'active' => serialize(['suratijin/terlambat', 'suratijin/terlambat*']),
            ]);
        }
        if ($menu) {
            Menu::create([
                'modul' => 'SuratIjin',
                'label' => 'Lupa Absen',
                'url' => 'suratijin/lupaabsen',
                'can' => serialize(['admin', 'kajur', 'dosen', 'pegawai', 'direktur', 'kaunit']),
                'icon' => 'far fa-circle',
                'urut' => 3,
                'parent_id' => $menu->id,
                'active' => serialize(['suratijin/lupaabsen', 'suratijin/lupaabsen*']),
            ]);
        }
    }
}
