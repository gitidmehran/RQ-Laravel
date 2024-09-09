<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notes;
use File,Auth,Validator;

class NotesController extends Controller
{
    protected $singular = "Note";
    protected $plural   = "Notes";
    protected $action   = "/dashboard/rq-notes";
    protected $view     = "notes.";
    protected $directory = "/public/notes";

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
            'action'   => $this->action
        ];
        $list = Notes::select('id','note_label','note_file')->get()->toArray();
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
            'action'   => $this->action
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
                $path = base_path().$this->directory;
                $file->move($path,$fileName);
                $input['note_file'] = $fileName;
            }
            $input['created_user'] = Auth::id();
            Notes::create($input);
            $response = array('success'=>true,'message'=>'Notes Added Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
            'row'      => Notes::find($id)->toArray()
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
