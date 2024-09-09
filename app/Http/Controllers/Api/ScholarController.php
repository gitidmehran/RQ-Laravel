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
use Illuminate\Http\Request;
use Auth,DB;

class ScholarController extends Controller
{
   
   protected $per_page = 2;
   protected $view = 'ayatview.';
   protected $defualtSelection = ['word','grammatical_description'];


   public function viewTranslation(Request $request)
   {
      try{
      $input = $request->all();
      $scholars = User::where('role',3)->get()->toArray();
      $user = Auth::user();
      $user_settings = !empty($user->user_settings)?json_decode($user->user_settings,true):[];
      $ayat_scholars_settings = $user_settings['ayat_scholars_settings'] ?? [];
      //$scholarid = $input['scholar_id'] ?? $ayat_scholars_settings;

      // Multi scholar function

      $scholarid = (isset($input['scholar_id']) && !empty($input['scholar_id']))? explode(',', $input['scholar_id']): $ayat_scholars_settings;
      
   // Multi scholar function

      $query = InfoData::with(['ayatsTranslations'=>fn($q)=> $q->OfNotEmpty()->OfSpecialScholars($scholarid)->with('scholarinfo:id,name,short_name','language')]);
     //maybe change surahNo 
      if(isset($input['surah_id']) && !empty($input['surah_id']))
         $query->where('surah_id',$input['surah_id']);

      if(isset($input['per_page']) && !empty($input['per_page']))
         $this->per_page = $input['per_page'];

      if(!empty($scholarid)){
         $translations = AyatsTranslation::where('translation','!=','');
         if(is_array($scholarid))
            $translations->whereIn('scholar_id',$scholarid);
         else
            $translations->where('scholar_id',$scholarid);

         $translations = $translations->select('ayat_id','scholar_id')->get()->toArray();
         $ayatIds = array_unique(array_column($translations,'ayat_id'));
         $query->whereIntegerInRaw('id',$ayatIds);
      }
      $list = $query->orderBy('surah_id')->orderBy('ayatNo')->paginate($this->per_page);
      $count = $list->total();
      $links = $list->appends($request->all())->links('pagination::bootstrap-5');
      $list = $list->toArray();
      $list = $list['data'];      
      if(!empty($list)){
         foreach ($list as $key => $value) {            
            usort($value['ayats_translations'], function ($item1, $item2) {
                return $item2['language'] <=> $item1['language'];
                //return $item2['language']['id'] <=> $item1['language']['id']; 28-12-22
            });
            $list[$key]['ayats_translations'] = $value['ayats_translations'];
         }
      }
      $surahs = QuranSurahs::select('id','arabic','latin','english','localtion','ayah')->get();
      $data = [
         'title' => 'Single Scholar Translation',
         'list' => $list,
         'links' => $links,
         
      ];
      $response = [
         'success' => true,
         'message' => 'Ayat Only Translations Data Retrieved Successfully',
         'total_records' => $count,
         'scholars' => $scholars,
         'filters' => $input,
         'list' => $list
         /*'current_page' => $current_page,
         'total_records' => $total,
         'per_page'   => $this->per_page,*/
         //'roles_data' => $roles,
         //'data' => $data
      ];
      } catch (\Exception $e) {
         $response = array('success'=>false,'message'=>$e->getMessage());
      }
        return response()->json($response);
      //return view($this->view.'ayat-only-translations',$data);
   }

   private function arrangeTranslationData(&$data,$list,$user_settings){
      //echo '<pre>';print_r($list['data']);die;
      $records = [];
      
      if(!empty($list['data'])){
         foreach ($list['data'] as $key => $value) {
            $records[$key] = [
               'id' => $value['id'],
               'suraId' => $value['surah_id'],
               'ayatId' => $value['ayatNo'] ,
               'arabic' => $value['arabic'],
            ];
            $translations = [];
            if(!empty($value['ayats_translations'])){
               foreach ($value['ayats_translations'] as $ikey => $val) {
                  $translations[$ikey]['languageId'] = @$val['language']['id'];
                  $translations[$ikey]['language'] = @$val['language']['short_name'];                  
                  $translations[$ikey]['scholarId']  = $val['scholarinfo']['id'];
                  $translations[$ikey]['scholarName']  = $val['scholarinfo']['short_name'];
                  $translations[$ikey]['translation'] = $val['translation'];
               }
            }
            usort($translations, function ($item1, $item2) {
                return $item2['languageId'] <=> $item1['languageId'];
            });
            
            $records[$key]['ayatTranslations'] = $translations;
            $words = $this->formatWordsArray($value['words'],$user_settings);
            // dd($words);die;
            $records[$key]['words'] = $words;
         }
      }
      $word_keys = $this->getWordKeys($records);
      
      $words_headings = $this->getWordHeadings($word_keys ?? $this->defualtSelection);
      // echo '<pre>';print_r($words_headings);die;
      if(isset($words_headings['meaning_urdu'])) unset($words_headings['meaning_urdu']);
      if(isset($words_headings['meaning_eng'])) unset($words_headings['meaning_eng']);
      $data['data'] = $records;
      $data['words_headings'] = $words_headings;
      // echo '<pre>';print_r($data);die;
      return $data;
   }

