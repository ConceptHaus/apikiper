<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class ColaboradoresReport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($headings){
        $this->headings = $headings;
    }
    
    public function collection()
    {
        return User::all();
    }

    public function headings():array{
        return $this->headings;
    }

}
