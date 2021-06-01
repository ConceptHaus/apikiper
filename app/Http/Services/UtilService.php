<?php
namespace App\Http\Services;
use DB;

class UtilService
{
    public static function getColumnStatuses($table, $column){
        $type = DB::select(DB::raw('SHOW COLUMNS FROM '.$table.' WHERE Field = "'.$column.'"'))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = array();
        foreach(explode(',', $matches[1]) as $value){
            $values[] = trim($value, "'");
        }
        return $values;
    }

    public static function getStartDateForNotifications($hours)
    {
        $now        = date('Y-m-d H:i:s');
        $start_date = strtotime('-'.$hours.' hours', strtotime($now));
        $start_date = date('Y-m-d H:i:s', $start_date);

        return $start_date;
    }

    public static function verifyNewStatusInStatuses($new_status, $statuses)
    {
        return in_array($new_status, $statuses);
    }

    public static function getValueInHours($value)
    {
        $values = explode("|", $value);
        $hours  = $values[0];
        if ($values[1] == "days") {
            $hours = $values[0] * 24;
        }
        return $hours;
    }
}
