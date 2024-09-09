<?php

namespace App\Http\Controllers;

use App\Models\Notes;
use App\Models\User;
use App\Models\Teams;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;

class UsersController extends Controller
{
    protected $singular = "User";
    protected $plural   = "Users";
    protected $action   = "/dashboard/users";
    protected $view     = "users.";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'roles'    => \Config('constants.roles')
        ];
        $list = User::orderBy('id','desc')->get()->toArray();
        $data['list'] = $list;
        return view($this->view.'list',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'teams'    => Teams::all()->toArray(),
            'roles'    => \Config('constants.roles')
        ];
        return view($this->view.'create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validationMessages = [
            'team_id.required' => 'Team field is required',
            'is_approved.required' => 'Status field is required'
        ];
        if($request->input('role')==3){
            $rules = [
                'name' => 'required|string',
                'short_name'  => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required',
                'is_approved' => 'required',
                'team_id' => 'required'
            ];
        }else{
            $rules = [
                'name' => 'required|string',
                'short_name'  => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required',
            ];
        }

        $validator = Validator::make($request->all(),$rules, $validationMessages);

        if($validator->fails()){
         return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
       $input = $validator->valid();
       try {
            if(isset($input['password']) && !empty($input['password'])){
                $input['password'] = Hash::make($input['password']);
            }

            // CREATE TEAM IF ROLE IS SELECTED TO SELF
            if($input['role']==3 && $input['team_id']=="self"){
                $team = Teams::create(['name'=>$input['name'],'short_name'=>$input['short_name']]);
                $input['team_id'] = $team['id'];
                $input['is_self'] = 1;
            }
            User::create($input);
            $response = array('success'=>true,'message'=>$this->singular.' Added Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'roles'    => \Config('constants.roles'),
            'teams'    => Teams::all()->toArray(),
            'row'      => User::find($id)->toArray()
        ];
        return view($this->view.'edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $validationMessages = [
            'team_id.required' => 'Team field is required',
            'is_approved.required' => 'Status field is required'
        ];
        if($request->input('role')==3){
            $rules = [
                'name' => 'required|string',
                'short_name'  => 'required|string',
                'role' => 'required',
                'is_approved' => 'required',
                'team_id' => 'required'
            ];
        }else{
            $rules = [
                'name' => 'required|string',
                'short_name'  => 'required|string',
                'role' => 'required',
            ];
        }
        $validator = Validator::make($request->all(),$rules,$validationMessages);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        try {
            if(isset($input['password']) && !empty($input['password'])){
                $input['password'] = Hash::make($input['password']);
            }

            // CREATE TEAM IF ROLE IS SELECTED TO SELF
            if($input['role']==3 && $input['team_id']=="self"){
                $team = Teams::create(['name'=>$input['name'],'short_name'=>$input['short_name']]);
                $input['team_id'] = $team['id'];
                $input['is_self'] = 1;
            }
            
            $user->update($input);
            $response = array('success'=>true,'message'=> $this->singular.' Updated Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $note = User::find($id);
            $note->delete();
            $response = array('success'=>true,'message'=> $this->singular.' Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
}