   private function formatOtherUserData(&$data,$user_settings,$request){
      $share_ayats = [];
      if($request->has('surah_id') && !empty($request->input('surah_id'))){
         $share_ayats = InfoData::where('surah_id',$request->get('surah'))->select('id','ayatNo')->groupBy('ayatNo')->orderBy('ayatNo')->get()->toArray();
      }

      // SELECT SCHOLARS DATA
      $query = User::where('role',3);
      if(!empty($user_settings['scholars_setting']))
         $query->whereIn('id',$user_settings['scholars_setting']);

      $scholars = $query->select('id','short_name')->get()->toArray();
      
      // SELECT LANGUAGES
      $language_query = new Languages();
      if(!empty($user_settings['languages_settings']))
         $language_query->whereIn('id',$user_settings['languages_settings']);
      $languages = $language_query->select('id','short_name')->get()->toArray();
      $listdata = reset($data['data']);
      $listdata = !empty($listdata)?$listdata['words']:[];
      $listdata = !empty($listdata)?$listdata[0]['translations']:[];
      // echo '<pre>';dd($listdata);die;
      $language_headings = [];
      if(!empty($listdata)){
          foreach ($listdata as $value) {
            $language_headings[] = [
               'language_id' => $value['language_id'],
               'scholar_id'  => $value['scholar_id'],
               'label'       => $value['scholar_name'].'-'.$value['language_name']
            ];
         }
         usort($language_headings, function ($item1, $item2) {
             return $item2['language_id'] <=> $item1['language_id'];
         });    
      }
      // echo '<pre>';dd($language_headings);die;

      $data['share_ayats'] = $share_ayats;
      $data['language_headings'] = $language_headings;
      return $data;
   }

