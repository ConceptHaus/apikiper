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
        //Funnel Module
        $new_setting            = new Setting();
        $new_setting->setting   = "oportunidades_status_max_count";
        $new_setting->value     = 8;
        $new_setting->save();

        //Notifications Module
        $new_setting            = new Setting();
        $new_setting->setting   = "prospectos_max_time_inactivity";
        $new_setting->value     = "24|hours";
        $new_setting->save();

        $new_setting            = new Setting();
        $new_setting->setting   = "prospectos_max_notification_attempt";
        $new_setting->value     = 3;
        $new_setting->save();

        $new_setting            = new Setting();
        $new_setting->setting   = "prospectos_receive_inactivity_notifications";
        $new_setting->value     = "all";
        $new_setting->save();

        $new_setting            = new Setting();
        $new_setting->setting   = "oportunidades_max_time_inactivity";
        $new_setting->value     = "2|days";
        $new_setting->save();

        $new_setting            = new Setting();
        $new_setting->setting   = "oportunidades_max_notification_attempt";
        $new_setting->value     = 3;
        $new_setting->save();

        $new_setting            = new Setting();
        $new_setting->setting   = "oportunidades_receive_inactivity_notifications";
        $new_setting->value     = "escalated";
        $new_setting->save();
    }
}
