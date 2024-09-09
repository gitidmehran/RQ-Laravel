<?php

//namespace App\Http\Controllers;

namespace App\Http\Controllers;
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Notes;
use File,Auth,Validator;

class NotesController extends Controller
{
    protected $per_page = 10;
    protected $directory = "/public/Notes";
    public function __construct()
    {
    $this->per_page = Config('constants.perpage_showdata');
    }


    public function index(Request $request)
    {
        try{
            $query  = Notes::select('id','note_label','note_file');
            /*start*/
            if ($request->has('query') && !empty($request->input('query'))) {
                $search = $request->get('query');
                $query = $query->where('note_label', 'like', '%' . $search . '%');
            }
            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $current_page = $query->currentPage();
            $result = $query->toArray(); 
            /*$list = Notes::select('id','note_label','note_file')->get()->toArray();
            $data['list'] = $list;
            return view($this->view.'list',$data);*/
            $response = [
                        'success' => true,
                        'message' => 'Notes Data Retrieved Successfully',
                        'current_page' => $current_page,
                        'total_records' => $total,
                        'per_page'   => $this->per_page,
                       
                        'list' => $result['data']
                    ];
            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage());
            }
        return response()->json($response);
    
    }
    public function store(Request $request)
    {
       $validator = Validator::make($request->all(),[
            'note_label' => 'required',
            'note_file'  => 'required'
       ]);
       if($validator->fails()){
        return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
       };
       $input = $validator->valid();
       try {
            if($request->hasFile('note_file')){
                $file = $request->file('note_file');
                $fileName = $file->getClientOriginalName();
                $fileName = Auth::id().'-'.$fileName;
                $path = base_path().$this->directory;
                $file->move($path,$fileName);
                $input['note_file'] = $fileName;
            }
            $input['created_user'] = Auth::id();
            $note = Notes::create($input);
            // UNLINK ALL OLD FILES IF EXISTS
            // if(!empty($input['note_file'])){
            //     $path = base_path().$this->directory;
            //     $old_file = $path.'/'.$input['note_file'];
            // if(File::exists($old_file)) 
            // {
            //     File::delete($old_file);
            //     Notes::where('id', $note['id'])->delete();
            // }
            // $data = Notes::where('id', $note['id'])->first();
            // return response()->json($data->deleted_at);
            //    //if(File::exists($old_file ) && empty($data->deleted_at))
            //    if(File::exists($old_file ))
            //     {
            //         $response = [
            //             'success' => true,
            //             'message' => 'Notes Added Successfully'
            //             'row' => $row
            //         ];
            //     }
            //     else{
            //         Notes::where('id', $note['id'])->delete();
            //         $response = [
            //             'success' => false,
            //             'message' => 'Sorry File  Uploading Failed !'
            //         ];
            //     }

            // }
            $response = array('success'=>true,'message'=>'Notes Added Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);

    }
    public function edit($id)
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'row'      => Notes::find($id)->toArray()
        ];
        return view($this->view.'edit',$data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'note_label' => 'required',
            'note_file'  => 'sometimes|required'
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = Notes::find($id);
        try {
            if($request->hasFile('note_file')){
                $file = $request->file('note_file');
                $fileName = $file->getClientOriginalName();
                $path = base_path().$this->directory;
                $file->move($path,$fileName);
                $input['note_file'] = $fileName;
                $old_file = $path.'/'.$note->note_file;
                if(File::exists($old_file)) File::delete($old_file);
            }
            $note->update($input);
            $response = array('success'=>true,'message'=>'Notes Updated Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);
    }
    
    public function destroy($id)
    {
        try {
            $note = Notes::find($id);
            $note->delete();
            $response = array('success'=>true,'message'=>'Record Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
}
