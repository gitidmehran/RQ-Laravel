<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', -1);
use App\Exports\WordExport;
use App\Models\InfoData;
use App\Models\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

class UploadFileController extends Controller
{
    public function storeFile(Request $request){
        $folder_name = $request->folder_name ?? "";
        if(request()->hasFile('file')){
            $image = request()->file('file');
            $image_name = request()->file('file')->getClientOriginalName();
            $path = Storage::disk('s3')->put($folder_name, $image, 'public');
            $url = Storage::disk('s3')->url($path);
            
            // if($folder_name == "word_notes"){
            //     $note = new WordNotes();
            //     $note->url = $url;
            // }elseif($folder_name == "grammar_notes"){
            //     $note = new GrammarNotes();
            //     $note->url = $url;
            // }elseif($folder_name == "ayat_notes") {
            //     $note = new AyatNotes();
            //     $note->url = $url;
            // }
            // $note->save();
            // words note grammar note ayat notes
        }
        return response()->json([
            "message"=> "file uploaded successfully through RQ",
            "path" => $url
        ]);
    }

    public function updateFilesName(Request $request){
        $storage = Storage::disk('s3');
        $allfiles = $storage->allFiles('');
        
        foreach ($allfiles as $key => $value) {
            $file = (object) pathinfo($value);
            $extenshion = $file->extension;
            $newName = time().Str::random(60).'.'.$extenshion;
            
            $word = Words::where('arabic_grammar', $value)->orWhere('usmani_style', $value)->first();
            if(!empty($word)){
                if($word['arabic_grammar']==$value){
                    Words::where('id', $word['id'])->update(['arabic_grammar' => $newName]);
                }

                if($word['usmani_style']==$value){
                    Words::where('id', $word['id'])->update(['usmani_style' => $newName]);
                }
            }

            $storage->move($value, $newName);
        }
        die('update Successfully');
    }

    public function removeOldFiles(Request $request){
        $words = Words::whereNotNull('arabic_grammar')->orWhereNotNull('usmani_style')->get()->toArray();
        // $singleWord = Words::where('id', '1001002003')->first()->toArray();
        // echo '<pre>';print_r($singleWord);die;
        $arabicIds = [];

        $usmaniIds = [];
        foreach ($words as $key => $value) {
            if(strpos($value['arabic_grammar'], '-') != false){
                $arabicIds[] = $value['id'];
            }
            if(str_contains($value['usmani_style'], '-')){
                $usmaniIds[] = $value['id'];
            }
        }
        // Words::whereIntegerInRaw('id', $arabicIds)->update(['arabic_grammar' => null]);
        Words::whereIntegerInRaw('id', $usmaniIds)->update(['usmani_style' => null]);
        echo count($usmaniIds).'<br />';
        echo '<pre>';print_r($usmaniIds);die;
    }

    public function uploadDottedLessData(){
        // UPDATING AYATS DATA
        /*
        $infodata = InfoData::select('id','arabic_simple')->get()->toArray();
        foreach ($infodata as $key => $value) {
            $string = $value['arabic_simple'];
            $length = mb_strlen($string, 'UTF-8');
            $characters = [];
            for ($i = 0; $i < $length; $i++) {
                $characters[] = $this->swapLetter(mb_substr($string, $i, 1, 'UTF-8'));
            }
            $dottedLessString = implode("", $characters);
            InfoData::where('id', $value['id'])->update(['dottedless_arabic' => $dottedLessString]);
        }
        return response()->json(['success' => true, 'message' => 'Ayat Data Updated Successfully']); 
        */

        // UPDATE WORDS DATA
        $words = Words::select('id', 'simple_word')->offset(70000)->limit(10000)->get()->toArray();

        foreach ($words as $key => $value) {
            $string = $value['simple_word'];
            $length = mb_strlen($string, 'UTF-8');
            $characters = [];
            for ($i = 0; $i < $length; $i++) {
                $characters[] = $this->swapLetter(mb_substr($string, $i, 1, 'UTF-8'));
            }
            $dottedLessString = implode("", $characters);
            Words::where('id', $value['id'])->update(['dottedless_word' => $dottedLessString]);
        }
        return response()->json($words);
    }

    function swapLetter($letter) {
       switch ($letter) {
            case "أ":
            case "إ":
            case "آ":
                return "ا";

            case "ئ":
                return "ٮ";

            case "ب":
            case "ت":
            case "ي":
                return "ٮ";

            case "ج":
            case "خ":
                return "ح";

            case "ف":
            case "ق":
                return "ڡ";

            case "ذ":
                return "د";

            case "ز":
                return "ر";

            case "ظ":
                return "ط";

            case "ض":
                return "ص";

            case "غ":
                return "ع";

            case "ن":
                return "ں";

            case "ة":
                return "ه";

            case "ش":
                return "س";

            default:
                return $letter;
        }
    }

    public function missingWordData(){
        $infodata = InfoData::select('id','surah_id','ayatNo','arabic','arabic_simple')->withCount('words')->where('ayatNo', '!=', 0)->get()->toArray();
        $list = [];
        
        foreach ($infodata as $key => $value) {
            $simpleArr = explode(" ", $value['arabic_simple']);
            if(count($simpleArr) != $value['words_count']){
                $list[] = [
                    'surahNo' => $value['surah_id'],
                    'ayatNo' => $value['ayatNo'],
                    'ayatWordLength' => count($simpleArr),
                    'word_count' => $value['words_count']
                ];
            }
        }
        // return \Excel::download(new WordExport($list), 'word-difference.xlsx');
        return response()->json([
            'totalRecords' => count($list),
            'data' => $list
        ]);
    }

}
