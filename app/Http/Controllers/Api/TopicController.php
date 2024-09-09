<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utility\Utility;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function publishedTopics(Request $request){
        try {
            
            $response = [
                'success' => true,
                'message' => 'Published Topics Retrieved Successfully',
                'list' => []
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
}
