<?php

namespace App\Http\Controllers\Api;
ini_set('memory_limit', '-1');
set_time_limit(0);
ini_set('max_execution_time', '-1');
use App\Http\Controllers\Controller;
use App\Models\compoundwords;
use App\Imports\InfoDataImport;
use App\Imports\RegularMeaningImporter;
use App\Imports\WordsImport;
use App\Imports\WordImporter;
use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\RegularMeaning;
use App\Models\Words;
use App\Models\WordsTranslations;
use App\Models\WordReferences;
use App\Models\PhrasesWords;
use App\Models\OtherWordsInfo;
use App\Models\WordNotes;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon,File,DB;

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

    public function importXMLTranslation() {
        try {
        
            $xml=simplexml_load_file("ia_all.xml") or die("Error: Cannot create object");
            $ayats = $xml->Suwar->Surah[0]->Ayah;
            $list = [];
    
            $s = 1000;
            for ($i=0; $i < 114; $i++) { 
                $ayats = $xml->Suwar->Surah[$i]->Ayah;
                
                $a = ++$s . '001';
                foreach ($ayats as $key => $value) {
                    foreach ($value as $k => $val) {
                        if(end($val['Source']) == 'Sam Gerrans') {
                            $list[] = [
                                'ayat_id' => $a,
                                'scholar_id' => 50,
                                'language_id' => 2,
                                'translation' => $val
                                // 'translation' => end($val)
                            ];
                        }
                    }
                    $a++;
                }
            }
            // AyatsTranslation::insert($list);
            
            echo '<pre>'; print_r($list); exit;

            echo 'Done...............';
            
        } catch (\Throwable $th) {
            
            echo "<pre>";
            print_r($th->getMessage());
        }
    }

    public function importFromJson(Request $request){
        
        // $totalTranslations = AyatsTranslation::count();
        // $lastFiveRecords = AyatsTranslation::orderBy('id', 'desc')->limit(5)->get()->toArray();
        // echo $totalTranslations. '<br >';
        // echo '<pre>';print_r($lastFiveRecords);
        // die;
        $data = file_get_contents(storage_path() . "/ayats_translations.json");
        $translations = json_decode($data, true);
        $newtranslations = array_filter($translations, function($item){
            return ($item['ayat_id'] > '1070031');
        });

        foreach ($newtranslations as $key => $value) { 
            unset($value['id']); 
            AyatsTranslation::create($value);
        }
        echo 'Records Inserted in RQuran Database';
    }

    public function importSingleScholar(Request $request){
        $translations = \DB::table('ayats_translations_new')->where('scholar_id', 50)->get();
        $translationArray = json_decode($translations, true);
        foreach ($translationArray as $key => $value) {
            unset($value['id']);
            AyatsTranslation::create($value);
        }
        echo 'Translations Inserted Successfully';
    }

    public function importMissingWordData(){
        try {
            $words = Words::with('ayat')->where('simple_word', '')->get()->toArray();
            
            $list = $missinglist = [];
            foreach ($words as $key => $value) {
                $ayat = $value['ayat'];
                $arabicSimple = explode(' ',$ayat['arabic_simple']);
                if(!isset($arabicSimple[$value['reference']-1])){
                    Words::where('id', $value['id'])->update(['simple_word' => $arabicSimple[$value['reference']-2]]);
                    // $list[] = [
                    //     'surah_no' => $value['surah_no'],
                    //     'ayat_no' => $value['ayat_no'],
                    //     'reference' => $value['reference'],
                    //     'word' => $value['word'],
                    //     'simpleWord' => $arabicSimple[$value['reference']-2] ?? ""
                    // ];
                }else{
                    Words::where('id', $value['id'])->update(['simple_word' => $arabicSimple[$value['reference']-1]]);
                }
                // $list[] = [
                //     'surah_no' => $value['surah_no'],
                //     'ayat_no' => $value['ayat_no'],
                //     'reference' => $value['reference'],
                //     'word' => $value['word'],
                //     'simpleWord' => $arabicSimple[$value['reference']-1] ?? ""
                // ];
            }
            return response()->json([
               'message' => 'Data Update Successfully',
               'data' => Words::where('simple_word', '')->get()->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

}
