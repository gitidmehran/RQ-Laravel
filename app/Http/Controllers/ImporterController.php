<?php

namespace App\Http\Controllers;
ini_set('memory_limit', '-1');
set_time_limit(0);
ini_set('max_execution_time', '-1');
use App\Http\Controllers\compoundwords;
use App\Imports\InfoDataImport;
use App\Imports\RegularMeaningImporter;
use App\Imports\WordImporter;
use App\Imports\WordsImport;
use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\OtherWordTranslationInfo;
use App\Models\OtherWordsInfo;
use App\Models\PhrasesWords;
use App\Models\RegularMeaning;
use App\Models\RootWordMeaning;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\WordReferences;
use App\Models\Words;
use App\Models\WordsTranslations;
use Carbon,File,DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImporterController extends Controller
{
    public function importHaniTranslations(Request $request){
        $data = InfoData::where('hani_trans','!=','')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $key => $value) {
                AyatsTranslation::where(['ayat_id'=>$value['id'],'scholar'=>2])->update(['translation'=>$value['hani_trans']]);
            }
        }
        die('imported');
    }

    public function ayatTranslationImport(Request $request){
        try{
            $path = $request->file('image');
            $data = Excel::toArray(new InfoDataImport(), $path);
            $data = $data[0];
            $scholars = array_shift($data);
            $nulldata = array_pop($data);
            if(!empty($data)){
              foreach($data as $key => $val){
                $row = InfoData::where(['surahNo'=>$val[1],'ayatNo'=>$val[2]])->orderBy('id','desc')->first();
                $shaheeInt = [
                  'ayat_id' => $row['id'],
                  'scholar' => 1,
                  'translation' => $val[5],
                  'language'    => 2
                ];
                $drTahir = [
                  'ayat_id' => $row['id'],
                  'scholar' => 3,
                  'translation' => $val[7],
                  'language'    => 1
                ];
                $drHany = [
                  'ayat_id' => $row['id'],
                  'scholar' => 2,
                  'translation' => $val[6],
                  'language'    => 2
                ];
                            // echo '<pre>';print_r($shaheeInt);die;
                AyatsTranslation::create($shaheeInt);
                AyatsTranslation::create($drTahir);
                AyatsTranslation::create($drHany);
              }
            }
            $response = ['success'=>true,'message'=>'Record Inserted'];
        }catch(\Exception $e){
            $response = ['success'=>false,'message'=>$e->getMessage()];
        }

        return response()->json($response);
    }

    public function importWords(Request $request){
        try{
            Excel::import(new WordsImport, $request->file);
            $response = ['flag'=>true,'msg'=>'Imported'];
        }catch(\Exception $e){
            $response = ['flag'=>false,'msg'=>$e->getMessage()];
        }
        return response()->json($response);
    }

    public function importCompoundWords(Request $request){
        try{
            $words_array = Words::whereNotNull('prefix')->orWhereNotNull('postfix')->get()->toArray();
            
            if(!empty($words_array)){
                foreach ($words_array as $key => $value) {
                    $compoundArray = [];
                    if(!empty($value['prefix'])){
                        $compoundArray[] = [
                            'word_id' => $value['id'],
                            'word' => $value['prefix'],
                            'created_at' => Carbon\Carbon::now()
                        ];
                    }
                    $compoundArray[] = [
                        'word_id' => $value['id'],
                        'word' => $value['root_word'],
                        'created_at' => Carbon\Carbon::now()
                    ];

                    if(!empty($value['postfix'])){
                        $compoundArray[] = [
                            'word_id' => $value['id'],
                            'word' => $value['postfix'],
                            'created_at' => Carbon\Carbon::now()
                        ];
                    }
                    compoundwords::insert($compoundArray);
                }
            }
            $response = ['flag'=>true,'msg'=>'Compound data inserted'];
        }catch(\Exception $e){
            $response = ['flag'=>false,'msg'=>$e->getMessage()];
        }
        return response()->json($response);
    }

    public function importRegularMeanings(Request $request){
        try{
            Excel::import(new RegularMeaningImporter, $request->file);
            $response = ['flag'=>true,'msg'=>'Imported'];
        }catch(\Exception $e){
            $response = ['flag'=>false,'msg'=>$e->getMessage()];
        }
        return response()->json($response);
    }

    public function importWordsTranslation(Request $request){
        try{
            $data = RegularMeaning::all()->toArray();
            foreach($data as $key => $value){
                WordsTranslations::create([
                    'word_id' => $value['word_id'],
                    'scholar' => 4,
                    'translation' => $value['english_translation'],
                    'language'    => 2
                ]);
                WordsTranslations::create([
                    'word_id' => $value['word_id'],
                    'scholar' => 4,
                    'translation' => $value['urdu_translation'],
                    'language'    => 1
                ]);
            }
            $response = ['flag'=>true,'msg'=>'Imported'];
        }catch(\Exception $e){
            $response = ['flag'=>false,'msg'=>$e->getMessage()];
        }
        return response()->json($response); 
    }

    public function convertExcelToArray(Request $request){
        $path = $request->file('image');
        $data = Excel::toArray(new WordImporter(), $path);
        $data = $data[0];
        $scholars = array_shift($data);
        $nulldata = array_pop($data);
        // File::put('wordsdata.json', json_encode($data), true);
        return response()->json(['data'=>$data]);
    }

    public function updateAyatIds(Request $request){
        try {
            $words = Words::select('id','surah_no','ayat_no')->get()->toArray();
            foreach ($words as $key => $value) {
                $infodata = InfoData::where(['surahNo'=>$value['surah_no'],'ayatNo'=>$value['ayat_no']])->first()->toArray();
                Words::where('id',$value['id'])->update(['ayat_id'=>$infodata['id']]);
            }
            $response = array('success'=>true,'message'=>'Updated Successfully');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function replaceAyatKeys(Request $request)
    {
        try {
            $infodata = InfoData::all()->toArray();
            foreach ($infodata as $key => $value) {
                $newid = 10000 + $value['id'];
                Words::where('ayat_id',$value['id'])->update(['ayat_id'=>$newid]);
                InfoData::where('id',$value['id'])->update(['id_key'=>$newid]);
            }
            $response = array('success'=>true,'message'=>'Ayat Keys Updated Successfully');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function replaceWordKeys(Request $request)
    {
        try {
            $words = Words::all()->toArray();
            $newids = [];
            foreach ($words as $key => $value) {
                $surahNo = $value['surah_no'];
                $ayatNo = $value['ayat_no'];
                $reference = $value['reference'];

                $newsurah = ($surahNo < 10) ? '00'.$surahNo : (($surahNo < 100) ? '0'.$surahNo : $surahNo);
                $newayat = ($ayatNo < 10) ? '00'.$ayatNo : (($ayatNo < 100) ? '0'.$ayatNo : $ayatNo);
                $newreference = ($reference < 10) ? '00'.$reference : (($reference < 100) ? '0'.$reference : $reference);
                $newid = '1'.$newsurah.$newayat.$newreference;

                // OtherWordsInfo::where('word_id',$value['id'])->update(['word_id'=>$newid]);
                // WordNotes::where('word_id',$value['id'])->update(['word_id'=>$newid]);
                // WordReferences::where('word_id',$value['id'])->update(['word_id'=>$newid]);
                // WordReferences::where('reference_word_id',$value['id'])->update(['word_id'=>$newid]);
                // PhrasesWords::where('word_id',$value['id'])->update(['word_id'=>$newid]);
                // PhrasesWords::where('phrase_word_id',$value['id'])->update(['word_id'=>$newid]);
                WordsTranslations::where('word_id',$value['id'])->update(['word_id'=>$newid]);
                Words::where('id',$value['id'])->update(['id_key'=>$newid]);
            }
            $response = array('success'=>true,'message'=>'Word key Updated Successfully','ids'=>$newids);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function importHaniTranslation()
    {
        $records = \DB::table('infodata_18-05-22')->where('hani_trans','!=','')->get()->toArray();
        foreach ($records as $key => $value) {
            $surahNo = $value->surahNo;
            $ayatNo  = $value->ayatNo;
            $newsurah = ($surahNo < 10) ? '00'.$surahNo : (($surahNo < 100) ? '0'.$surahNo : $surahNo);
            $newayat = ($ayatNo < 10) ? '00'.$ayatNo : (($ayatNo < 100) ? '0'.$ayatNo : $ayatNo);

            $new_key_id = "1".$newsurah.$newayat;
            AyatsTranslation::create([
                'language' => '2',
                'scholar'  => '2',
                'translation' => $value->hani_trans,
                'ayat_id' => $new_key_id
            ]);
        }
        return response()->json(['success'=>true,'count'=>count($records),'data'=>$records]);
    }

    public function getTranslation(Request $request)
    {
        $list = InfoData::with('ayatsTranslations')->limit(10)->get()->toArray();
        return response()->json($list,200, [], JSON_UNESCAPED_UNICODE);
    }

    public function refreshData(Request $request){
        // die('refreshData');
        $updatedAyatTranslations = DB::table('ayats_translations_new')->where('id','>','16215')->get()->toArray();
        $updatedAyatTranslationsArray = json_decode(json_encode($updatedAyatTranslations), true);
        foreach ($updatedAyatTranslationsArray as $key => $value) {
            if(isset($value['created_at'])) unset($value['created_at']);
            if(isset($value['updated_at'])) unset($value['updated_at']);
            AyatsTranslation::create($value);
        }
        echo count($updatedAyatTranslationsArray).' inserted';die;
        // UPDATE ROOT WORD IDS
        $rootWords = DB::table('words_new')->get()->toArray();
        $rootWordsArray = json_decode(json_encode($rootWords), true);
        $rootWordData = RootWordMeaning::get()->toArray();
        $rootWordIDs = array_unique(array_column($rootWordData,'id'));
        foreach ($rootWordsArray as $key => $value) {
            if(in_array($value['root_word_id'], $rootWordIDs)){
                Words::where([
                    'surah_no' => $value['surah_no'],
                    'ayat_no' => $value['ayat_no'],
                    'reference' => $value['reference']
                ])->update(['root_word_id' => $value['root_word_id']]);
            }
        }
        die('Root Word ID Updated Successfully');
        // UPDATE Grammar NOTES DATA
        $usmaniNotes = DB::table('words_new')->whereNotNull('arabic_grammar')->get()->toArray();
        $usmaniNotesArray = json_decode(json_encode($usmaniNotes), true);
        foreach ($usmaniNotesArray as $key => $value) {
            Words::where([
                'surah_no' => $value['surah_no'],
                'ayat_no' => $value['ayat_no'],
                'reference' => $value['reference']
            ])->update(['arabic_grammar'=> $value['arabic_grammar']]);
        }
        die('Grammar Notes Updated Successfully');

        // UPDATE USMANI NOTES DATA
        $usmaniNotes = DB::table('words_new')->whereNotNull('usmani_style')->get()->toArray();
        $usmaniNotesArray = json_decode(json_encode($usmaniNotes), true);
        foreach ($usmaniNotesArray as $key => $value) {
            Words::where([
                'surah_no' => $value['surah_no'],
                'ayat_no' => $value['ayat_no'],
                'reference' => $value['reference']
            ])->update(['usmani_style'=> $value['usmani_style']]);
        }
        die('Usmani Notes Updated Successfully');

        // IMPORT MISSING USER DATA
        $newusers = DB::table('users_new')->get()->toArray();
        $useArray = json_decode(json_encode($newusers), true);
        foreach ($useArray as $key => $value) {
            User::where('name', $value['name'])->update($value);
            // $check = User::where('email', $value['email'])->where('id', $value['id'])->count();
            // if(empty($check) && $value['id'] > 20){
            //     User::create($value);
            // }
        }
        die('users');
        $words = Words::get()->toArray();
        $wordIds = array_unique(array_column($words,'id'));
        $users = User::where('role', 3)->get()->toArray();
        $userIds = array_unique(array_column($users,'id'));

        // UPDATE SIMPLE WORD IN WORDS TABLE
        
        $newwords = DB::table('words_new')->get()->toArray();
        $wordsArray = json_decode(json_encode($newwords), true);
        foreach ($wordsArray as $key => $value) {
            $singleWord = Words::where(['surah_no'=> $value['surah_no'], 'ayat_no'=> $value['ayat_no'], 'reference' => $value['reference']])->first()->toArray();
            if(!empty($singleWord) && empty($singleWord['simple_word'])){
                Words::where(['surah_no'=> $value['surah_no'], 'ayat_no'=> $value['ayat_no'], 'reference' => $value['reference']])->update(['simple_word' => $value['word_wo']]);    
            }
            
        }
        die('Sample Word Updated Successfully');
        
        // Update OtherInfo
        $otherInfo = DB::table('other_words_infos')->get();
        $otherArray = json_decode(json_encode($otherInfo), true);
        if(!empty($otherArray)){
            foreach ($otherArray as $key => $value) {
                if($value['reference_type'] !='both' && $value['reference_type'] !='number'){
                    if(in_array($value['word_id'], $wordIds) && in_array($value['scholar'], $userIds)){
                        $row = [
                            'word_id' => $value['word_id'],
                            'scholar_id' => $value['scholar'],
                            'addresser' => $value['addresser'],
                            'addressee' => $value['addressee'],
                            'quranic_lexicon' => ($value['reference_type']=='by_reference') ? 'single_al_word':'None',
                            'quranic_lexicon_type' => ($value['reference_type']=='by_reference') ? 'Source':'',
                            'quranic_lexicon_number' => $value['reference_type_number'],
                            'disable' => false
                        ];
                        OtherWordTranslationInfo::create($row);
                        if($value['reference_type']=="by_reference"){
                            $referencewords = DB::table('word_references')->where(['word_id' => $value['word_id'],'scholar' => $value['scholar']])->get();
                            $referencewordsArray = json_decode(json_encode($referencewords), true);
                            if(!empty($referencewordsArray)){
                                foreach ($referencewordsArray as $ikey => $val) {
                                    if(in_array($val['reference_word_id'], $wordIds)){
                                        $referenceRow = [
                                            'word_id' => $val['reference_word_id'],
                                            'scholar_id' => $value['scholar'],
                                            'addresser' => $value['addresser'],
                                            'addressee' => $value['addressee'],
                                            'quranic_lexicon' => '',
                                            'quranic_lexicon_type' => 'Referred',
                                            'quranic_lexicon_number' => 0,
                                            'disable' => true,
                                            'reference_word' => $value['word_id']
                                        ];
                                        OtherWordTranslationInfo::create($referenceRow);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        die('Other Info Update Successfully');

        // OTHER WORD TRANSLATIONS
        $otherInfo = DB::table('other_word_translation_infos_copy')->get()->toArray();
        $otherInfoArray = json_decode(json_encode($otherInfo), true);
        if(!empty($otherInfoArray)){
            foreach ($otherInfoArray as $key => $value) {
                if(in_array($value['word_id'], $wordIds) && in_array($value['reference_word'], $wordIds) && in_array($value['scholar_id'], $userIds)){
                    OtherWordTranslationInfo::create($value);
                }
            }
        }
        die('Other info data updated Successfully');

        // WORD TRANSLATIONS
        $wordTranslations = DB::table('words_translations_new')->get()->toArray();
        $wordTranslationsArray = json_decode(json_encode($wordTranslations), true);
        if(!empty($wordTranslationsArray)){
            foreach ($wordTranslationsArray as $key => $value) {
                if(in_array($value['word_id'], $wordIds) && in_array($value['scholar'], $userIds)){
                    $data = [
                        'word_id' => $value['word_id'],
                        'scholar_id' => $value['scholar'],
                        'language_id' => $value['language'],
                        'translation' => $value['translation'],
                    ];
                    WordsTranslations::create($data);    
                }
                
            }
        }
        die('Word Translations data Imported Successfully');

        // UPDATE WORDS TABLE
        $newwords = DB::table('words_new')->get()->toArray();
        $wordsArray = json_decode(json_encode($newwords), true);
        foreach ($wordsArray as $key => $value) {
            if(isset($value['iid'])) unset($value['iid']);
            
            Words::create($value);
        }
        die('word inserted');

        // UPDATE AYAT TRANSLATIONS
        $translations = DB::table('ayats_translations_new')->get()->toArray();
        $translationArray = json_decode(json_encode($translations), true);
        $infodata = InfoData::get()->toArray();
        $infodataIds = array_unique(array_column($infodata,'id'));
        // scholars 
        $users = User::where('role', 3)->get()->toArray();
        $userIds = array_unique(array_column($users,'id'));

        foreach ($translationArray as $key => $value) {
            if(in_array($value['ayat_id'], $infodataIds) && in_array($value['scholar'], $userIds)){
                $data = [
                    'ayat_id' => $value['ayat_id'],
                    'language_id' => $value['language'],
                    'scholar_id' => $value['scholar'],
                    'translation' => $value['translation']
                ];
                AyatsTranslation::create($data);       
            }
        }
        die('Ayat Translation Updated Successfully');
    }

}
