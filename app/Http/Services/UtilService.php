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
}
