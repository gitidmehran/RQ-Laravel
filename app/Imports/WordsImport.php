<?php

namespace App\Imports;

use App\Models\Words;
use App\Models\InfoData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class WordsImport implements ToModel,WithStartRow,WithCalculatedFormulas
{
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if($row[2]==null || $row[3]==null || $row[4]==null || $row[5]==null || $row[8]==null || $row[9]==null){
            return null;
        }
        $ayat = InfoData::where(['surahNo'=>$row[1],'ayatNo'=>$row[2]])->first()->toArray();
        return new Words([
            'ayat_no' => $ayat['id'],
            'reference' => $row[3],
            'word' => $row[4],
            'root_word' => $row[5],
            'grammatical_description' => $row[6],
            'prefix' => $row[7],
            'actual_word' => $row[8],
            'filtered_word' => $row[9],
            'postfix' => $row[10]
        ]);
    }
}
