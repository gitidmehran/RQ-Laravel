<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AyatNotes;
use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\Languages;
use App\Models\Story;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\Words;
use App\Models\OtherWordTranslationInfo;
use App\Utility\Utility;
use Auth, Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TranslationDisplayController extends Controller
{
    protected $per_page = 50;

    public function index(Request $request)
    {
        
        try {
            $settings = $this->getSettingsData();

            $input_scholar_id = $request->input('scholarId');

            $scholarIds = (isset($input_scholar_id) && !empty($input_scholar_id))?explode(',', $input_scholar_id):$settings['wordScholarsIDs'];
            
            $query = InfoData::query()->RequestFilters($request)->select('id', 'surah_id', 'ayatNo', 'arabic','arabic_simple')->with([
                'ayatsTranslationsByView' => fn ($q) => $q->OfSpecialScholars($scholarIds)->where('translation','!=',''),
                'words' => fn ($q) => $q->with([
                    'otherTranslationInfo'=> fn($infoQuery) => $infoQuery->OfSpecialScholars($scholarIds)->with('referredUser:id,name,short_name','referredword:id,surah_no,ayat_no,word,reference'),
                    'wordTranslationsByView'=> fn ($newq) => $newq->OfSpecialScholars($scholarIds)])->with('rootWordMeaning')
                ]
            );
            
            if($request->has('perPage') && !empty($request->get('perPage')))
                $this->per_page = $request->input('perPage');

            $query = $query->paginate($this->per_page);
            $total =  $query->total();
            $result = $query->toArray();
           // return response()->json($result); 
            $ayatNotes = AyatNotes::whereIn('scholar_id',$scholarIds)->get()->toArray();
            $wordNotes = WordNotes::whereIn('scholar_id', $scholarIds)->get()->toArray();
            // $ayatNoteFiles = Storage::disk('s3')->allFiles('ayat-notes');
            $ayatNoteFiles = [];
            
            $list = [];
            if (!empty($result['data'])) {
                foreach ($result['data'] as $key => $value) {
                    $ayatTranslations = $value['ayats_translations_by_view'];
                    // ARRANGE AYAT NOTES INSIDE AYAT TRANSLATIONS
                    if(!empty($value['ayats_translations_by_view'])){
                        foreach ($value['ayats_translations_by_view'] as $ikey => $val) {
                            $note = array_filter($ayatNotes, function($item) use($val){
                                return ($item['ayat_id']==$val['ayat_id'] && $item['language_id']==$val['language_id'] && $item['scholar_id']==$val['scholar_id']);
                            });
                            // $note = reset($note);
                            // $noteFileLink = !empty($note['note_file']) ? Utility::generateS3BucketUrl($note['note_file'], 'ayat-notes', $ayatNoteFiles) : '';
                            // $ayatTranslations[$ikey]['note'] = $noteFileLink;
                        }
                    }
                    $list[] = [
                        "id" => $value['id'],
                        "surahId" => $value['surah_id'],
                        "ayatId" => $value['ayatNo'],
                        "arabic" => $value['arabic'],
                        "arabicSimple" => $value['arabic_simple'],
                        'ayatTranslations' => Utility::convertKeysToCamelCase($ayatTranslations),
                        'words' => $this->formatWordData($value['words'], $settings, $wordNotes)
                    ];

                    if(!isset($settings['showNonArabic']) || $settings['showNonArabic']==false){
                        unset($list[$key]['arabicSimple']);
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => 'Word By Word Data Retrieved Successfully',
                'total' => $total,
                'filters' => $request->all(),
                'columns' => $this->convertLabelToCamelCase($settings['allowedColumns']),
                'list' => $list
            ];
            
        } catch (\Exception $e) {
            $response = array('success'=> false,'message'=> $e->getMessage());
        }
        return response()->json($response);
    }

    public function wordSearch(Request $request){
        try {
            $settings = $this->getSettingsData();
            
            $query = InfoData::query()->select('id', 'surah_id', 'ayatNo', 'arabic','arabic_simple')->with([
                'ayatsTranslationsByView' => fn ($q) => $q->OfSettingsFilter($settings)->where('translation','!=',''),
                'words' => fn ($q) => $q->with([
                    'otherTranslationInfo'=> fn($infoQuery) => $infoQuery->OfSpecialScholars($settings['wordScholarsIDs'])->with('referredUser:id,name'),
                    'wordTranslationsByView'=> fn ($newq) => $newq->OfSettingsFilter($settings)])->with('rootWordMeaning')
                ]
            );

            if( ($request->input('searchType') == 'phrase' || $request->input('searchType') == 'simplePhrase') && !empty($request->input('search')) ) {
                $column = $request->input('searchType')=="phrase" ? "arabic" : "arabic_simple";
                $query = $query->where($column, 'like', '%'. $request->input('search') .'%');
            }

            // FILTER DATA ON BEHALF OF WORD, ROOT_WORD, SIMPLE_WORD
            if($request->has('search') && !empty($request->input('search'))){
                $words = Words::RequestFilters($request)->get()->toArray();
                $ayatIds = array_unique(array_column($words, 'ayat_id'));
                $query = $query->whereIn('id', $ayatIds);
            }

            // FILTER DATA ON BEHALF OF TRANSLATIONS
            if($request->has('translationKeyWord') && !empty($request->input('translationKeyWord'))){
                $keyword = $request->input('translationKeyWord');
                $ayatTranslations = AyatsTranslation::where('translation','like','%'.$keyword.'%')->get()->toArray();
                $ayatIds = array_unique(array_column($ayatTranslations,'ayat_id'));
                $query->whereIn('id',$ayatIds);
            }

            // FILTER DATA ON BEHALF OF RootWord
            // if($request->has('root_word') && !empty($request->input('root_word'))){
            //     $keyword = $request->input('root_word');
            //     $ayatTranslations = Words::where('root_word','like','%'.$keyword.'%')->get()->toArray();
            //     $ayatIds = array_unique(array_column($ayatTranslations,'ayat_id'));
            //     $query->whereIn('id',$ayatIds);
            // }
            
            $query = $query->paginate($this->per_page);
            $total =  $query->total();

            $result = $query->toArray();
            $list = [];
            // echo count($result['data']);die;
            $ayatNotes = AyatNotes::whereIn('scholar_id',$settings['ayatScholarsIDs'])->get()->toArray();
            $wordNotes = WordNotes::whereIn('scholar_id',$settings['wordScholarsIDs'])->get()->toArray();

            if (!empty($result['data'])) {
                foreach ($result['data'] as $key => $value) {

                    $ayatTranslations = $value['ayats_translations_by_view'];
                    // ARRANGE AYAT NOTES INSIDE AYAT TRANSLATIONS
                    if(!empty($value['ayats_translations_by_view'])){
                        foreach ($value['ayats_translations_by_view'] as $ikey => $val) {
                            $note = array_filter($ayatNotes, function($item) use($val){
                                return ($item['ayat_id']==$val['ayat_id'] && $item['language_id']==$val['language_id'] && $item['scholar_id']==$val['scholar_id']);
                            });
                            $note = reset($note);
                            $ayatTranslations[$ikey]['note'] = $note['note_file'] ?? '';
                        }
                    }

                    $list[] = [
                        "id" => $value['id'],
                        "surahId" => $value['surah_id'],
                        "ayatId" => $value['ayatNo'],
                        "arabic" => $value['arabic'],
                        'arabicSimple' => $value['arabic_simple'],
                        'ayatTranslations' => Utility::convertKeysToCamelCase($ayatTranslations),
                        'words' => $this->formatWordData($value['words'], $settings, $wordNotes)
                    ];

                    if(!isset($settings['showNonArabic']) || $settings['showNonArabic']==false){
                        unset($list[$key]['arabicSimple']);
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => 'Word Search Data Retrieved Successfully',
                'total' => $total,
                'filters' => $request->all(),
                'columns' => $this->convertLabelToCamelCase($settings['allowedColumns']),
                'list' => $list
            ];
        } catch (Exception $e) {
            $response = array('success'=> false,'message'=> $e->getMessage());
        }
        return response()->json($response);
    }

    private function getSettingsData()
    {
        $settings = Auth::user()->user_settings;
        $settings = json_decode($settings, true);
        $allowedColumns = $allowedScholarsArray = [];
        $showNonArabic = false;

        if(empty($settings)){
            $languages = Languages::select('id','name','short_name')->get()->toArray();
            $scholar = User::find(Auth::id())->toArray();
            $allowedAyatLanguages=$allowedWordLanguages= array_unique(array_column($languages,'id'));
            $ayatScholarsIDs=$wordScholarsIDs = [$scholar['id']];
           
            $constantWords = \Config('constants.words');
            foreach (['word','root_word'] as $val) {
                $key  =  lcfirst(str_replace(['_','-'], "", ucwords($val, "_")));
                $allowedColumns[] =[
                    'title' => $constantWords[$val],
                    'dataIndex' => $key,
                    'key' => $key,
                    'note' => ''
                ];
            }
            foreach ($languages as $key => $value) {
                $allowedScholarsArray[] = [
                    'key' => $scholar['short_name'].'-'.$value['short_name'],
                    'language_id' => $value['id'],
                    'scholar_id' => $scholar['id']
                ];
                $key = $scholar['short_name'].$value['short_name'];
                $key  =  lcfirst(str_replace(['_','-'], "", ucwords($key, "_")));
                $allowedColumns[] =[
                    'title' => $scholar['short_name'].'-'.$value['name'],
                    'dataIndex' => $key,
                    'key' => $key,
                    'note' => $key.'_note'
                ];
            }
        } else{
            // Get Columns Data From Settings
            $displayWords = array_filter($settings['displayWords'], function ($item) {
                return $item['checked']==1;
            });
        
            $constantsWords = Config('constants.words');
            if (!empty($displayWords)) {
                foreach ($displayWords as $v) {
                    $key = lcfirst(str_replace("_", "", ucwords($v['value'], "_")));
                    $allowedColumns[] = [
                        'title' => $constantsWords[$v['value']],
                        'dataIndex' => $key,
                        'key' => $key,
                        'note' => ''
                    ];
                }
            }

            // Grammer Settings
            if(!empty($settings['grammerSettings'])){
                $grammerSettings = array_filter($settings['grammerSettings'], function ($item) {
                    return $item['checked']==1;
                });
                
                foreach ($grammerSettings as $value) {
                    if($value['value'] == 'show_non_arabic'){
                        $showNonArabic = true;
                    }

                    if($value['value'] != 'show_word_translation' && $value['value'] != 'show_non_arabic'){
                        $key = lcfirst(str_replace(['_','-'], "", ucwords($value['value'], "_")));
                        $allowedColumns[] = [
                            'title' => $value['label'],
                            'dataIndex' => $key,
                            'key' => $key,
                            'note' => ''
                        ];
                    }
                }
            }

            // Getting Words Settings
            $wordScholars = array_filter($settings['wordScholars'], function ($item) {
                return $item['checked']==1;
            });
            $wordScholarsIDs = array_unique(array_column($wordScholars, 'scholarId'));
            $allowedWordLanguages = array_unique(array_column($wordScholars, 'languageId'));
            $allowedScholarLabels =array_unique(array_column($wordScholars, 'displayLabel'));

            foreach ($wordScholars as  $v) {
                $key = lcfirst(str_replace(['_','-'], "", ucwords($v['displayLabel'], "_")));
                $allowedColumns[] = [
                    'title' => $v['displayLabel'],
                    'dataIndex' => $key,
                    'key' => $key,
                    'note' => $key.'_note'
                ];
                $allowedScholarsArray[] = [
                    'key' => $v['displayLabel'],
                    'language_id' => $v['languageId'],
                    'scholar_id' => $v['scholarId']
                ];
            }

            // Getting Ayats Settings
            $ayatScholars = array_filter($settings['ayatScholars'], function ($item) {
                return $item['checked']==1;
            });
            $ayatScholarsIDs = array_unique(array_column($ayatScholars, 'scholarId'));
            $allowedAyatLanguages = array_unique(array_column($ayatScholars, 'languageId'));  
        }
        
        $data = [
            'showNonArabic' => $showNonArabic,
            'allowedColumns' => $allowedColumns,
            'allowedScholarsArray' => $allowedScholarsArray,
            'allowedAyatLanguages' => $allowedAyatLanguages,
            'allowedWordLanguages' => $allowedWordLanguages,
            'wordScholarsIDs' => $wordScholarsIDs,
            'ayatScholarsIDs' => $ayatScholarsIDs
        ];

        return $data;
    }

    private function formatWordData($wordList, $settings, $wordNotes)
    {
        $list = [];
        foreach ($wordList as $key => $val) {

            // CHECKING IF ANY QURANIC LEXICON TYPE IS SOURCE IN OTHER TRANSLATIONINFO ARRAY
            $isReferenceWord = array_filter($val['other_translation_info'], function($item){
                return ($item['quranic_lexicon_type']=="Source");
            });
            $otherTranslationInfo = reset($val['other_translation_info']);
            
            $referenceNumber = @$otherTranslationInfo['quranic_lexicon_number'] ?? '';

            $isReferenceWord = reset($isReferenceWord);
             
            $referenceType = @$otherTranslationInfo['quranic_lexicon_type'] ?? '';
            $userName = (isset($isReferenceWord['referred_user']['short_name']) && !empty($isReferenceWord['referred_user']['short_name'])) ? $isReferenceWord['referred_user']['short_name'] : '';
            $referenceType = !empty($isReferenceWord)? "Source - ". $userName: $referenceType;;
            $referredDAta = [];
             //echo "<pre>";
            if($referenceType==="Referred"){
                 $referredWord = @$otherTranslationInfo['referredword']??'';
                 //$list[$key]['single_reference_word'] =$referredWord;
                if(!empty($referredWord)){
                    $reference = $referredWord['surah_no'].':'.$referredWord['ayat_no'].':'.$referredWord['reference'];
                   // $list[$key]['single_reference_word'] =$reference;
                    $referenceType = $referenceType.'-'.$otherTranslationInfo['referred_user']['short_name'].'- ('.$reference.')';    
                }
            //     //print_r($otherTranslationInfo);
            
             }
            $list[$key]['id'] = $val['id'];
            $list[$key]['reference'] = ($val['surah_no'].':'.$val['ayat_no'].':'.$val['reference']);
            $list[$key]['eng_root_word'] = @$val['root_word_meaning']['english_root_word'] ??'' ;

            // ADDING NON-ARABIC ON BEHALF OF SETTINGS
            if(isset($settings['showNonArabic']) && $settings['showNonArabic']==true){
                $list[$key]['simple_word'] = $val['simple_word'];
            }
            
            $val['addresser'] = @$otherTranslationInfo['addresser'] ??'' ;
            $val['addressee'] = @$otherTranslationInfo['addressee'] ??'' ;
            $val['quranic_lexicon_type'] = $referenceType;
            $val['meaning_eng'] = @$val['root_word_meaning']['meaning_eng'] ??'-' ;
            $val['meaning_urdu'] = @$val['root_word_meaning']['meaning_urdu'] ??'-' ;

            // Arrange Word List by Word Columns
            foreach ($settings['allowedColumns'] as $v) {
                $keyWithUnderScore = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $v['key'])), '_');
                $list[$key][$v['key']] = isset($val[$keyWithUnderScore]) ? $val[$keyWithUnderScore] : '-';

                // ATTACH GRAMMAR NOTES IF GRAMMETICAL DESCRIPTION IS SELECTED
                if($v['key']==="grammaticalDescription"){
                    $grammarNotesBucket  = "grammar-notes";
                    $list[$key]['usmani_style'] = !empty($val['usmani_style']) ? Utility::generateS3BucketUrl($val['usmani_style'], $grammarNotesBucket) : '';

                    $list[$key]['arabic_grammar'] = !empty($val['arabic_grammar']) ? Utility::generateS3BucketUrl($val['arabic_grammar'], $grammarNotesBucket) : '';
                }
            }

            // $list[$key]['quranic_lexicon_number'] = $referenceNumber;
            
            if( $referenceNumber >= 2 ) {
                if( is_array( $wordList[$key+1]['other_translation_info'] ) ) {
                    foreach ($wordList[$key+1]['other_translation_info'] as $k => $value) {
                        $wordList[$key+1]['other_translation_info'][$k]['quranic_lexicon_number'] = '2';
                    }
                }
                $list[$key + 1]['quranic_lexicon_number'] = $referenceNumber;
            }


            // Arrange Word Translations By Selected Scholars
            $translations = $val['word_translations_by_view'];

            foreach ($settings['allowedScholarsArray'] as $v) {
                $singleTranslation = array_filter($translations, function ($item) use ($v) {
                    return ($item['language_id']==$v['language_id'] && $item['scholar_id']==$v['scholar_id']);
                });
                $singleTranslation = reset($singleTranslation);
                $translationKey =  $v['key'];
                $list[$key][$translationKey] = !empty($singleTranslation['translation']) ? $singleTranslation['translation'] : '-';
                $noteKey = $translationKey.'_note';
                $wordId = $val['id'];
                $wordnote = array_filter($wordNotes, function($item) use($wordId,$v){
                    return ($item['word_id']==$wordId && $item['language_id']==$v['language_id'] && $item['scholar_id']==$v['scholar_id']);
                });
                $wordnote = reset($wordnote);
                $wordNoteFileLink = !empty($wordnote['note_file']) ? Utility::generateS3BucketUrl($wordnote['note_file'], 'word-notes') : '';
                
                $list[$key][$noteKey] = $wordNoteFileLink;
            }

            // Reference Detail with Scholar
            $referenceDetail = [];
            foreach (@$val['other_translation_info'] as $v) {
                $referenceDetail[] = [
                    'type' => $v['quranic_lexicon_type'],
                    'scholar' => @$v['referred_user']['short_name'] ?? ''
                ];
            }
            $list[$key]['other_detail'] = $referenceDetail;
            $scholarIds = array_values($settings['wordScholarsIDs'] ?? []);
            
            $list[$key]['word_scholars_ids'] = $scholarIds;
        }
        $list = Utility::convertKeysToCamelCase($list);
        return $list;
    }

    // Get My Translations
    public function myTranslations(Request $request){
        try {
            $authId = $request->has('scholar')?$request->get('scholar'):Auth::id();
            $input = $request->all();
            $ayatTranslations = AyatsTranslation::where('scholar_id',$authId)->get()->toArray();
            $ids = !empty($ayatTranslations)?array_unique(array_column($ayatTranslations,'ayat_id')):[];

            if($request->has('perPage') && !empty($request->get('perPage')))
                $this->per_page = $request->get('perPage');

            $settings['ayatScholarsIDs'] = $authId;
            $query = InfoData::query()->RequestFilters($request)->with([
                'ayatsTranslationsByView' => fn($q) => $q->OfSettingsFilter($settings),
            ])->whereIntegerInRaw('id',$ids);
            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $result = $query->toArray();

            $columnskeys = ['id','reference',"ayat","scholar"];

            foreach ($columnskeys as $val) {
                $columns[] = [
                    'title' => ucfirst($val),
                    'dataIndex' => $val,
                    'key' => $val,
                ];
            }

            $languages = Languages::select('name','short_name')->get()->toArray();

            foreach ($languages as $val) {
                 $columns[] = [
                    'title' => ucfirst($val['name']),
                    'dataIndex' => strtolower($val['short_name']),
                    'key' => strtolower($val['short_name']),
                ];
            }
            $list = [];

            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $value) {
                    $list[$key]['id'] = $value['id'];
                    $list[$key]['reference'] = $value['surah_id'].':'.$value['ayatNo'];
                    $list[$key]['surah_id'] = $value['surah_id'];
                    $list[$key]['ayat_no'] = $value['ayatNo'];
                    $list[$key]['ayat'] = $value['arabic'];
                    $translation = reset($value['ayats_translations_by_view']);
                    $scholarName = @$translation['scholar_name'] ?? '';
                    $scholarId = @$translation['scholar_id'] ?? '';
                    $list[$key]['scholar'] = $scholarName;
                    $list[$key]['scholar_id'] = $scholarId;
                    foreach (@$value['ayats_translations_by_view'] as $v) {
                        $list[$key][strtolower($v['language_name'])] = $v['translation'];
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => 'My Translations Retrieved Successfully',
                'filters' => $input,
                "per_page" => $this->per_page,
                'total_records' => $total,
                'columns' => (array)$columns,
                'list' => $list
            ];
            $response = Utility::convertKeysToCamelCase($response);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message'=> $e->getMessage());
        }
        return response()->json($response);
    }

    // GET TRANSLATIONS BY SCHOLARS
    public function getTranslationByScholars(Request $request){
        try {
            $input = $request->all();
            $settings = $this->getSettingsData();
            $scholarIds = (isset($input['scholarId']) && !empty($input['scholarId']))?explode(',', $input['scholarId']):$settings['ayatScholarsIDs'];
            
            $scholars = User::where(['role' => 3])->whereIn('id', $settings['ayatScholarsIDs'])->select('id','name')->orderBy('name')->get()->toArray();

            $query = InfoData::query()->RequestFilters($request)->with([
                'ayatsTranslationsByView' => fn ($q) => $q->OfSpecialScholars($scholarIds)->where('translation','!=',''),
            ]);

            // RETURN ONLY THOSE AYATS WHERE TRANSLATION IS NOT EMPTY
            if(isset($input['scholarId']) && !empty($input['scholarId'])){
                $ayatTranslationQuery = AyatsTranslation::whereIn('scholar_id', $scholarIds)->where('translation','!=','')->get()->toArray();
                $ayatIds = array_unique(array_column($ayatTranslationQuery,'ayat_id'));
                $query = $query->whereIn('id', $ayatIds);
            }


            if(isset($input['surahId']) && !empty($input['surahId']))
                $query = $query->where('surah_id', $input['surahId']);

            if(isset($input['perPage']) && !empty($input['perPage']))
                $this->per_page = $input['perPage'];

            $query = $query->paginate($this->per_page);
            $total =  $query->total();
            $result = $query->toArray();
            // return response()->json($result);die;

            $ayatNotes = AyatNotes::whereIn('scholar_id', $scholarIds)->get()->toArray();


            $list = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $key => $value) {
                    $ayatTranslations = $value['ayats_translations_by_view'];
                    usort($ayatTranslations, function($item1, $item2){
                        return $item2['language_id'] <=> $item1['language_id'];
                    });
                    foreach ($ayatTranslations as $ikey => $val) {
                        $note = array_filter($ayatNotes, function($item) use($val){
                            return ($item['ayat_id']==$val['ayat_id'] && $item['scholar_id']==$val['scholar_id'] && $item['language_id']==$val['language_id']);
                        });
                        $note = reset($note);
                        $ayatTranslations[$ikey]['note'] = $note['note_file'] ?? '';
                    }

                    $list[] = [
                        'id' => $value['id'],
                        'surah_id' => $value['surah_id'],
                        'ayatNo' => $value['ayatNo'],
                        'arabic' => $value['arabic'],
                        'arabicSimple' => $value['arabic_simple'],
                        'ayat_translations' => $ayatTranslations
                    ];

                    if(!isset($settings['showNonArabic']) || $settings['showNonArabic']==false){
                        unset($list[$key]['arabicSimple']);
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => 'Scholar Translations Retrieved Successfully',
                'total_records' => $total,
                'filters' => $input,
                'scholars' => $scholars,
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    private function convertLabelToCamelCase($array){
        $finalArray = [];
        foreach ($array as  $value) {
            $finalArray[] = [
                "title"=> $value['title'],
                "dataIndex"=> lcfirst(str_replace("_", "", ucwords($value['dataIndex'], "_"))),
                "key"=> lcfirst(str_replace("_", "", ucwords($value['key'], "_"))),
                "note"=> lcfirst(str_replace("_", "", ucwords($value['note'], "_")))
            ];
        }
        return $finalArray;
    }

    public function publishedTopics(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'storyId' => 'required',
                'sortType' => 'sometimes'
            ]);
            if($validator->fails()){
                return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
            }
            $input = $validator->valid();

            $row = Story::find($input['storyId'])->toArray();
            $sections = json_decode($row['sections'], true);
            $ayats = [];
            foreach ($sections as $key => $value) {
                foreach ($value['ayats'] as $k => $v) {
                    $ayats[] = [
                        'ayat_id' => $v['id'],
                        'sequence' => $v['sequence']
                    ];
                }
            }
            
            $ayatIds = array_unique(array_column($ayats, 'ayat_id')) ?? [];
            $settings = $this->getSettingsData();
            $result = InfoData::query()->RequestFilters($request)->select('id', 'surah_id', 'ayatNo', 'arabic')->with([
                'ayatsTranslationsByView' => fn ($q) => $q->OfSettingsFilter($settings)->where('translation','!=',''),
                'words' => fn ($q) => $q->with([
                    'otherTranslationInfo'=> fn($infoQuery) => $infoQuery->OfSpecialScholars($settings['wordScholarsIDs'])->with('referredUser:id,name'),
                    'wordTranslationsByView'=> fn ($newq) => $newq->OfSettingsFilter($settings)])->with('rootWordMeaning')
                ]
            )->whereIn('id', $ayatIds)->get()->toArray();
            // return $result;
            
            $ayatNotes = AyatNotes::whereIn('scholar_id',$settings['ayatScholarsIDs'])->get()->toArray();
            $wordNotes = WordNotes::whereIn('scholar_id', $settings['wordScholarsIDs'])->get()->toArray();
            $list = [];
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    $ayatTranslations = $value['ayats_translations_by_view'];
                    // ARRANGE AYAT NOTES INSIDE AYAT TRANSLATIONS
                    if(!empty($value['ayats_translations_by_view'])){
                        foreach ($value['ayats_translations_by_view'] as $ikey => $val) {
                            $note = array_filter($ayatNotes, function($item) use($val){
                                return ($item['ayat_id']==$val['ayat_id'] && $item['language_id']==$val['language_id'] && $item['scholar_id']==$val['scholar_id']);
                            });
                            $note = reset($note);
                            $ayatTranslations[$ikey]['note'] = $note['note_file'] ?? '';
                        }
                    }
                    $sequence = array_filter($ayats, function($item) use($value){
                        return ($item['ayat_id']==$value['id']);
                    });
                    $sequence =  reset($sequence);
                    $list[] = [
                        "id" => $value['id'],
                        'sequence' => $sequence['sequence'] ?? '',
                        "surahId" => $value['surah_id'],
                        "ayatId" => $value['ayatNo'],
                        "arabic" => $value['arabic'],
                        'ayatTranslations' => Utility::convertKeysToCamelCase($ayatTranslations),
                        'words' => $this->formatWordData($value['words'], $settings, $wordNotes)
                    ];
                }
            }
            // return $list;
            // SORT LIST DATA BY AYAT SEQUENCE
            if(isset($input['sortType']) && $input['sortType'] !="ayatSequence"){
                usort($list, function($a,$b){
                    return $a['sequence'] <=> $b['sequence'];
                });
            }
            // REMOVE SEQUENCE PROPERTY FROM LIST ARRAY
            array_walk( $list, function(&$a){unset($a['sequence']);});
            
            $response = [
                'success' => true,
                'message' => 'Published Topics Retrieved Successfully',
                'total_records' => count($ayatIds),
                'filters' => $request->all(),
                'story_title' => $row['title'],
                'columns' => $this->convertLabelToCamelCase($settings['allowedColumns']),
                'list' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    public function updateContemporaryGrammar(Request $request){
        $validator = Validator::make($request->all(), [
            'previousWord' => 'required',
            'newWord' => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        }
        $input = $validator->valid();

        try {
            Words::where('contemporary_grammar', $input['previousWord'])->update(['contemporary_grammar' => $input['newWord']]);
            $response = ['success' => true, 'message' => 'Contemporary Grammar Updated Successfully', 'data' => $input];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }
        return response()->json($response);
    }
}
