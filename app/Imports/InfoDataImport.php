<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InfoDataImport implements ToCollection,WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FirstSheetImport()
        ];
    }
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
}
