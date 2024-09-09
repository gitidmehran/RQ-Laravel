<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\Words;
use App\Utility\Utility;
use File,Auth,Validator,DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WordsNotesController extends Controller
{
    protected $per_page = 50;
    protected $bucketPath = "word-notes";

    public function index(Request $request)
    {
        try{
            $files = Storage::disk('s3')->allFiles($this->bucketPath);
            $user = Auth::user();
            $query  = WordNotes::with('scholar:id,name','word:id,word,surah_no,ayat_no','language:id,name')->orderBy('id','desc');

            if($user->role !=1){
                $query = $query->where('scholar_id', $user->id);
            }

            if ($request->has('query') && !empty($request->input('query'))) {
                $search = $request->get('query');
                $query = $query->where('note_label', 'like', '%' . $search . '%');
            }

            if($request->has('per_page') && !empty($request->input('per_page')))
                $this->per_page = $request->get('per_page');

            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $result = $query->toArray();
            
            $list = [];
            if(!empty($result['data']))
            {
                foreach($result['data'] as $key => $value ){
                    $list[] = [
                        'id' => $value['id'],
                        'surah_no' => $value['word']['surah_no'],
                        'ayat_no' => $value['word']['ayat_no'],
                        'word_reference' => $value['word']['reference'] ?? '',
                        'word_id' => $value['word']['id'] ?? '',
                        'word' => $value['word']['word'] ?? '',
                        'scholar_id' => $value['scholar_id'],
                        'scholar_name' => $value['scholar']['name'] ?? '',
                        'language_id' => $value['language_id'],
                        'language_name' => $value['language']['name'] ?? '',
                        'note_label' => $value['note_label'],
                        'note_file' => $value['note_file'],
                        'url' => Utility::generateS3BucketUrl($value['note_file'], $this->bucketPath, $files)
                    ];
                }
            }
            $response = [
                'success' => true,
                'message' => 'Words Notes Data Retrieved Successfully',
                'total_records' => $total,
                'list' => $list
            ];
            $response = Utility::convertKeysToCamelCase($response);
            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage());
            }
        return response()->json($response);
    
    }
    public function store(Request $request)
    {

       $validator = Validator::make($request->all(),[
            'scholarId' => 'required',
            'ayatId'    => 'required',
            'wordId'    => 'required',
            'languageId' => 'required',
            'surahId'   => 'required',
            'noteLabel' => 'required',
            'noteFile'  => 'required'
        ]);
        if($validator->fails()){
         return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        try {
            $isAlreadyAdded = WordNotes::where(['word_id'=>$input['wordId'],'scholar_id'=>$input['scholarId'],'language_id'=>$input['languageId']])->count();
            if(!empty($isAlreadyAdded)){
                return response()->json(['success' => false, 'message' => "Word note already added, You Can't add Duplicate word note."], 422);
            }

            $input['noteFile'] = Utility::uplaodFileToS3Bucket($request,$this->bucketPath, 'noteFile', "");
            $data = [
                'scholar_id' => $input['scholarId'],
                'word_id' => $input['wordId'],
                'note_file' => $input['noteFile'],
                'note_label' => $input['noteLabel'],
                'language_id' => $input['languageId']
            ];
            $note = WordNotes::create($data);
            $result = WordNotes::with('scholar:id,name','word:id,word,surah_no,ayat_no','language:id,name')->find($note['id'])->toArray();
            
            $row = [
                'id' => $result['id'],
                'surah_no' => $result['word']['surah_no'],
                'ayat_no' => $result['word']['ayat_no'],
                'word_reference' => $result['word']['reference'] ?? '',
                'word_id' => $result['word']['id'] ?? '',
                'word' => $result['word']['word'] ?? '',
                'scholar_id' => $result['scholar']['id'],
                'scholar_name' => $result['scholar']['name'] ?? '',
                'language_id' => $result['language']['id'],
                'language_name' => $result['language']['name'] ?? '',
                'note_label' => $result['note_label'],
                'note_file' => $result['note_file'],
                'url' => Utility::generateS3BucketUrl($result['note_file'], $this->bucketPath)
            ];

            $response = [
                'success' => true,
                'message' => 'Word Note Added Successfully',
                'row' => $row
            ];
            $response = Utility::convertKeysToCamelCase($response);
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);

    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'scholarId' => 'required',
            'ayatId'    => 'required',
            'wordId'    => 'required',
            'surahId'   => 'required',
            'languageId'   => 'required',
            'noteLabel' => 'required',
            'noteFile'  => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = WordNotes::find($id);

        try {
            $input['noteFile'] = Utility::uplaodFileToS3Bucket($request, $this->bucketPath, 'noteFile', $note['note_file']);

            $note->update([
                "scholar_id" => $input['scholarId'],
                "word_id" => $input['wordId'],
                'note_file' => $input['noteFile'],
                'note_label' => $input['noteLabel'],
                'language_id' => $input['languageId']
            ]);


            $result = WordNotes::with('scholar:id,name','word:id,word,surah_no,ayat_no','language:id,name')->find($id)->toArray();
            
            $row = [
                'id' => (int)$id,
                'surah_no' => $result['word']['surah_no'],
                'ayat_no' => $result['word']['ayat_no'],
                'word_reference' => $result['word']['reference'] ?? '',
                'word_id' => $result['word']['id'] ?? '',
                'word' => $result['word']['word'] ?? '',
                'scholar_id' => $result['scholar']['id'],
                'scholar_name' => $result['scholar']['name'] ?? '',
                'language_id' => $result['language']['id'],
                'language_name' => $result['language']['name'] ?? '',
                'note_label' => $result['note_label'],
                'note_file' => $result['note_file'],
                'url' => Utility::generateS3BucketUrl($result['note_file'], $this->bucketPath)
            ];
            
            $response = [
                'success' => true,
                'message' => 'Ayats Notes Data Updated Successfully',
                'row' => $row
            ];
            $response = Utility::convertKeysToCamelCase($response);
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line' => $e->getLine());
       }
       return response()->json($response);
    }
    
    public function destroy($id)
    {
        try {
            $note = WordNotes::with('word','scholar','language:id,name')->find($id);
            if(empty($note)){
                return response()->json(['success' => false, 'message' => 'No Record Found.']);
            }

            $note->delete();

            $row = [
                'id' => (int)$id,
                'surah_no' => $note['word']['surah_no'],
                'ayat_no' => $note['word']['ayat_no'],
                'word_reference' => $note['word']['reference'] ?? '',
                'word' => $note['word']['word'] ?? '',
                'scholar_id' => $note['scholar']['id'],
                'scholar_name' => $note['scholar']['name'] ?? '',
                'language_id' => $note['language']['id'],
                'language_name' => $note['language']['name'] ?? '',
                'note_file' => $note['note_file'],
                'url' => Utility::deleteFileFromS3Bucket($note['note_file'], $this->bucketPath)
            ];
            $response = array('success'=>true,'message'=>'Record Deleted!','row'=> $row);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }


    public function search(Request $request)
    {
        try {
            $input = $request->all();
            $list = Words::where(['surah_no' => $input['surahNo'],'ayat_no'=>$input['ayatNo']])->select('id','word')->get()->toArray();
            $response = [
                    'success' => true,
                    'message' => 'Words Data find Successfully',
                    'list' => $list
                ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }

}
