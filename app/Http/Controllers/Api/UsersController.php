<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Notes;
use App\Models\Teams;
use App\Models\User;
use App\Utility\Utility;
use Auth,Validator,Hash,DB;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    protected $singular = "User";
    protected $plural   = "Users";
    protected $action   = "/dashboard/users";
    protected $view     = "users.";
    protected $per_page = 10;
    protected $roles = [];

    public function __construct()
    {
        $this->roles = \Config('constants.roles');
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $role = $user->role;

            $type = @$request->get('type') ?? 'Published Scholars';

            $query = User::select('id','name','short_name','email','role','is_approved', 'translated_language')->orderBy('id','desc');

            switch ($type) {
                case 'Non-Published Scholars':
                    $query = $query->where(['role' => 3, 'is_approved' => 2]);
                    break;

                case 'Basic Users':
                    $query = $query->where('role', 4);
                    break;

                case 'Administrator':
                    $query = $query->whereIn('role', [1,2]);
                    break;
                
                case 'Passive Scholars':
                    $query = $query->where(['role' => 3, 'is_approved' => 3]);
                    break;
                
                default:
                    $query = $query->where(['role' => 3, 'is_approved' => 1]);
                    break;
            }

            // IF AUTHENTICATED USER IS NOT ADMIN THEN RETURN ONLY ALLOWED SCHOLARS DATA
            if($role != 1){
                $allowedScholars = !empty($user['user_permissions'])?json_decode($user['user_permissions'], true):[];
                $query = $query->whereIn('id', $allowedScholars);
            }

            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $result = $query->toArray();
            
            $list = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $value) {
                    $list[] = [
                        'id' => $value['id'],
                        'name' => $value['name'],
                        'short_name' => $value['short_name'],
                        'email' => $value['email'],
                        'role' => $value['role'],
                        'role_text' => $this->roles[$value['role']],
                        'is_approved' => $value['is_approved'],
                        'translated_languages' => !empty($value['translated_language']) ? explode(',',$value['translated_language']):[]
                    ];
                }
            }

            // SCHOLARS PERMISSIONS
            if($role==1){
                $scholars = array_filter($list, function($item){
                    return ($item['role']==3);
                });
                $allowedScholars = !empty($scholars)?array_column($scholars,'id'):[];
            }
            $roleTypes = ["Published Scholars", "Passive Scholars", "Non-Published Scholars", "Basic Users", "Administrator"];
            if($role > 2){
                $roleTypes = ["Published Scholars"];
            }
            //return response()->json($this->roles);
            $response = [
                'success' => true,
                'message' => 'User Data Retrieved Successfully',
                'total_records' => $total,
                'allowedScholars' => $allowedScholars,
                'roles'    => $this->roles,
                'role_types' => $roleTypes,
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
        
        if($request->input('role')==3){
            $rules = [
                'name' => 'required|string',
                'shortName'  => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required',
                'isApproved' => 'required',
                'translatedLanguages' => 'required'
            ];
        }else{
            $rules = [
                'name' => 'required|string',
                'shortName'  => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required',
            ];
        }

        $validator = Validator::make($request->all(),$rules, ['isApproved.required' => 'Status field is required']);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {
            if(isset($input['password']) && !empty($input['password'])){
                $input['password'] = Hash::make($input['password']);
            }
            $data = [
                'name' => $input['name'],
                'short_name' => $input['shortName'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => $input['role'],
                'is_approved' => @$input['isApproved'] ?? '',
                'translated_language' => isset($input['translatedLanguages']) ? implode(',', $input['translatedLanguages']) : ''
            ];
            $user = User::create($data);
            $row = [
                'id' => $user['id'],
                'name' => $user['name'],
                'short_name' => $user['short_name'],
                'email' => $user['email'],
                'role' => (int)$user['role'],
                'role_text' => $this->roles[$user['role']],
                'is_approved' => (int)$user['is_approved'],
                'translated_languages' => !empty($user['translated_language']) ? explode(',',$user['translated_language']):[]
            ];
            $response = [
                'success' => true, 
                'message' => $this->singular.' Added Successfully',
                'row' => $row
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
            'shortName'  => 'required|string',
            'email' => 'required',
            'role' => 'required',
        ];
        if($request->input('role')==3){
            $rules['isApproved'] = 'required';
            $rules['translatedLanguages'] = 'required';
        }

        $validator = Validator::make($request->all(),$rules,['is_approved.required' => 'Status field is required']);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {

            $data = [
                'name' => $input['name'],
                'short_name' => $input['shortName'],
                'email' => $input['email'],
                'role' => $input['role'],
                'is_approved' => @$input['isApproved'] ?? '',
                'translated_language' => isset($input['translatedLanguages']) ? implode(',', $input['translatedLanguages']) : ''
            ];
            if(isset($input['password']) && !empty($input['password'])){
                $data['password'] = Hash::make($input['password']);
            }

            
            User::where('id',$id)->update($data);
            $user = User::find($id)->toArray();
            $row = [
                'id' => $user['id'],
                'name' => $user['name'],
                'short_name' => $user['short_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'role_text' => $this->roles[$user['role']],
                'is_approved' => $user['is_approved'],
                'translated_languages' => !empty($user['translated_language']) ? explode(',',$user['translated_language']):[]
            ];

            $response = [
                'success' => true,
                'message' => $this->singular.' Updated Successfully',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

   
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();

            $row = [
                'id' => $user['id'],
                'name' => $user['name'],
                'short_name' => $user['short_name'],
                'role' => $user['role'],
                'role_text' => $this->roles[$user['role']],
                'is_approved' => $user['is_approved'],
                'translated_languages' => !empty($user['translated_language']) ? explode(',',$user['translated_language']):[]
            ];

            $response = [
                'success' => true,
                'message' => $this->singular.' Deleted!',
                'row' => $row
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function getAllScholars(Request $request)
    {
        try {
            $user = Auth::user();

            $query = User::select('id','name','short_name')->where('role',3)->where('is_approved',1);
            if($request->has('both') && !empty($request->input('both')))
                $query = $query->where('is_approved', 0);

            if($user->role != 1 && $user->role !=4){
                $userPermissions = !empty($user['user_permissions'])?json_decode($user['user_permissions'], true):[];
                $query = $query->whereIn('id', $userPermissions)->orderBy('id','desc');
            }

            if($user->role==4){
                $settings = !empty($user['user_settings']) ? json_decode($user['user_settings'], true):[];
                $query = $query->whereIn('id', $settings['allowedScholar'])->orderBy('name','asc');
            }

            $result =  $query->get()->toArray();
            $response = [
                'success' => true,
                'message' => 'Scholar Data Retrieved Successfully',
                'list' => Utility::convertKeysToCamelCase($result)
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message'=> $e->getMessage());
        }
        return response()->json($response);
    }
}
