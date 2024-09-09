<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Words;
use App\Utility\Utility;
use Illuminate\Http\Request;
use Validator, File ,Auth;

class GrammarNotesController extends Controller
{
    protected $bucketPath = "grammar-notes";
    
    public function index()
    {
        try {
            $files = Storage::disk('s3')->allFiles($this->bucketPath);
            
            $query = Words::select('grammatical_description','usmani_style','arabic_grammar')->where('grammatical_description','!=','')->groupBy('grammatical_description')->get()->toArray();
            $list = [];
            
            if (!empty($query)) {
                foreach ($query as $key => $val) {
                    $usmaniStyle = !empty($val['usmani_style']) ? Utility::generateS3BucketUrl($val['usmani_style'], $this->bucketPath, $files) : '';

                    $arabicGrammar = !empty($val['arabic_grammar']) ? Utility::generateS3BucketUrl($val['arabic_grammar'], $this->bucketPath, $files) : '';

                    $list[] = [
                        'grammaticalDescription' => $val['grammatical_description'],
                        'usmaniStyle' => $usmaniStyle,
                        'arabicGrammar' => $arabicGrammar,
                    ];
                }
            }
            $response = [
                'success' => true,
                'message' => 'Grammar Notes Data Retrieved Successfully',
                'totalRecords' => count($query),
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    
    public function updateGrammarNote(Request $request)
    {
        
        $user = Auth::user();
        $authId = $user->id;
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(),[
                'grammaticalDescription' => 'required',
                'arabicGrammar' => 'required|file|mimes:pdf|max:10000',
                'usmaniStyle' => 'nullable'
            ]);
            if($validator->fails()){
             return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
            };
            $input = $validator->valid();
            $oldRecord = Words::where('grammatical_description', $input['grammaticalDescription'])->get()->toArray();
            $oldRecord= reset($oldRecord);
            $oldFileName = $oldRecord['arabic_grammar'] ?? '';
            $input['arabicGrammar'] = Utility::uplaodFileToS3Bucket($request,$this->bucketPath, 'arabicGrammar', $oldFileName);
            
            Words::where('grammatical_description',$input['grammaticalDescription'])->update(['arabic_grammar'=>$input['arabicGrammar']]);
            $input['arabicGrammar'] = Utility::generateS3BucketUrl($input['arabicGrammar'], $this->bucketPath);
            DB::commit();
            $response = [
                'success' => true,
                'message' => 'Arabic Grammar Note Updated Successfully',
                'row' => $input
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

     public function updateUsmaniNote(Request $request)
    {
        $user = Auth::user();
        $authId = $user->id;

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(),[
                'grammaticalDescription' => 'required',
                'usmaniStyle' => 'required|file|mimes:pdf|max:10000',
                'arabicGrammar' => 'nullable'
            ]);
            if($validator->fails()){
             return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
            };
            $input = $validator->valid();

            $oldRecord = Words::where('grammatical_description', $input['grammaticalDescription'])->get()->toArray();
            $oldRecord= reset($oldRecord);
            $oldFileName = $oldRecord['usmani_style'] ?? '';

            $input['usmaniStyle'] = Utility::uplaodFileToS3Bucket($request,$this->bucketPath, 'usmaniStyle', $oldFileName);

            Words::where('grammatical_description',$input['grammaticalDescription'])->update(['usmani_style'=>$input['usmaniStyle']]);
            
            $input['usmaniStyle'] = Utility::generateS3BucketUrl($input['usmaniStyle'], $this->bucketPath);
            DB::commit();
            $response = [
                'success' => true,
                'message' => 'Usmani Style Note Updated Successfully',
                'row' => $input
            ];
        } catch (\Exception $e) {
            DB::rollback();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
}
