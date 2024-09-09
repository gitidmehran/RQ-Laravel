<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Helps;
use Illuminate\Http\Request;
use Validator;
use App\Utility\Utility;

class HelpsController extends Controller
{
    protected $bucketPath = "help-notes";

    public function index() {
        $result = Helps::get()->toArray();
      
        $list = [];
        $url = env('AWS_URL')."/".$this->bucketPath."/";
        if(!empty($result))
        {
            foreach($result as $key => $value ){
                $list[] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'link' => $value['link'],
                    'file' => $value['file'],
                    'url' => $url
                ];
            }
        }
        $response = [
            'success' => true, 
            'message' => 'Record Retrieved Successfully',
            'row' => $list
        ];
        return response()->json($response);

    }

    public function store(Request $request) {
        
        $rules = [
            'name' => 'required|string',
        ];
        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $validator->valid();
        $input['file'] = Utility::uplaodFileToS3Bucket($request,$this->bucketPath, 'file', "");
        $url = env('AWS_URL');
        
        $fileUrl = $url."/".$this->bucketPath."/";
        try {
            $data = [
                'name' => $input['name'],
                'link' => $input['link'] ?? '',
                'file' => $input['file'] ?? '',
            ];
            $help = Helps::create($data);
            $rec = [
                'id' => $help['id'],
                'name' => $help['name'],
                'link' => $help['link'],
                'file' => $help['file'],
                'url' => $fileUrl
            ];
            $response = [
                'success' => true, 
                'message' => 'Record Added Successfully',
                'row' => $rec,
                
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function delete($id)
    {
        try {
            $help = Helps::find($id);
            $help->delete();
            $deleteFile = Utility::deleteFileFromS3Bucket($help['file'],$this->bucketPath);
        
            $row = [
                'id' => $help['id'],
                'name' => $help['name'],
                'link' => $help['link'],
                'file' => $help['file'],
            ];

            $response = [
                'success' => true,
                'message' => 'Record Deleted Successfully!',
                //'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string',
        ];
        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {
            $data = [
                'name' => $input['name'],
            ];

            Helps::where('id',$id)->update($data);
            $user = Helps::find($id)->toArray();
            $row = [
                'id' => $user['id'],
                'name' => $user['name'],
                'link' => $user['link'],
                'file' => $user['file'],
            ];

            $response = [
                'success' => true,
                'message' => 'Record Deleted Successfully!',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
}
