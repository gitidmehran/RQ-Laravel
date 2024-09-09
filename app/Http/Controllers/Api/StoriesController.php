<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryAyats;
use App\Utility\Utility;
use Illuminate\Http\Request;
use Validator, Auth;

class StoriesController extends Controller
{
    
    public function index()
    {
        try {
            $role = Auth::user()->role;
            $query = Story::with('user:id,name');
            if($role===3){
                $query = $query->where('scholar_id', Auth::id());
            }
            $stories = $query->get()->toArray();
            $list = [];
            foreach ($stories as $key => $value) {
                $list[] =[
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'status' => $value['status'],
                    'description' => $value['description'],
                    'scholar' => @$value['user']['name'] ?? '',
                    'total_counts' => $this->getSectionAndAyatsCount($value['sections']),
                ];
            }
            $response = [
                'success' => true,
                'message' => 'Stories Data Retrieved Successfully',
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'sections' => 'required',
            'description' => 'sometimes'
        ]);

        if($validator->fails()){
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        }
        $input = $validator->valid();

        try {
            $story = Story::create([
                'title' => $input['title'], 
                'description' => @$input['description'] ?? '',
                'sections' => json_encode($input['sections'])
            ]);
            
            $result = Story::with('user')->find($story['id'])->toArray();

            $row = [
                'id' => $result['id'],
                'title' => $result['title'],
                'status' => $result['status'],
                'description' => $result['description'],
                'scholar' => $result['user']['name'],
                'total_counts' => $this->getSectionAndAyatsCount($result['sections'])
            ];
            $response = [
                'success' => true,
                'message' => 'Stories Added Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function show(Request $request, $id){
        // 'sections' => json_decode($value['sections'], true)
        try {
            $result = Story::with('user')->find($id)->toArray();
            $row = [
                'id' => $result['id'],
                'title' => $result['title'],
                'description' => $result['description'],
                'sections' => json_decode($result['sections'], true)
            ];
            $response = [
                'success' => true,
                'message' => 'Story Data Retrieved Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'sections' => 'required',
            'description' => 'sometimes'
        ]);

        if($validator->fails()){
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        }
        $input = $validator->valid();
    
        try {
            $story = Story::find($id);
            $story->update([
                'title' => $input['title'], 
                'description' => @$input['description'] ?? '',
                'sections' => json_encode($input['sections'])
            ]);
           
            $result = Story::with('user')->find($id)->toArray();

            $row = [
                'id' => $result['id'],
                'title' => $result['title'],
                'description' => $result['description'],
                'scholar' => $result['user']['name'],
                'total_counts' => $this->getSectionAndAyatsCount($result['sections'])
            ];
            $response = [
                'success' => true,
                'message' => 'Stories Data Retrieved Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function destroy($id)
    {
        try {
            $story = Story::with('user')->find($id);
            $story->delete();

            $row = [
                'id' => $story['id'],
                'title' => $story['title'],
                'description' => $story['description'],
                'scholar' => $story['user']['name'],
                'total_counts' => $this->getSectionAndAyatsCount($story['sections'])
            ];
            $response = [
                'success' => true,
                'message' => 'Stories Deleted Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    private function getSectionAndAyatsCount($sections){
        $sections = json_decode($sections, true);
        $totalSections = count($sections);
        $totalAyats = 0;
        foreach ($sections as $key => $value) {
            if (isset($value['ayats']) && !empty($value['ayats'])) {
                $totalAyats = (int)$totalAyats + (int)count($value['ayats']);
            }
        }
        $text = $totalSections.'/'.$totalAyats;
        return $text;
    }

    public function updateStatus(Request $request, $id){
        try {
            $input = $request->all();
            Story::where('id', $id)->update(['status' => $input['status']]);
            $result = Story::with('user')->find($id)->toArray();
            $row = [
                'id' => $result['id'],
                'title' => $result['title'],
                'status' => $result['status'],
                'description' => $result['description'],
                'scholar' => $result['user']['name'],
                'total_counts' => $this->getSectionAndAyatsCount($result['sections']),
            ];
            $response = [
                'success' => true,
                'message' => 'Story Status Updated Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function getAllStories(Request $request){
        try {
            $query = Story::with('user');
            if($request->has('status') && $request->input('status')=="all"){
                $query = $query->where('scholar_id', Auth::id());
            }else{
                $query = $query->where('status', 'Published');
            }
            $result = $query->get()->toArray();
            $list = [];
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $list[] = [
                        'id' => $value['id'],
                        'title' => $value['title'],
                        'status' => $value['status'],
                        'scholar' => @$value['user']['name'] ?? '',
                        'total_counts' => 0
                    ];
                }
            }
            $response = [
                'success' => true,
                'message' => 'All Stories Data Retrieved Successfully',
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
}
