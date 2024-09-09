<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utility\Utility;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request){
        try {
            $result = User::where(['role'=>3])->get()->toArray();
            $impersonation=$publishScholarPermissions=$nonPublishScholarPermissions=$userPermissions=[];
            
            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $id = $value['id'];
                    $permissions = !empty($value['user_permissions'])?json_decode($value['user_permissions'], true):[];

                    $allowedScholar = json_decode( $value['user_settings'], true );
                    $viewPermissions = !empty($allowedScholar['allowedScholar'])?$allowedScholar['allowedScholar']:[];

                    // Non Publish Scholar Permissions
                    if($value['is_approved']==2){

                        $nonPublishScholarPermissions[] =[
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'scholars' => $this->getScholarsData($id, $viewPermissions, $result)
                        ];
                    }elseif($value['is_approved']!==3) {

                        $impersonation[] =[
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'scholars' => $this->getScholarsData($id,$permissions,$result)
                        ];

                        $publishScholarPermissions[] =[
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'scholars' => $this->getScholarsData($id, $viewPermissions, $result)
                        ];
                    }                }

            }


            $basicUser = User::where(['role'=>4])->first();
            if(!empty($basicUser)) {
                $basicUser = $basicUser->toArray();
                $allowedScholar = json_decode( $basicUser['user_settings'], true );
                $viewPermissions = !empty($allowedScholar['allowedScholar'])?$allowedScholar['allowedScholar']:[];
                
                $userPermissions[] = [
                    'id' => 100,
                    'name' => 'Basic User',
                    'scholars' => $this->getScholarsData('',$viewPermissions,$result)
                ];
            }

            $list = [
                'user_permissions' => $userPermissions,
                'impersonation' => $impersonation,
                'publish_scholar_permissions' => $publishScholarPermissions,
                'nonpublish_scholar_permissions' => $nonPublishScholarPermissions
            ];
            
            $response = [
                'success' => true,
                'message' => 'Scholar Permission Data Retrieved Successfully',
                'list' => $list
            ];
            $response = Utility::convertKeysToCamelCase($response);
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=> $e->getMessage());
        }      
        return response()->json($response);
    }

    public function store(Request $request){
        try {
            $input = $request->get('request');
            $type = $request->get('type') ?? '';

            if( $type == 'basic_permission' ) {
                $input = reset($input);
                $scholars = array_filter($input['scholars'], function($item){
                    return ($item['checked']==true);
                });
                $scholarIds = !empty($scholars)?array_column($scholars,'id'):[];

                $users = User::where('role', 4)->get()->toArray();//->update(['user_settings' => json_encode($allowedUser)]);
                foreach ($users as $key => $user) {
                    $set = $user['user_settings'];
                    $set = json_decode($set, true);
                    $set['allowedScholar'] = $scholarIds ?? [];
                    User::where(['role'=> 4, 'id' => $user['id']])->update(['user_settings' => $set]);
                }
            } elseif(!empty($input)) {
                foreach ($input as $key => $value) {
                    $id = $value['id'];
                    $scholars = array_filter($value['scholars'], function($item){
                        return ($item['checked']==true);
                    });
                    $scholarIds = !empty($scholars)?array_column($scholars,'id'):[];

                    if( !empty( $type ) && $type == 'scholar_permission' ) {
                        $user = User::where('id', $id)->get()->toArray();
                        $data = json_decode( $user[0]['user_settings'], true ) ?? [];
                        $inputData = [
                            'allowedScholar' => $scholarIds,
                        ];
                        
                        $data = array_merge($data, $inputData);

                        User::where('id', $id)->update(['user_settings'=> json_encode($data)]);
                    } else {
                        // $scholarIds = !empty($scholars)?array_column($scholars,'id'):[];
                        if(!empty($scholarIds)){
                            $scholarIds = json_encode($scholarIds);
                            User::where('id',$id)->update(['user_permissions'=>$scholarIds]);
                        }
                    }

                }
            }
            $response = array('success'=> true, 'message'=> 'User Permissions Saved Successfully');
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=> $e->getMessage());   
        }
        return response()->json($response);
    }

    private function getScholarsData($id,$permissions,$list){
        $list = array_filter($list, function($item) use($id) {
            return ($item['id'] !==$id);
        });

        $result = [];
        if(!empty($list)){
            foreach ($list as $key => $value) {
                $result[] = [
                    'id' => $value['id'],
                    'short_name' => $value['short_name'],
                    'checked' => in_array($value['id'],$permissions)
                ];
            }
        }
        return $result;
    }
}
