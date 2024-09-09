<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\RootWordMeaning;
use App\Models\Words;
use App\Utility\Utility;
use Illuminate\Http\Request;
use Validator;

class QuranAyatsController extends Controller
{
 
  public function index(Request $request)
  {
    $validator = Validator::make($request->all(),[
      'surahId' => 'required',
      'scholarId' => 'sometimes|required'
    ]);
    if($validator->fails()){
      return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
    };
    $input = $validator->valid();
    $list = InfoData::where('surah_id',$input['surahId'])->select('id','surah_id','ayatNo','arabic')->orderBy('ayatNo')->groupBy('ayatNo')->get()->toArray();
    $alreadyAddedAyats = [];

    if(isset($input['scholarId']) && !empty($input['scholarId'])){
      $scholarId = $input['scholarId'];
      $ayatIds = array_unique(array_column($list,'id'));
      $ayatTranslations = AyatsTranslation::where('scholar_id',$scholarId)->whereIn('ayat_id',$ayatIds)->get()->toArray();
      $alreadyAddedAyats = array_unique(array_column($ayatTranslations,'ayat_id')); 
    }
    
    foreach ($list as $key => $value) {
      $list[$key]['reference'] = $value['surah_id'].':'.$value['ayatNo'];
      $list[$key]['alreadyAdded'] = (boolean)in_array($value['id'],$alreadyAddedAyats);
    }

    try {
      $response = [
        'success' => true,
        'message' => 'Ayat Data Retrieved Successfully',
        'filters' => $input,
        'list' => $list
      ];
    } catch (\Exception $e) {
      $response = array('success' => false, 'message' => $e->getMessage());
    }
    $response = Utility::convertKeysToCamelCase($response);
    return response()->json($response);
  }


  public function searchAyats(Request $request){
    try{
        $surah = $request->input('surah');
        $ayats = InfoData::where('surah_id',$surah)->select('id','ayatNo')->orderBy('ayatNo')->groupBy('ayatNo')->get()->toArray();
        $response = [
                   'success' => true,
                   'message' => 'SearchAyats Data Retrieved Successfully',
                   'surah' => $surah,
                   'ayats' => $ayats
                ];
      } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage());
      }
        return response()->json($response);
    //return response()->json(['success'=>true,'ayats'=>$ayats]);
  }

  public function quranicData(Request $request){
    try {
      $ayats = \Cache::rememberForever('ayats', function(){
        return InfoData::select('id','surah_id','ayatNo','arabic','arabic_simple')->get()->toArray();
      });

      $words = \Cache::rememberForever('words', function(){
        return Words::select(['id','surah_no','ayat_no','ayat_id','reference','word','simple_word','root_word','seperate_root_word'])->get()->toArray();
      });

      $rootWords = \Cache::rememberForever('rootWords', function(){
        return RootWordMeaning::select(['id','english_root_word','root_word','seprate_root_word','first','second','third','meaning_urdu','meaning_eng'])->get()->toArray();
      });
      
      $response = [
        'success' => true,
        'message' => 'Basic Quranic Data Retrieved Successfully',
        'quranic_ayats' => $ayats,
        'words' => $words,
        'root_words' => $rootWords
      ];
    } catch (\Exception $e) {
      $response = array('success' => false, 'message' => 'Something Went Wrong','error' => $e->getMessage());
    }
    $response = Utility::convertKeysToCamelCase($response);
    return response()->json($response);
  }

}
