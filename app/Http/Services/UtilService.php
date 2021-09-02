<?php
namespace App\Http\Services;
use DB;

class UtilService
{
    public static function getColumnStatuses($table, $column)
    {
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

    public static function getHoursDifferenceForTimeStamps($start, $end)
    {
        $start_date = strtotime($start);
        $end_date   = strtotime($end);
        $datediff   = $end_date - $start_date;

        return floor($datediff / (60 * 60));
    }

    public static function arrayGroupByKey($array, $key)
    {
        $return = array();
        foreach($array as $val) {
            $return[$val[$key]][] = $val;
        }
        return $return;
    }

    public static function getDaysDifferenceForTimeStamps($start, $end)
    {
        $start_date = strtotime($start);
        $end_date   = strtotime($end);
        $datediff   = $end_date - $start_date;

        return floor($datediff / (60 * 60 * 24));
    }

    public static function getDatesRangeForFilter($start_date, $end_date)
    {
        $days = UtilService::getDaysDifferenceForTimeStamps($start_date, $end_date);

        if ($days > 0) {
            if ($days < 31) {
                return 'days';
            }
            elseif ($days > 30 AND $days < 62) {
                return 'weeks';
            }
            elseif ($days > 61 AND $days < 366) {
                return 'months';
            }
            else{
                return 'years';    
            }
        }

        return 'days';
    }

    public static function getRangesFromRangeType($start_date, $end_date, $range_type)
    {
        $periods = array();

        switch ($range_type) {
            
            case 'days':
                $periods[] =  ['start_date' => $start_date, 'end_date' => $end_date];
                break;
           
            case 'weeks':

                $start_d = strtotime($start_date); 
                $end_d   = strtotime($end_date);
                
                $end_1   = strtotime ( '+1 week' , $start_d ) ;
                $start_1 = $start_d;

                while ($start_d < $end_d) {
                    $new_start_d = $start_d;
                    while ($start_d < $end_1) {
                        $start_d = strtotime ( '+1 day' , $start_d);
                    }

                    $new_end_1 = strtotime ( '-1 day' , $end_1);

                    if ($new_start_d == $new_end_1) {
                        $new_end_1 = $end_1;
                    }
                    
                    $periods[] =  ['start_date' =>  date('Y-m-d', $new_start_d), 'end_date' =>  date('Y-m-d', $new_end_1)];
                    
                    if(strtotime ( '+1 week' , $end_1 ) > $end_d){
                        $end_1 = $end_d;
                    }else{
                        $end_1 = strtotime ( '+1 week' , $end_1);
                    }
                }
                break;
            
            case 'months':

                $start_d = strtotime($start_date); 
                $end_d   = strtotime($end_date);
                
                $end_1   = strtotime ( '+1 month' , $start_d ) ;
                $start_1 = $start_d;

                while ($start_d < $end_d) {
                    $new_start_d = $start_d;
                    while ($start_d < $end_1) {
                        $start_d = strtotime ( '+1 day' , $start_d);
                    }

                    $new_end_1 = strtotime ( '-1 day' , $end_1);

                    if ($new_start_d == $new_end_1) {
                        $new_end_1 = $end_1;
                    }
                    if ($new_end_1 == strtotime ( '-1 day' , $end_1)) {
                        $new_end_1 = $end_1;
                    }
                    
                    $periods[] =  ['start_date' =>  date('Y-m-d', $new_start_d), 'end_date' =>  date('Y-m-d', $new_end_1)];
                    
                    if(strtotime ( '+1 month' , $end_1 ) > $end_d){
                        $end_1 = $end_d;
                    }else{
                        $end_1 = strtotime ( '+1 month' , $end_1);
                    }
                }
                break;
            
            case 'years':

                $start_d = strtotime($start_date); 
                $end_d   = strtotime($end_date);
                
                $end_1   = strtotime ( '+1 year' , $start_d ) ;
                $start_1 = $start_d;

                while ($start_d < $end_d) {
                    $new_start_d = $start_d;
                    while ($start_d < $end_1) {
                        $start_d = strtotime ( '+1 day' , $start_d);
                    }

                    $new_end_1 = strtotime ( '-1 day' , $end_1);

                    if ($new_start_d == $new_end_1) {
                        $new_end_1 = $end_1;
                    }
                    if ($new_end_1 == strtotime ( '-1 day' , $end_1)) {
                        $new_end_1 = $end_1;
                    }
                    
                    $periods[] =  ['start_date' =>  date('Y-m-d', $new_start_d), 'end_date' =>  date('Y-m-d', $new_end_1)];
                    
                    if(strtotime ( '+1 year' , $end_1 ) > $end_d){
                        $end_1 = $end_d;
                    }else{
                        $end_1 = strtotime ( '+1 year' , $end_1);
                    }
                }

                break;
                
                
            default:
                $periods[] =  ['start_date' => $start_date, 'end_date' => $end_date];
                break;
        
        
        }

        return $periods;
    }

    public static function createCustomLog($file, $message){
        $myfile = fopen(storage_path() . "/logs/" . $file . ".log", "a+");
        $txt    = date('Y-M-d H:i:s') . " " . $message . "\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }

}
