<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AyatNotes;
use App\Models\User;
use App\Utility\Utility;
use File,Auth,Validator,DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AyatNotesController extends Controller
{
    protected $per_page = 50;
    protected $bucketPath = "ayat-notes";

    public function index(Request $request)
    {
        try{
            $files = Storage::disk('s3')->allFiles($this->bucketPath);
            $user = Auth::user();
            $query  = AyatNotes::with('scholar:id,name','ayat:id,surah_id,ayatNo','language:id,name')->orderBy('id','desc');

            if($user->role !=1){
                $query = $query->where('scholar_id', $user->id);
            }

            if ($request->has('query') && !empty($request->input('query'))) {
                $search = $request->get('query');
                $query = $query->where('note_label', 'like', '%' . $search . '%');
            }

            if($request->has('perPage') && !empty($request->get('perPage')))
                $this->per_page = $request->input('perPage');

            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $result = $query->toArray();
            $list = [];
            if(!empty($result['data']))
            {
                foreach($result['data'] as $key => $value ){
                    $list[] = [
                        'id' => $value['id'],
                        'surah_no' => $value['ayat']['surah_id'],
                        'ayat_no' => $value['ayat']['ayatNo'],
                        'ayat_id' => $value['ayat_id'],
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
                'message' => 'Ayats Notes Data Retrieved Successfully',
                'total_records' => $total,
                'list' => $list,
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
            'languageId' => 'required',
            'ayatId'    => 'required',
            'noteLabel' => 'required',
            'noteFile'  => 'required'
       ]);
       if($validator->fails()){
        return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
       };
       $input = $validator->valid();
    
       try {
            $isAlreadyAdded = AyatNotes::where(['ayat_id'=>$input['ayatId'],'scholar_id'=>$input['scholarId'],'language_id'=>$input['languageId']])->count();

            if(!empty($isAlreadyAdded)){
                return response()->json(['success' => false, 'message' => "Ayat Note already added, You Can't add Duplicate"], 422);
            }

            $input['noteFile'] = Utility::uplaodFileToS3Bucket($request,$this->bucketPath, 'noteFile', "");
        
            $record = [
                'scholar_id' => $input['scholarId'],
                'language_id' => $input['languageId'],
                'ayat_id' => $input['ayatId'],
                'note_file' => $input['noteFile'],
                'note_label' => $input['noteLabel']
            ];
            $note = AyatNotes::create($record);
            
            $note = AyatNotes::with('scholar:id,name','ayat:id,surah_id,ayatNo','language:id,name')->find($note['id'])->toArray();
            $row = [
                'id' => $note['id'],
                'surah_no' => $note['ayat']['surah_id'],
                'ayat_no' => $note['ayat']['ayatNo'],
                'ayat_id' => $note['ayat_id'],
                'scholar_id' => $note['scholar_id'],
                'scholar_name' => $note['scholar']['name'] ?? '',
                'language_id' => $note['language_id'],
                'language_name' => $note['language']['name'] ?? '',
                'note_label' => $note['note_label'],
                'note_file' => $note['note_file'],
                'url' => Utility::generateS3BucketUrl($note['note_file'], $this->bucketPath)
            ];

            $response = [
                'success' => true,
                'message' => 'Ayat Note Data Retrieved Successfully',
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
            'languageId' => 'required',
            'ayatId'    => 'required',
            'noteLabel' => 'required',
            'noteFile'  => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = AyatNotes::find($id);
        try {

            $input['noteFile'] = Utility::uplaodFileToS3Bucket($request, $this->bucketPath, 'noteFile', $note['note_file']);
            
            $record = [
                'scholar_id' => $input['scholarId'],
                'language_id' => $input['languageId'],
                'ayat_id' => $input['ayatId'],
                'note_file' => $input['noteFile'],
                'note_label' => $input['noteLabel']
            ];

            $note->update($record);
            
            $note = AyatNotes::with('scholar:id,name','ayat:id,surah_id,ayatNo','language:id,name')->find($id)->toArray();
            $row = [
                'id' => $note['id'],
                'surah_no' => $note['ayat']['surah_id'],
                'ayat_no' => $note['ayat']['ayatNo'],
                'ayat_id' => $note['ayat_id'],
                'scholar_id' => $note['scholar_id'],
                'scholar_name' => $note['scholar']['name'] ?? '',
                'language_id' => $note['language_id'],
                'language_name' => $note['language']['name'] ?? '',
                'note_label' => $note['note_label'],
                'note_file' => $note['note_file'],
                'url' => Utility::generateS3BucketUrl($note['note_file'], $this->bucketPath)
            ];

            $response = [
                'success' => true,
                'message' => 'Ayats Notes Data Updated Successfully',
                'row' => $row
            ];
            $response = Utility::convertKeysToCamelCase($response);
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);
    }
    
    public function destroy($id)
    {
        try {
            $note = AyatNotes::with('scholar:id,name','ayat:id,surah_id,ayatNo','language:id,name')->find($id);
            $note->delete();
            
            $row = [
                'id' => $note['id'],
                'surah_no' => $note['ayat']['surah_id'],
                'ayat_no' => $note['ayat']['ayatNo'],
                'ayat_id' => $note['ayat_id'],
                'scholar_id' => $note['scholar_id'],
                'scholar_name' => $note['scholar']['name'] ?? '',
                'language_id' => $note['language_id'],
                'language_name' => $note['language']['name'] ?? '',
                'note_label' => $note['note_label'],
                'note_file' => $note['note_file'],
                'url' => Utility::deleteFileFromS3Bucket($note['note_file'], $this->bucketPath)
            ];
            $response = array('success'=>true,'message'=>'Record Deleted!','row'=> $row);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
}
