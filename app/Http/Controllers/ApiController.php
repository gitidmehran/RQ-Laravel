<?php

namespace App\Http\Controllers;

ini_set('memory_limit', '-1');
set_time_limit(0);
ini_set('max_execution_time', '-1');
use Illuminate\Http\Request;
use App\Models\WordReferences;
use App\Models\WordsTranslations;
use Carbon;

class ApiController extends Controller
{
    public function updateReferenceWords(Request $request){
        $limit = 100;
        $references = WordReferences::all()->toArray();
        $scholars = array_unique(array_column($references,'scholar'));
        $word_ids = array_unique(array_column($references,'word_id'));
        $translations = WordsTranslations::whereIn('scholar',$scholars)->whereIn('word_id',$word_ids)->get()->toArray();
        
        $reference_array = collect([]);
        foreach ($references as $key => $val) {
            foreach ($translations as $ival) {
                if($val['word_id']==$ival['word_id'] && $val['scholar']==$ival['scholar']){
                    $reference_array->push([
                        'word_id'     => $val['reference_word_id'],
                        'scholar'     => $ival['scholar'],
                        'language'    => $ival['language'],
                        'translation' => $ival['translation'],
                        'is_reference_word' => 1,
                        'created_at' => Carbon\Carbon::now()
                    ]);   
                }
            }
        }
        $chunks = $reference_array->chunk(500);
        foreach ($chunks as $val) {
            WordsTranslations::insert($val->toArray());
        }
        echo 'inserted <br />';
        echo count($reference_array);
    }

    public function updateReference(Request $request){
        $reference = WordReferences::join('words_translations','words_translations.word_id','=','word_references.word_id')
            ->where('words_translations.scholar','=','word_references.scholar')
            ->limit(5)
            ->get()
            ->toArray();
        echo '<pre>';print_r($reference);die;
    }
}
