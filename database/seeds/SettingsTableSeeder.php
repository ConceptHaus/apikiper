<?php

use Illuminate\Database\Seeder;
use App\Modelos\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $new_setting            = new Setting();
        $new_setting->setting   = "oportunidades_status_max_count";
        $new_setting->value     = 8;
        $new_setting->save();
    }
}
