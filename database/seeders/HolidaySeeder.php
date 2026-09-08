<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi', 'type' => 'national'],
            ['date' => '2026-01-16', 'name' => 'Isra Mi’raj Nabi Muhammad SAW', 'type' => 'national'],

            ['date' => '2026-02-16', 'name' => 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili', 'type' => 'joint_leave'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 'national'],

            ['date' => '2026-03-18', 'name' => 'Cuti Bersama Hari Suci Nyepi Tahun Baru Saka 1948', 'type' => 'joint_leave'],
            ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'type' => 'national'],

            ['date' => '2026-03-20', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'national'],
            ['date' => '2026-03-22', 'name' => 'Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'national'],
            ['date' => '2026-03-23', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-03-24', 'name' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriyah', 'type' => 'joint_leave'],

            ['date' => '2026-04-03', 'name' => 'Wafat Yesus Kristus / Jumat Agung', 'type' => 'national'],
            ['date' => '2026-04-05', 'name' => 'Kebangkitan Yesus Kristus (Paskah)', 'type' => 'national'],

            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Yesus Kristus', 'type' => 'national'],
            ['date' => '2026-05-15', 'name' => 'Cuti Bersama Kenaikan Yesus Kristus', 'type' => 'joint_leave'],
            ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 Hijriyah', 'type' => 'national'],
            ['date' => '2026-05-28', 'name' => 'Cuti Bersama Hari Raya Idul Adha 1447 Hijriyah', 'type' => 'joint_leave'],
            ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE', 'type' => 'national'],

            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national'],
            ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 Hijriyah', 'type' => 'national'],

            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia', 'type' => 'national'],
            ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],

            ['date' => '2026-12-24', 'name' => 'Cuti Bersama Hari Raya Natal', 'type' => 'joint_leave'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'type' => $holiday['type'],
                    'is_joint_leave' => $holiday['type'] === 'joint_leave',
                ]
            );
        }
    }
}