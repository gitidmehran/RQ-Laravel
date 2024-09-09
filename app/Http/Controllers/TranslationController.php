<?php

namespace App\Http\Controllers;

use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\Languages;
use App\Models\Notes;
use App\Models\OtherWordsInfo;
use App\Models\PhrasesWords;
use App\Models\PhrasesWordsTranslations;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\WordReferences;
use App\Models\Words;
use App\Models\WordsTranslations;
use Auth,Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    protected $singular = "Translation";
    protected $plural = "Translations";
    protected $view = 'translations.';
    protected $perPage = 50;

    public function translationAyat(Request $request){

        $scholars = User::where('role',3)->get()->toArray();
       // return response()->json();
        return view($this->view.'add-translation',['scholars' => $scholars]);
    }

    public function getTranslation(Request $request){
        try{
            $input = $request->all();
            $authId = (isset($input['user']) && !empty($input['user']))?$input['user']:Auth::id();
            

            $ayat = InfoData::with(
                'words.singleReferenceWord.word.ayat:id,surahNo,ayatNo',
                'words.phraseReferenceWord.word.ayat:id,surahNo,ayatNo', 
                'words.phrasesWords'
                )
                ->where(['surahNo'=>$input['surahNo'],'ayatNo'=>$input['ayat']])
                ->get()->first()->toArray();
            
            // CHECK IF SCHOLAR ALREADY ADDED THE TRANSLATION
            $isAlreadyAdded = AyatsTranslation::where(['scholar_id'=>$authId,'ayat_id'=>$ayat['id']])->count();
            if($isAlreadyAdded > 0){
                $response = array('success'=>false,'message'=> "You have already added,You Can't add Duplicate Translation.",'authId'=>$authId,'ayat_id'=>$ayat['id']);
                return response()->json($response);
            }

            $scholars = User::where('id',$authId)->get()->toArray();
            $languages = Languages::all()->toArray();
            $notes = Notes::all()->toArray();
            $word_references = \Session::has('word_references')?\Session::get('word_references'):[];
            $word_ids = array_column($ayat['words'],'id');
            $data = [
              'success' => true,
              'message' => 'Get Translation Data Retrieved Successfully',
              'ayat' => $ayat,
              'scholars' => $scholars,
              'languages' => $languages,
              'notes'   => $notes,
              'auth_id' => $authId,
              'word_references' => $word_references,
              'word_ids' => json_encode($word_ids)
            ];
            $view = view($this->view.'translation', $data)->render();
            $response = array('success'=>true,'view'=>$view);
        }catch(\Exception $e){
            $response = ['success'=>false,'message'=>$e->getMessage()];
        }
        return response()->json($response);
    }

    public function getRelatedWords(Request $request){
        $params1 = $request->get('params1');
        $wordids = explode(',', $request->get('params'));
        $scholarId = !empty($request->input('params2'))?$request->input('params2'):Auth::id();

        $words = Words::WithFilters(null,$wordids,null)->get()->toArray();
        $words_name = implode(' ',array_column($words,'word'));
        $words_ = array_column($words,'word');
        // Split out first array element and get References
        $single_word = reset($words);

        $word_reference = WordReferences::where('scholar',$scholarId)->get()->toArray();
        $word_reference_ids = array_unique(array_column($word_reference,'reference_word_id')); 

        $phrase_word_reference = PhrasesWords::where('scholar',$scholarId)->get()->toArray();
        $phrase_word_reference_ids = array_unique(array_column($phrase_word_reference,'phrase_word_id')); 

        $t_w = count( $words_ );
        if ( $t_w > 1 ) {

            $phraseWords = $this->arrangePhraseWords( $words_, $t_w );

            // echo $phraseWords;
            // exit;

             // CHECK IF SESSION HAS REFERENCE
            $session_word_references = \Session::has('phrase_word_references')?\Session::get('phrase_word_references'):[];
            $session_word_references = !empty($phrase_word_reference)?array_filter($phrase_word_reference):[];
            $words = [];
            $wordId = $single_word['id'];
            if(!empty($params1)){
                $references = array_filter($phrase_word_reference,fn($data) => $data['word_id']==$wordId);
                $words = array_column($references,'phrase_word_id');
            }else if(!empty($session_word_references)){
                $words = $session_word_references[$wordId] ?? [];
            };

            $session_word_references[$wordId] = $words;
            // $checkedAll = (count($words)===count($relatedWords));

            $data = [
                'title'         => 'Phrase Word References',
                'single_word'   => $single_word,
                'related_words' => [],
                'words'         => $words,
                'checked_all'   => '',
                'exact_words'   => json_decode( $phraseWords, true )
            ];

            // print_r($data);
            // exit;

            return view($this->view.'phrase-references',$data);
        }

        // IDS THAT ARE ALREADY ADDED AS REFERENCE
        $idsToExclude = (!empty($word_reference_ids) && empty($params1))?$word_reference_ids:null;

        // GET MATCHING WORDS        ->where('id','!=',$wordids)
        $relatedWords = Words::with('ayat')->WithFilters($words_name,null,$idsToExclude)->get()->toArray();
       
        // GET EXACT MATCHING WORDS
        $exactWords = Words::with('ayat')->where('word',$words_name)->WithFilters(null,null,$idsToExclude)->get()->toArray();

        // CHECK IF SESSION HAS REFERENCE
        $session_word_references = \Session::has('word_references')?\Session::get('word_references'):[];
        $session_word_references = !empty($word_references)?array_filter($word_references):[];
        $words = [];
        $wordId = $single_word['id'];
        if(!empty($params1)){
            $references = array_filter($word_reference,fn($data) => $data['word_id']==$wordId);
            $words = array_column($references,'reference_word_id');
        }else if(!empty($session_word_references)){
            $words = $session_word_references[$wordId] ?? [];
        };

        $session_word_references[$wordId] = $words;
        $checkedAll = (count($words)===count($relatedWords));
        $data = [
            'title'         => 'Word References',
            'single_word'   => $single_word,
            'related_words' => $relatedWords,
            'words'         => $words,
            'checked_all'   => $checkedAll,
            'exact_words'   => $exactWords
        ];

        // print_r($data);
        // exit;
        return view($this->view.'word-references',$data);
    }

    public function saveTranslation(Request $request){
        $input = $request->all();
        $checkAlreadyExist = AyatsTranslation::where(['ayat_id'=>$input['ayat_id'],'scholar'=>$input['auth_id']])->count();
        if($checkAlreadyExist>0){
            return response()->json(['success'=>false,'message'=>"You have already added,You Can't add Duplicate Translation."]);
        }
        $requestdata = $this->arrangeRequestData($input);
        DB::beginTransaction();
        try{
    
            // INSERT RECORDS IF NOT EMPTY 
            if(!empty($requestdata['ayat_translations'])) 
                AyatsTranslation::insert($requestdata['ayat_translations']);

            if(!empty($requestdata['word_translations'])) 
                WordsTranslations::insert($requestdata['word_translations']);

            if(!empty($requestdata['reference_word_translations']))
                WordsTranslations::insert($requestdata['reference_word_translations']);

            if(!empty($requestdata['word_preferences_array'])) 
                WordReferences::insert($requestdata['word_preferences_array']);

            if(!empty($requestdata['word_numbers_array'])) 
                PhrasesWords::insert($requestdata['word_numbers_array']);

            if(!empty($requestdata['phrase_reference_word_translations'])) 
                PhrasesWordsTranslations::insert($requestdata['phrase_reference_word_translations']);

            if(!empty($requestdata['otherinfo_array'])) 
                OtherWordsInfo::insert($requestdata['otherinfo_array']);

            if(!empty($requestdata['notes_array'])) 
                WordNotes::insert($requestdata['notes_array']);
            DB::commit();
            \Session::put('word_references',[]);
            \Session::put('phrase_word_references',[]);
            $response = array('success'=>true,'message'=>'Data Updated','action'=>'reload');
        }catch(\Exception $e){
            DB::rollback();            
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function saveWordPreferences(Request $request){
        try {
            $input = $request->all();
            $word_references = \Session::has('word_references')?\Session::get('word_references'):[];
            $word_references[$input['word_id']] = $input['reference_words'];
            \Session::put('word_references',$word_references);
            $response = array('success'=>true,'message'=>'Data Added','action'=>'close','references'=>$word_references);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    // Raza Dev
    public function savePhraseWordPreferences(Request $request){
        // return $request->all();
        try {
            $input = $request->all();
            $word_references = \Session::has('phrase_word_references')?\Session::get('phrase_word_references'):[];
            $word_references[$input['word_id']] = $input['reference_phrase_words'];
            \Session::put('phrase_word_references',$word_references);
            $response = array('success'=>true,'message'=>'Data Added','action'=>'close','references'=>$word_references);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function translationsByScholars(Request $request){
        $authId = $request->has('scholar')?$request->get('scholar'):Auth::id();
        $column = 'created_user';
        $data = $this->getTranslations($column,$authId,$request);
        return view($this->view.'scholar-translations',$data);
    }

    public function singleScholarTranslation(Request $request){
        $authId = '2';
        $column = 'scholar';
        $data = $this->getTranslations($column,$authId,$request);
        return view($this->view.'scholar-translations',$data);
    }

    public function edit(Request $request,$id,$scholar){
        $authId = $scholar;
        $row = InfoData::with(['ayatsTranslations' => fn($q) => $q->OfSingleScholar($authId)])->where('id',$id)->first()->toArray();
        $row_translations = $row['ayats_translations'];
        $ayat_translations = [];
        $languages = Languages::select('id','name')->get()->toArray();
        foreach ($languages as $key => $value) {
            $ayat = array_filter($row_translations,function($item) use ($value){
                return ($item['language']==$value['id']);
            });
            $translation = '';
            if(!empty($ayat)){
                $ayat = reset($ayat);
                $translation = $ayat['translation'];
            }
            $ayat_translations[$key]['ayat_id'] = $row['id'];
            $ayat_translations[$key]['language_name'] = $value['name'];
            $ayat_translations[$key]['language'] = $value['id'];
            $ayat_translations[$key]['scholar'] = $authId;
            $ayat_translations[$key]['translation'] = $translation;  
        }
       
        $row['ayats_translations'] = $ayat_translations;
        // echo 'auth id '.$authId;die;
        $query_words = Words::with([
            'phrasesWords',
            'wordReferences',
            'WordNotes',
            'otherWordInfo',
            'singleReferenceWord'=> fn($q) => $q->with('word.translations','scholarInfo:id,short_name'),
            'phraseReferenceWord'=> fn($q) => $q->with('word.phrase_translations','scholarInfo:id,short_name'),
            'translations'=> fn($q) => $q->OfSpecialScholars($authId)//->groupBy('language','scholar'),
        ])->where('ayat_id',$id)->get()->toArray();
        // echo '<pre>';print_r($query_words);die;
        $wordinfodata = $this->arrangWordInfo($query_words);
        // echo '<pre>';print_r($wordinfodata);die;
        $word_ids = array_column($wordinfodata['words'],'id');
        $notes = Notes::all()->toArray();
        $prev = url()->previous();
        $previous_url = explode('/', $prev);
        $previous_url  = end($previous_url);
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'auth_id'  => $authId,
            'languages'=> $languages,
            'row'      => $row,
            'words'    => $wordinfodata['words'],
            'notes'    => $notes,
            'phrases_words' => $wordinfodata['phrases_words'],
            'word_ids' => json_encode($word_ids),
            'previous_url' => $previous_url
        ];
        // echo json_encode($data);die;
        return view($this->view.'edit',$data);
    }

    public function update(Request $request){
        $input = $request->all();
        $requestdata = $this->arrangeRequestData($input);
        // echo '<pre>';print_r($word_preferences_array);die;
        DB::beginTransaction();
        try{
    
            // INSERT OR UPDATE RECORDS IF NOT EMPTY 
            if(!empty($requestdata['ayat_translations'])){
                foreach ($requestdata['ayat_translations'] as $key => $val) {
                    $isExist = AyatsTranslation::where(['ayat_id'=>$val['ayat_id'],'scholar'=>$val['scholar'],'language'=>$val['language']])->first();
                    if(!empty($isExist)){
                        if(isset($val['created_at'])) unset($val['created_at']);
                        $isExist->update($val);
                    }else{
                        AyatsTranslation::insert($val);
                    }
                }
            }

            if(!empty($requestdata['word_translations'])){
                WordsTranslations::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->delete();
                WordsTranslations::insert($requestdata['word_translations']);
            }

            if(!empty($requestdata['reference_word_translations'])){
                $references = WordReferences::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->get()->toArray();
                if(!empty($references)){
                    $referencesIds = array_column($references,'reference_word_id');
                    WordsTranslations::where(['scholar'=>$input['auth_id'],'is_reference_word'=>1])->whereIn('word_id',$referencesIds)->delete();
                }
                WordsTranslations::insert($requestdata['reference_word_translations']);
            }

            if(!empty($requestdata['word_preferences_array'])){
                WordReferences::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->delete();
                WordReferences::insert($requestdata['word_preferences_array']);
            }

            if(!empty($requestdata['word_numbers_array'])){
                PhrasesWords::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->delete();
                PhrasesWords::insert($requestdata['word_numbers_array']);                
            }

            if(!empty($requestdata['phrase_reference_word_translations'])) 
                $references = PhrasesWords::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->get()->toArray();
                // echo json_encode( $references );exit;

                if(!empty($references)){
                    $referencesIds = array_column($references,'phrase_word_id');
                    PhrasesWordsTranslations::where(['scholar'=>$input['auth_id'],'is_reference_word'=>1])->whereIn('word_id',$referencesIds)->delete();
                }
                PhrasesWordsTranslations::insert($requestdata['phrase_reference_word_translations']);

            if(!empty($requestdata['otherinfo_array'])){
                OtherWordsInfo::where('scholar',$input['auth_id'])->whereIn('word_id',$input['word_ids'])->delete();
                OtherWordsInfo::insert($requestdata['otherinfo_array']);
            }

            if(!empty($requestdata['notes_array'])){
                foreach ($requestdata['notes_array'] as $key => $val) {
                    WordNotes::where(['word_id'=>$val['word_id'],'scholar'=>$val['scholar'],'note_id'=>$val['note_id']])->delete();
                    WordNotes::insert($val);
                }
            }
            DB::commit();
            \Session::put('word_references',[]);
            \Session::put('phrase_word_references',[]);
            return redirect('/dashboard/'.$input['previous_url'])->with('success','Data Updated');
        }catch(\Exception $e){
            DB::rollback();            
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    private function arrangeRequestData($input){
        // echo json_encode( $input );exit;
        $ayat_translations = $word_translations = $word_preferences_array = $word_numbers_array = $otherinfo_array = $notes_array = $reference_word_translations = $phrase_reference_word_translations = [];
        $word_references = \Session::has('word_references')?\Session::get('word_references'):[];
        $phrase_word_references = \Session::has('phrase_word_references')?\Session::get('phrase_word_references'):[];
        // echo '<pre>';print_r($word_references);die;
        $scholarId = (isset($input['auth_id']) && !empty($input['auth_id']))?$input['auth_id']:Auth::id();        
        // Ayats Translations
        if(!empty($input['ayat_translations'])){
            foreach ($input['ayat_translations'] as $key => $value) {
                foreach ($value as $ikey => $val) {
                    $ayat_translations[] = [
                        'ayat_id' => $input['ayat_id'],
                        'scholar' => $key,
                        'language' => $ikey,
                        'translation' => $val,
                        'created_user' => Auth::id(),
                        'created_at' => \Carbon\Carbon::now()
                    ];
                }
            }    
        }

        if(!empty($input['words_translations'])){
            foreach (@$input['words_translations'] as $key => $value) {
                foreach ($value['language'] as $ikey => $val) {
                    $word_translations[] = [
                        'word_id' => $key,
                        'language' => $ikey,
                        'translation' => $val,
                        'scholar'     => $scholarId,
                        'created_at' => \Carbon\Carbon::now()
                    ];
                }
                // MAKE WORD PREFERENCES ARRAY
                if($value['reference_type']=="by_reference" && array_key_exists($key, $word_references)){
                    foreach ($word_references[$key] as $val) {
                        $word_preferences_array[] = [
                            'word_id' => $key,
                            'scholar' => $scholarId,
                            'reference_word_id' => $val,
                            'created_at' => \Carbon\Carbon::now(),
                        ];

                        // ADDING DATA TO REFERENCE WORD TRANSLATIONS ARRAY
                        foreach ($value['language'] as $ikey => $ival) {
                            $reference_word_translations[] = [
                                'word_id' => $val,
                                'language' => $ikey,
                                'translation' => $ival,
                                'scholar'     => $scholarId,
                                'is_reference_word' => 1,
                                'created_at' => \Carbon\Carbon::now()
                            ];
                        }
                    }
                }                

                // Make WORD NUMBERS ARRAY
                if(@$value['reference_type_number'] > 1  && array_key_exists($key, $phrase_word_references) ){
                    $wordIndex = array_search($key,array_values($input['word_ids']));
                    $slice = array_slice($input['word_ids'], $wordIndex,$value['reference_type_number']);
                    array_shift($slice);


                    // foreach ($slice as $val) {
                    foreach ($phrase_word_references[$key] as $val) {
                        $word_numbers_array[] = [
                            'word_id' => $key,
                            'scholar' => $scholarId,
                            'phrase_word_id' => $val,
                            'created_at' => Carbon\Carbon::now(),
                        ];

                        // ADDING DATA TO REFERENCE WORD TRANSLATIONS ARRAY
                        foreach ($value['language'] as $ikey => $ival) {
                            $phrase_reference_word_translations[] = [
                                'word_id' => $val,
                                'language' => $ikey,
                                'translation' => $ival,
                                'scholar'     => $scholarId,
                                'is_reference_word' => 1,
                                'created_at' => \Carbon\Carbon::now()
                            ];
                        }
                    };            
                }

                // Addresser, Addressee Info
                $otherinfo_array[] = [
                    'word_id'   => $key,
                    'scholar'   => $scholarId,
                    'addresser' => $value['addresser'],
                    'addressee' => $value['addressee'],
                    'reference_type' => $value['reference_type'],
                    'reference_type_number' => $value['reference_type_number'],
                    'created_at' => Carbon\Carbon::now(),
                ];    
            }
        }

        // NOTES AGAINST WORDS AND SCHOLARS
        if(!empty($input['word_references'])){
            $filter_references = array_filter($input['word_references']);
            foreach ($filter_references as $key => $value) {
                foreach (array_filter($value) as $ikey => $val) {
                    $notes_array[] = [
                      'word_id' => $key,
                      'note_id' => $val,
                      'scholar' => $scholarId,
                      'created_at' => Carbon\Carbon::now()
                    ];
                }
            }    
        }
        // echo 'word reference array <pre>';print_r($otherinfo_array);die;
        return [
            'ayat_translations' => $ayat_translations,
            'word_translations' => $word_translations,
            'notes_array'       => $notes_array,
            'word_numbers_array' => $word_numbers_array,
            'otherinfo_array' => $otherinfo_array,
            'word_preferences_array' => $word_preferences_array,
            'reference_word_translations' => $reference_word_translations,
            'phrase_reference_word_translations' => $phrase_reference_word_translations
        ];
    }

    private function arrangWordInfo(&$words){
        $phrases_words = [];
        if(!empty($words)){
            foreach ($words as $key => $value) {
                
                $phrases_words_ids = array_column($value['phrases_words'],'phrase_word_id');
                if(!empty($phrases_words_ids)){                    
                    array_push($phrases_words,...$phrases_words_ids);
                }
                
                $row_translations = $value['translations'];
                $translations = [];
                $words[$key]['word_refrence_count'] = count($value['word_references']);
                $words[$key]['notes'] = array_column($value['word_notes'],'note_id');
                foreach (@$row_translations as $val) {
                    $translations[$val['language']] = $val['translation'];
                }
                $singleWord = $value['single_reference_word'];
                if(!empty($singleWord) && empty($translations)){
                    $wordTranslations = $singleWord['word']['translations'];
                    $scholarId = $singleWord['scholar'];
                    $singleWordTranslations = array_filter($wordTranslations,function($item) use($scholarId){
                        return $item['scholar']===$scholarId;
                    });
                    if(!empty($singleWordTranslations)){
                        $singleWordTranslations = array_values(array_column( $singleWordTranslations , null, 'language' ));
                    }
                    foreach ($singleWordTranslations as $v) {
                        $translations[$v['language']] = $v['translation'];
                    }
                }
                $words[$key]['translations'] = $translations;

                $singleWord = $value['phrase_reference_word'];
                if(!empty($singleWord) && empty($translations)){
                    $wordTranslations = $singleWord['word']['phrase_translations'];
                    $scholarId = $singleWord['scholar'];
                    $singleWordTranslations = array_filter($wordTranslations,function($item) use($scholarId){
                        return $item['scholar']===$scholarId;
                    });
                    if(!empty($singleWordTranslations)){
                        $singleWordTranslations = array_values(array_column( $singleWordTranslations , null, 'language' ));
                    }
                    foreach ($singleWordTranslations as $v) {
                        $translations[$v['language']] = $v['translation'];
                    }
                }
                $words[$key]['translations'] = $translations;
            }
        }

        // echo "<pre>"; print_r( $words );exit;

        return ['words'=>$words,'phrases_words'=>$phrases_words];
    }

    public function singleTranslationView(Request $request){
        $authId = $request->has('user')?$request->get('user'):Auth::id();
        $ayat = InfoData::with('words.singleReferenceWord.word.ayat:id,surahNo,ayatNo')->where('id',23)->get()->first()->toArray();
        // \Session::put('word_references',[]);
        // echo '<pre>';print_r($ayat);die;
        $scholars = User::where('id',$authId)->get()->toArray();
        $languages = Languages::all()->toArray();
        $notes = Notes::all()->toArray();
        $word_references = \Session::has('word_references')?\Session::get('word_references'):[];
        $word_ids = array_column($ayat['words'],'id');
        $data = [
          'ayat' => $ayat,
          'scholars' => $scholars,
          'languages' => $languages,
          'notes'   => $notes,
          'auth_id' => $authId,
          'word_references' => $word_references,
          'word_ids' => json_encode($word_ids)
        ];
        return view($this->view.'single-translation',$data);
    }

    public function removePreference(Request $request){
        try {
            $input = $request->all();
            WordsTranslations::where(['word_id'=>$input['word'],'scholar'=>$input['scholar'],'is_reference_word'=>1])->delete();

            $check_parent_delete = WordReferences::where(['word_id'=>$input['word']])->where(['reference_word_id'=>$input['word']])->count();
            if( $check_parent_delete > 0 ) { 
                // references are deleted
                WordReferences::where(['word_id'=>$input['word']])->delete();
            } else {
                // only child reference were deleted
                WordReferences::where(['reference_word_id'=>$input['word']])->delete();
            }

            $response = array('success'=>true,'message'=>"Word Preference deleted Successfully");
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function removePhrasePreference(Request $request){
        try {
            $input = $request->all();
            $refWord = explode( ',', $input['refWord'] );

            // echo "<pre>"; print_r( $input ); exit;

            PhrasesWordsTranslations::where(['scholar'=>$input['scholar'],'is_reference_word'=>1])->whereIn( 'word_id', $refWord )->delete();

            $check_parent_delete = PhrasesWords::where(['word_id' => $input['word']])->where(['scholar'=>$input['scholar']])->count();
            if( $check_parent_delete > 0 ) { 
                // references are deleted
                PhrasesWords::where(['word_id' => $input['word'], 'scholar'=>$input['scholar']])->delete();
            } else {
                // only child reference were deleted
                PhrasesWords::where('scholar', $input['scholar'])->whereIn( 'phrase_word_id', $refWord )->delete();
            }

            $response = array('success'=>true,'message'=>"Phrase Word Preference deleted Successfully");
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function destroy(Request $request,$id){
        try {
            $authId = Auth::id();
            $ayat = InfoData::with('words')->find($id)->toArray();
            $word_ids = array_column($ayat['words'],'id');
            DB::beginTransaction();
            if(!empty($word_ids)){
                WordReferences::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
                PhrasesWords::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
                PhrasesWordsTranslations::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
                WordNotes::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
                WordsTranslations::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
                OtherWordsInfo::where('scholar',$authId)->whereIn('word_id',$word_ids)->delete();
            }
            AyatsTranslation::where('scholar',$authId)->where('ayat_id',$id)->delete();
            DB::commit();
            $response = array('success'=>true,'message'=> $this->singular." Removed Successfully");
        } catch (\Exception $e) {
            DB::rollback();
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    // GET TRANSLATION ON BEHALF OF DIFFERENT FILTERS

    private function getTranslations($column,$authId,$request){
        $translations = AyatsTranslation::where($column,$authId)->groupBy('ayat_id')->get()->toArray();
        // echo '<pre>';print_r($translations);die;
        $ayatIds = array_unique(array_column($translations,'ayat_id'));
        $scholars = array_unique(array_column($translations,'scholar'));
        $dblanguages = Languages::all()->toArray();

        if($request->has('per_page') && !empty($request->input('per_page')))
            $this->perPage = $request->get('per_page');

        $query = InfoData::query()
        ->RequestFilters($request)
        ->with([
            'ayatsTranslations' => fn($q) => $q->OfSpecialScholars($scholars)->with('scholarinfo:id,short_name'),
        ])->whereIntegerInRaw('id',$ayatIds)->orderBy('surahNo')->orderBy('ayatNo')->paginate($this->perPage);

        $count = $query->total();
        $links = $query->appends($request->all())->links('pagination::bootstrap-5');
        $ayatTranslations = $query->toArray();        
       
        $list = $language_names = [];
        if(!empty($ayatTranslations['data'])){
            foreach ($ayatTranslations['data'] as $key => $value) {
                $list[$key]['id'] = $value['id'];
                $list[$key]['arabic'] = $value['arabic'];
                $list[$key]['ayatNo'] = $value['ayatNo'];
                $list[$key]['surahNo'] = $value['surahNo'];
                $scholarName = !empty($value['ayats_translations'])?$value['ayats_translations'][0]['scholarinfo']['short_name']:'';
                $scholarId = !empty($value['ayats_translations'])?$value['ayats_translations'][0]['scholarinfo']['id']:'';
                $list[$key]['scholar_name'] = $scholarName;
                $list[$key]['scholar_id'] = $scholarId;
                $languages = [];
                foreach ($dblanguages as $ikey => $val) {
                    $languageItem = array_filter($value['ayats_translations'],function($translation) use ($val){
                        return $translation['language']==$val['id'];
                    });
                    if(!empty($languageItem)){
                        $item = reset($languageItem);
                        $languages[$val['name']] = $item['translation'];
                    }else{
                        $languages[$val['name']] = '';
                    }
                    $language_names[] = $val['name'];
                } 
                $list[$key]['languages'] = $languages;
            }
        }
        $language_names = array_unique($language_names);
        $share_ayats = [];
        if($request->has('surah') && !empty($request->input('surah'))){
            $share_ayats = InfoData::where('surahNo',$request->get('surah'))->select('id','ayatNo')->groupBy('ayatNo')->orderBy('ayatNo')->get()->toArray();
        }
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'links'    => $links,
            'filter'   => $request->all(),
            'total_records' => $count,
            'language_names' => $language_names,
            'share_ayats' => $share_ayats,
            'list'     => $list
        ];
        return $data;
    }

    private function arrangePhraseWords( $words_, $t_w ) {
        $t = DB::table('words')->whereIn('word',  $words_)->get()->toArray();

        $c = 0;
        $n = array();

        foreach ($t as $key => $value) {
            $c = (int)$key + 1;
            if (isset($t[$c])) {

                switch ($t_w) {
                    case '2':
                        if (
                            $value->word    == $words_[0] &&
                            $t[$c]->word    == $words_[1] &&
                            $value->ayat_id == $t[$c]->ayat_id
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c]
                            );
                        }
                        break;
                    case '3':
                        if (
                            $value->word    == $words_[0] &&
                            $t[$c]->word    == $words_[1] &&
                            $t[$c + 1]->word    == $words_[2] &&
                            $value->ayat_id == $t[$c]->ayat_id
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id      => $value,
                                $t[$c]->id      => $t[$c],
                                $t[$c + 1]->id    => $t[$c + 1]
                            );
                        }
                        break;
                    case '4':
                        if (
                            $value->word    == $words_[0] &&
                            $t[$c]->word    == $words_[1] &&
                            $t[$c + 1]->word    == $words_[2] &&
                            $t[$c + 2]->word    == $words_[3] &&
                            $value->ayat_id == $t[$c]->ayat_id
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c],
                                $t[$c + 1]->id => $t[$c + 1],
                                $t[$c + 2]->id => $t[$c + 2]
                            );
                        }
                        break;
                    case '5':
                        if (
                            $value->word    == $words_[0] &&
                            $t[$c]->word    == $words_[1] &&
                            $t[$c + 1]->word    == $words_[2] &&
                            $t[$c + 2]->word    == $words_[3] &&
                            $t[$c + 3]->word    == $words_[4] &&
                            $value->ayat_id == $t[$c]->ayat_id
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c],
                                $t[$c + 1]->id => $t[$c + 1],
                                $t[$c + 2]->id => $t[$c + 2],
                                $t[$c + 3]->id => $t[$c + 3]
                            );
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
        return json_encode( $n );
    }
}
