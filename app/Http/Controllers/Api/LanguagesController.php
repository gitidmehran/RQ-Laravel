<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Utility\Utility;
use Illuminate\Http\Request;
use Validator;

class LanguagesController extends Controller
{
    
    public function index()
    {
        try {
            $list = Languages::get()->toArray();
            $response = [
                'success' => true,
                'message' => 'Languages Data Retrieved Successfully',
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response =  array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'shortName'    => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };   
        $input = $validator->valid();

        try {
            
            $isAlreadyAdded = Languages::where('name', $input['name'])->count();
            if(!empty($isAlreadyAdded)){
                return response()->json(['success' => false, 'message' => "Language already added, You Can't add Duplicate"], 422);
            }

            $language = Languages::create([
                'name' => $input['name'],
                'short_name' => $input['shortName']
            ]);
            $row = [
                'id' => $language['id'],
                'name' => $language['name'],
                'short_name' => $language['short_name'],
            ];
            $response = [
                'success' => true,
                'message' => 'Language Added Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response =  array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'shortName'    => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };   
        $input = $validator->valid();

        try {
            Languages::where('id', $id)->update([
                'name' => $input['name'],
                'short_name' => $input['shortName']
            ]);
            $row = [
                'id' => (int)$id,
                'name' => $input['name'],
                'short_name' => $input['shortName']
            ];
            $response = [
                'success' => true,
                'message' => 'Language Updated Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response =  array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function destroy($id)
    {
        try {
            $language = Languages::find($id);
            $language->delete();
            $row = [
                'id' => $language['id'],
                'name' => $language['name'],
                'short_name' => $language['short_name']
            ];
            $response = [
                'success' => true,
                'message' => 'Language Data Deleted Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response =  array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
}