   private function formatWordsArray($data,$settings){
      $user = Auth::user();
      $role = $user->role;
      // $user = User::where('id',21)->first()->toArray();
      // echo '<pre>';print_r($user);die;
      // $role = $user['role'];
      $list = [];
      $word_selection = $settings['words_settings'] ?? $this->defualtSelection;
      foreach ($data as $key => $value) {
          //echo 'value data is <pre>';print_r($value);die;
         $keyvalues = $translations = [];
         $keyvalues['word_id'] = $value['id'];
         $keyvalues['role'] = $role;
         $keyvalues['root_word_id'] = $value['root_word_id'];
         $keyvalues['eng_root_word'] = isset( $value['root_word_meaning']) ? $value['root_word_meaning']['english_root_word'] : '';
         $keyvalues['surah_id'] = $value['surah_no'];
         $keyvalues['ayat_no'] = $value['ayat_no'];
         $keyvalues['reference'] = $value['reference'];
         $heading_class = '';

         // ---------Change in this loop---------------
         // -------------------------------------------
         // ---------Show Root Word Meaning Rows-------

         // GET WORDS COLUMN ACCORDING TO SELECTION
         foreach ($word_selection as $val) {
            
            if ( $val == 'meaning_urdu' || $val == 'meaning_eng' ) {
               $keyvalues[$val] = isset( $value['root_word_meaning']) ? $value['root_word_meaning'][$val] : '';
            } else {
               $keyvalues[$val] = $value[$val];
            }

         }
         // -----------------END ----------------------

         if(!empty($settings['word_translation_settings'])){
            foreach ($settings['word_translation_settings'] as $val) {
               $keyvalues[$val] = @$value['other_word_info'][$val];
            }   
         }

         $word_references= $value['word_references'] ?? [];
         // die;
         $keyvalues['reference_type_number'] = @$value['other_word_info']['reference_type_number'];
         $reference_type = $value['other_word_info']['reference_type'] ?? '';

         if(in_array($reference_type,['by_reference','by_number'])){
            $heading_class = $heading_class.' highlight-word-heading';
         }
         $text = '';
         if(@$value['other_word_info']['reference_type']=="by_reference"){
            $reference_word_ids = array_unique(array_column($word_references,'word_id'));
            $text = in_array($value['id'],$reference_word_ids)?'Source':'Referred';
         }else{
            $constants = \Config('constants.references');
            $text = $constants[$reference_type] ?? '';
         }

         if(isset($settings['word_translation_settings']['reference_type']) && !empty($settings['word_translation_settings']['reference_type'])){
            $keyvalues['reference_type'] = $text;
         }

         if(!empty($value['single_reference_word'])){
            $heading_class = $heading_class.' highlight-word-heading';
         }
         
         $formattedRecords = $this->formateWordTranslation($value);
         $translations = $formattedRecords['translations'];

         if(!empty($formattedRecords['reference_type'])){
            $keyvalues['reference_type'] = $formattedRecords['reference_type'];
            if(empty($heading_class)){
               $heading_class = $heading_class.' highlight-word-heading';   
            }
            
         }
         $newworddata= [];
         
         $keyvalues['heading_class'] = $heading_class;
         $list[$key] = $keyvalues;
         $list[$key]['phrases_words'] = $value['phrases_words'] ?? [];
         // IF TRANSLATIONS ARE NOT EMPTY THEN FORMATE ACCORDING TO LANGUAGE
         if(!empty($translations)){
            usort($translations, function ($item1, $item2) {
                return @$item2['language_id'] <=> @$item1['language_id'];
            });
            $translations = array_unique($translations,SORT_REGULAR);
            $translations = array_values($translations);

            $list[$key]['translations'] = $translations;
         }
      }
      $idsNeedsToRemove = [];
      // echo "<pre>";print_r($list);die;
      // FORMATING ARRAY AGAINST PHRASES
      foreach ($list as $key => $value) {
         if(!empty($value['phrases_words'])){
            $phrase = $value['phrases_words'];
            $phrases_words_ids = array_column($phrase,'phrase_word_id');
            $newwords = array_filter($list, fn($item)=> in_array($item['word_id'],$phrases_words_ids));
            $words_name = implode(' ', array_column($newwords,'word'));
            $root_words_name = implode(' ',array_column($newwords,'root_word'));      
            foreach (@$phrase as  $ival) {
               $idsNeedsToRemove[] = $ival['phrase_word_id'];
            }
            $value['word'] = $value['word'].' '.$words_name;
            $value['root_word'] = !empty($value['root_word']) ? $value['root_word'].' '.$root_words_name:$root_words_name;
            unset($value['phrases_words']);
            $list[$key] = $value;
         }else{
            unset($value['phrases_words']);
            $list[$key] = $value;
         }
      }
      $finaldata = array_filter($list,fn($item) => (!in_array($item['word_id'], $idsNeedsToRemove)));
      // echo '<pre>';print_r($finaldata);die;
      return $finaldata;
   }

   private function formateWordTranslation($value){
      $translations = [];
      $translationArray = $value['translations'] ?? [];

      $word_references = $value['word_references'] ?? [];

      $phrases_words =  $value['phrases_words'] ?? [];
      $reference_type = '';
      if(!empty($translationArray)){
         foreach ($translationArray as $val) {
            $class = $val['language']['id']==1 ? 'urdu-word-font':'arabic-word-font';
            if($val['is_reference_word']==1 && empty($reference_type)){
               $class = $class.' highlight-word';
               $reference_type = 'Referred';
            }
            if(!empty($word_references)){
               $references_scholar_ids = array_unique(array_column($word_references,'scholar_id'));
               if(in_array($val['scholar_id'],$references_scholar_ids)){
                  $class = $class.' highlight-word';
               }
            }
            
            if(!empty($phrases_words)){
               
               $phrases_scholar_ids = array_unique(array_column($phrases_words,'scholar_id'));
               if(in_array($val['scholar_id'],$phrases_scholar_ids) && !strpos($class, 'highlight-word')){
                  $class = $class.' highlight-word';
               }
               
            }

            $translations[]= [
               'language_id'   => $val['language']['id'],
               'language_name' => $val['language']['short_name'],
               'scholar_id'    => @$val['scholar_id'],
               'scholar_name'  => @$val['scholar']['short_name'],
               'translation'   => $val['translation']??'-',
               'class' => $class
            ];
         }
      }
      return [
         'translations' => $translations,
         'reference_type' => $reference_type
      ];
   }

   private function getWordKeys($records)
   {
      if(!empty($records[0]['words'])){
         return array_keys($records[0]['words'][0]);
      }else if (!empty($records[1]['words'][0])) {
         return array_keys($records[1]['words'][0]);
      }else if (!empty($records[2]['words'][0])) {
         return array_keys($records[2]['words'][0]);
      }
      return [];
   }

   private function getWordHeadings($keys){
      $options = \Config::get('constants.words');
      $data = [];
      foreach ($keys as $key => $value) {
         $data[$value] = @$options[$value];
      }
      return array_filter($data);
   }

}