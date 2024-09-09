<?php

namespace App\Imports;

use App\Models\RegularMeaning;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Words;
use App\Models\InfoData;

class RegularMeaningImporter implements ToModel,WithStartRow
{
    public function startRow(): int
    {
        return 3;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         if($row[2]==null || $row[3]==null || $row[4]==null || $row[5]==null || $row[7]==null || $row[8]==null){
            return null;
        }
        $ayat = InfoData::where(['surahNo'=>$row[5],'ayatNo'=>$row[7]])->first()->toArray();
        
        $word = Words::where(['ayat_no'=>$ayat['id'],'reference'=>$row[8]])->limit(1)->get()->toArray();
        // echo '<pre>';print_r($word);die;
        if(!empty($word)){
            Words::where('id',$word[0]['id'])->update(['seperate_root_word'=>$row[4]]);
            return new RegularMeaning([
                'word_id' => $word[0]['id'],
                'english_translation' => $row[2],
                'urdu_translation' => $row[3]
            ]);
        }
        return null;
    }
}
