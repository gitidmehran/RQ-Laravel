<?php

//namespace App\Http\Controllers;
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\Languages;
use App\Models\Notes;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\Words;
use App\Models\WordsTranslations;
use App\Models\QuranSurahs;
use App\Models\RootWordMeaning;
use Illuminate\Http\Request;
use App\Models\WordMeaning;
use Auth,Validator,Hash,DB;



class RootwordsController extends Controller
{
    protected $per_page = '50';

    public function index(Request $request)
    {
        try{
            $query = RootWordMeaning::select('id','root_word','english_root_word','meaning_urdu','meaning_eng');
            if($request->has('query') && $request->get('query')){
                $queryword = $request->get('query');
                $wordsArray = \Config('constants.word_characters');
                $word = $wordsArray[$queryword];
                $query->where('root_word', 'LIKE', '' . $word . '%');
            }
            $query = $query->paginate($this->per_page);
            $total = $query->total();
            //$current_page = $query->currentPage();
            $list = $query->toArray(); 
            //$list = [];
            //$list = $query->get()->toArray();
            $response = array('success'=>true,
                'message'=>'Meaning Data Retrieved Successfully',
                //'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'list'=>$list);
        }catch(\Exception $e){
            $response = ['success'=>false,'message'=>$e->getMessage()];
        }
        return response()->json($response);
    }

    public function show(Request $request)
    {
    try{
        $id = $request->params;
        $root_word_meanings = DB::table('root_word_meanings')->where('id', $id)->get();
        $data = [
            'title'         => 'RootWord References',
            'root_word_meanings' => $root_word_meanings
        ];
    }catch(\Exception $e){
        $response = ['success'=>false,'message'=>$e->getMessage()];
    }
        return response()->json($data);
        // return view('rootWords.rootword', $data);
    }

    public function store(Request $request)
    {
         $id = $request->id;
         $validator = Validator::make($request->all(),[
            'meaning_eng' => 'nullable|string',
            'meaning_urdu'  => 'nullable|string'            
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        try{
            $data = [
                    'meaning_urdu' => $input['meaning_urdu'],
                    'meaning_eng' => $input['meaning_eng']
            ];
            RootWordMeaning::where('id',$id)->update($data);
            $response = array('success' => true, 'message' => 'Data Updated Successfully');
        }catch(\Exception $e){
            $response = ['success'=>false,'message'=>$e->getMessage()];
        }

        return response()->json($response);
        //return redirect()->back();
    }

    public function store_root_word(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'rootwordmeaningeng' => 'nullable|string',
            'rootwordmeaningurdu'  => 'nullable|string'            
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();

        DB::beginTransaction();
        try {
            // $meaningurdu = $request->rootwordmeaningurdu;
            // $meaningeng = $request->rootwordmeaningeng;
           
            $id = $request->rootword_id;

            $data = [
                    'meaning_urdu' => $input['rootwordmeaningurdu'],
                    'meaning_eng' => $input['rootwordmeaningeng']
            ];
            $abc = new Request;
            RootWordMeaning::where('id',$id)->update($data);
            //$showAlldata = $this->index($abc);//RootWordMeaning::select('id','root_word','english_root_word','meaning_urdu','meaning_eng')->get()->toArray();
            /*$abc = RootWordMeaning::where('id',$id)->first();
            $abc->update($data);//
            $abc = $abc->refresh();*/
            //DB::update('update root_word_meanings set meaning_urdu = ?,meaning_eng = ? where id = ?', [$meaningurdu, $meaningeng, $id]);

            $query = RootWordMeaning::select('id','root_word','english_root_word','meaning_urdu','meaning_eng');
            if($request->has('query') && $request->get('query')){
                $queryword = $request->get('query');
                $wordsArray = \Config('constants.word_characters');
                $word = $wordsArray[$queryword];
                $query->where('root_word', 'LIKE', '' . $word . '%');
            }
            $query = $query->paginate($this->per_page);
            $total = $query->total();
            //$current_page = $query->currentPage();
            $list = $query->toArray(); 
            //$list = [];
            //$list = $query->get()->toArray();
            

            DB::commit();
            $response = array('success'=>true,
                'message'=>'RootWordMeaning Data Updated Successfully',
                //'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'list'=>$list);
            /*$response = array('success' => true, 'message' => 'RootWordMeaning Data Updated Successfully', $response);*/
       } catch (\Exception $e) {
            DB::rollback();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

}
