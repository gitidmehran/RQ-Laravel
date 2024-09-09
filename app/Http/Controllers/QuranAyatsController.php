<?php

namespace App\Http\Controllers;
use App\Imports\InfoDataImport;
use App\Models\AyatsAbrahamicLocutionTranslation;
use App\Models\AyatsTranslation;
use App\Models\EnglishTranslations;
use App\Models\InfoData;
use App\Models\User;
use App\Models\UrduTranslations;
use Illuminate\Http\Request;

class QuranAyatsController extends Controller
{
  protected $per_page = 50;
  public function index()
  {
    return view('quranAyats.list');
  }

  public function search(Request $request){
    $filter = $request->post('query_data');
    $per_page = $request->has('perpage')?$request->post('perpage'):$this->per_page;
    $list = InfoData::where('arabic','like','%'.$filter.'%')->orWhere('arabic_simple','like','%'.$filter.'%')->paginate($per_page);
    return response()->json(['success'=>true,'list'=>$list]);
  }

  public function detailAyat(Request $request,$id){
    $user_session = $request->session()->has('user_session')?$request->session()->get('user_session'):[];
    $scholarIds = isset($user_session['scholars'])?$user_session['scholars']:[];
   
    $query = InfoData::with(['ayatsTranslations'=>function($q) use ($scholarIds){
       $q->ofSpecialScholars($scholarIds)->with(['scholarinfo:id,short_name','language:id,short_name']);
    }])->with(['words.translations'=>function($q) use($scholarIds){
       $q->ofSpecialScholars($scholarIds)->with(['scholar:id,short_name','language:id,short_name']);
    }]);

    $row = $query->where('id',$id)->get()->toArray();
    $row_data = $this->arrang_data($row);
   
    $related = [];
    if(!empty($row)){
      $relatedquery = InfoData::with(['ayatsTranslations'=>function($q) use ($scholarIds){
         $q->ofSpecialScholars($scholarIds)->with(['scholarinfo:id,short_name','language:id,short_name']);
      }])->with(['words.translations'=>function($q) use($scholarIds){
         $q->ofSpecialScholars($scholarIds)->with(['scholar:id,short_name','language:id,short_name']);
      }]);
      $related_result = $relatedquery->where('id','>',$id)->where('surahNo',$row[0]['surahNo'])->limit(49)->orderBy('ayatNo')->get()->toArray();
      $related = $this->arrang_data($related_result);
    }

    $data = [
      'data'=>$row_data,
      'related'=>$related,
      'search_action' => url('search-by-ayats'),
      'total_records' => count($related)+1
    ];
    return view('quranAyats.detail',$data);
  }

  private function arrang_data($records,$data=[]){
    if(!empty($records)){
      foreach ($records as $key => $value) {
        $data[$key] = [
          'id' => $value['id'],
         'surahNo' => $value['surahNo'],
         'ayatNo' => $value['ayatNo'],
         'arabic' => $value['arabic'],
        ];

        $translations = [];
        if(!empty($value['ayats_translations'])){
           foreach ($value['ayats_translations'] as $ikey => $val) {
              $translations[$ikey]['language'] = $val['language']['short_name'];
              $translations[$ikey]['scholar']  = $val['scholarinfo']['short_name'];
              $translations[$ikey]['translation'] = $val['translation'];
           }
        }
        $data[$key]['ayats_translations'] = $translations;
        $data[$key]['words'] = $value['words'];
      }  
    }
    return $data;
  }

  public function searchAyats(Request $request){
    $surah = $request->input('surah');
    $ayats = InfoData::where('surahNo',$surah)->select('id','ayatNo')->orderBy('ayatNo')->groupBy('ayatNo')->get()->toArray();
    return response()->json(['success'=>true,'ayats'=>$ayats]);
  }

  public function filterByVerses(Request $request){

    $input = $request->all();
    $user_session = $request->session()->has('user_session')?$request->session()->get('user_session'):[];
    $scholarIds = isset($user_session['scholars'])?$user_session['scholars']:[];
    
    $query = InfoData::with(['ayatsTranslations'=>function($q) use ($scholarIds){
       $q->ofSpecialScholars($scholarIds)->with(['scholarinfo:id,short_name','language:id,short_name']);
    }])->with(['words.translations'=>function($q) use($scholarIds){
       $q->ofSpecialScholars($scholarIds)->with(['scholar:id,short_name','language:id,short_name']);
    }]);
          // echo '<pre>';print_r($input);die;
    $range = [$input['from_verse'],$input['to_verse']];

    $this->per_page = $request->has('per_page')?$request->get('per_page'):$this->per_page;
    $list = $query->whereBetween('id',$range)->paginate($this->per_page);
    $count = $list->total();
    $links = $list->appends($request->all())->links('pagination::bootstrap-5');
    $list = $list->toArray();
    $ayats = InfoData::select('id','ayatNo')->where('surahNo',$input['surah'])->get()->toArray();
    $records = $this->arrang_data($list['data']);
    $data = [
      'data'=>$records,
      'filter' => $input,
      'share_ayats' => $ayats,
      'links' => $links,
      'total_records' => $count,
    ];

    return view('ayatview.ayatview',$data);
  }
}
