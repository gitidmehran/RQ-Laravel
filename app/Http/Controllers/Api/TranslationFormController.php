<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\Languages;
use App\Models\OtherWordTranslationInfo;
use App\Models\User;
use App\Models\Words;
use App\Models\WordsTranslations;
use App\Utility\Utility;
use DB, Auth, Validator;
use Illuminate\Http\Request;

class TranslationFormController extends Controller
{

    public function create(Request $request)
    {
        $input = $request->all();
        try {
            $check = InfoData::with([
                'ayatsTranslations' => fn($q) => $q->OfSpecialScholars($input['scholarId'])
            ])->where(['surah_id' => $input['surahId'], 'ayatNo' => $input['ayatId']])->first()->toArray();

            if (!empty($check['ayats_translations'])) {
                return response()->json(['success' => false, 'message' => "You have already added,You Can't add Duplicate Translation."]);
            }
            // return response()->json($check);die;
            $row = $this->arrangeGetData($input);
            // return res

            $response = [
                'success' => true,
                'message' => 'Add Translation Form Information Retrieved Successfully',
                'quranicLexicons' => Config('constants.references'),
                'row' => $row
            ];
        } catch (Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $data = $this->arrangeRequestData($input);

            $ayatTranslationData = $data['ayatTranslationData'];
            $wordTranslations = $data['wordTranslations'];
            $otherWordTranslationInfo = $data['otherWordTranslationInfo'];
            // return $otherWordTranslationInfo;
            DB::beginTransaction();
            if (!empty($ayatTranslationData))
                AyatsTranslation::insert($ayatTranslationData);

            if (!empty($wordTranslations))
                WordsTranslations::insert($wordTranslations);

            if (!empty($otherWordTranslationInfo))
                OtherWordTranslationInfo::insert($otherWordTranslationInfo);

            DB::commit();
            $response = [
                'success' => true,
                'message' => 'Translation Added Successfully'
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    public function edit(Request $request)
    {
        $input = $request->all();

        try {
            $user = Auth::user();
            // return response()->json($user);
            $settings = $user->user_settings;
            $list = $this->arrangeGetData($input);

            $response = [
                'success' => true,
                'user_setting' => $settings,
                'message' => 'Edit Translation Form Information Retrieved Successfully',
                'quranicLexicons' => Config('constants.references'),
                'row' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    public function update(Request $request)
    {
        try {
            $input = $request->all();
            $data = $this->arrangeRequestData($input);
            //return response()->json($data);
            $ayatTranslationData = $data['ayatTranslationData'];
            $wordTranslations = $data['wordTranslations'];
            // return $wordTranslations;
            $otherWordTranslationInfo = $data['otherWordTranslationInfo'];
            $wordIds = array_column($input['words'], 'wordId');
            $otherInfo = OtherWordTranslationInfo::whereIn('word_id', $wordIds)->orWhereIn('reference_word', $wordIds)->where('scholar_id', $input['scholarId'])->get()->toArray();
            $otherInfoWordIDs = array_unique(array_column($otherInfo, 'word_id'));


            DB::beginTransaction();
            if (!empty($ayatTranslationData)) {
                $ayatIds = array_unique(array_column($ayatTranslationData, 'ayat_id'));
                AyatsTranslation::where('scholar_id', $input['scholarId'])->whereIn('ayat_id', $ayatIds)->delete();
                AyatsTranslation::insert($ayatTranslationData);
            }

            if (!empty($wordTranslations)) {
                WordsTranslations::where('scholar_id', $input['scholarId'])->whereIn('word_id', $otherInfoWordIDs)->delete();
                WordsTranslations::insert($wordTranslations);
            }

            if (!empty($otherWordTranslationInfo)) {
                OtherWordTranslationInfo::where('scholar_id', $input['scholarId'])->whereIn('word_id', $otherInfoWordIDs)->delete();
                OtherWordTranslationInfo::insert($otherWordTranslationInfo);
            }

            DB::commit();
            $response = [
                'success' => true,
                'message' => 'Translation Updated Successfully'
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    private function arrangeRequestData($input)
    {

        $languages = Languages::select('id', 'name')->get()->toArray();

        $ayatTranslationData = Utility::convertKeysToSnakeCase($input['ayatTranslations']);
        array_walk($ayatTranslationData, function (&$a) {
            if (isset($a['disabled']))
                unset($a['disabled']); });

        $wordTranslations = $otherWordTranslationInfo = [];
        if (!empty($input['words'])) {
            foreach ($input['words'] as $key => $value) {
                // ARRANGE OTHER WORD INFO
                $otherWordTranslationInfo[] = [
                    'word_id' => $value['wordId'],
                    'scholar_id' => $input['scholarId'],
                    "quranic_lexicon" => $value['quranicLexicon'],
                    "quranic_lexicon_type" => $value['quranicLexiconType'],
                    "quranic_lexicon_number" => $value['quranicLexiconNumber'],
                    "addresser" => $value['addresser'],
                    "addressee" => $value['addressee'],
                    "disable" => (int) $value['disable'],
                    'reference_word' => $value['referenceWord']
                ];

                if (!empty($value['relatedWords'])) {
                    foreach ($value['relatedWords'] as $ival) {
                        $otherWordTranslationInfo[] = [
                            'word_id' => (int) $ival,
                            "scholar_id" => $input['scholarId'],
                            "quranic_lexicon" => "",
                            "quranic_lexicon_type" => "Referred",
                            "quranic_lexicon_number" => $value['quranicLexiconNumber'],
                            "addresser" => $value['addresser'],
                            "addressee" => $value['addressee'],
                            "disable" => (int) true,
                            "reference_word" => $value['wordId']
                        ];
                    }
                }

                // ARRANGE WORD TRANSLATIO IF DISABLE NOT EQUAL TO FALSE
                if ($value['disable'] == false) {
                    foreach ($languages as $k => $v) {
                        $smallName = strtolower($v['name']);
                        if (isset($value[$smallName])) {
                            $wordTranslations[] = [
                                'word_id' => $value['wordId'],
                                'scholar_id' => $input['scholarId'],
                                'language_id' => $v['id'],
                                'translation' => $value[$smallName],
                            ];
                        }

                        if (!empty($value['relatedWords'])) {
                            foreach ($value['relatedWords'] as $ival) {
                                $wordTranslations[] = [
                                    'word_id' => (int) $ival,
                                    'scholar_id' => $input['scholarId'],
                                    'language_id' => $v['id'],
                                    'translation' => $value[$smallName],
                                ];
                            }
                        }
                    }
                }
            }
        }
        usort($wordTranslations, fn($a, $b) => $a['word_id'] <=> $b['word_id']);
        usort($otherWordTranslationInfo, fn($a, $b) => $a['word_id'] <=> $b['word_id']);

        return [
            'ayatTranslationData' => $ayatTranslationData,
            'wordTranslations' => $wordTranslations,
            'otherWordTranslationInfo' => $otherWordTranslationInfo
        ];
    }
    public function getRelatedWords(Request $request)
    {
        $input = $request->all();
        $wID = $input['wordId'];
        try {
            $word = Words::where('id',$input['wordId'])->first()->toArray();
            $relatedWords = Words::WithFilters($word['word'],null,$input['wordId'])->get()->toArray();

            $data = OtherWordTranslationInfo::where('scholar_id', Auth::id())->where(function($query12) use($wID){
                $query12->where('word_id',$wID)
                ->orWhere('reference_word',$wID);
            })->get()->toArray();

            //return response()->json($data);
            $query = new OtherWordTranslationInfo();
            if (Auth::user()->role == 3) {
                $query = $query->where('scholar_id', Auth::id());
            }
            $result = $query->get()->toArray();
            //return response()->json($result);
            $wordIds = !empty($result)?array_unique(array_column($result, "word_id")):[];
            $check = !empty($data)?array_unique(array_column($data, "word_id")):[];
            
           //return response()->json($check);
            $list = [];
            if (!empty($relatedWords)) {
                //return response()->json($relatedWords);
                foreach ($relatedWords as  $value) {
                   
                    $list[] = [
                        'id' => $value['id'],
                        'word' => $value['word'],
                        'surah_no' => $value['surah_no'],
                        'ayat_no' => $value['ayat_no'],
                        'reference' => $value['reference'],
                        'checked' => in_array($value['id'], $check),
                        //'diable' => in_array($value['id'], $wordIds),
                        'disable' => in_array($value['id'], $check) === true ? false : in_array($value['id'], $wordIds),
                    ];
                }
            }
            $response = [
                'success' => true,
                'message' => 'Translation Data Retrieved Successfully',
                'totalWords' => count($list),
                'list' => $list
            ];
            $response = Utility::convertKeysToCamelCase($response);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
    public function WordsRizwan(Request $request){
        $input = $request->all();
        try {
            $wordId121 = $input['wordId'];
            

            $langId['urdu'] = $input['urdu'];
            $langId['english'] = $input['english'];
            //return response()->json($langId);
            //$referredWordRizwan = array_column($langId,);
            //return response()->json($langId);
            $authId = Auth::id();
            //$transData = $input['translation'];
            $referredWordRizwan = [] ;
            //return response()->json($authId);
            // $word = OtherWordTranslationInfo::where('word_id',$input['wordId'])->where('scholar_id',$authId)->first()->toArray();
            // $wordId = $word['word_id'];



            $referredWordRizwan = OtherWordTranslationInfo::where('scholar_id',$authId)->where(function($query)use($wordId121) {
                $query->where('word_id',$wordId121)
                ->orWhere('reference_word',$wordId121);
                })->select('word_id')->get()->toArray();
                
            $referredWordRizwan = array_column($referredWordRizwan,'word_id');

            
            $wordTransTable = WordsTranslations::whereIn('word_id',$referredWordRizwan)
                ->where('scholar_id', $authId)
                ->delete();
                $referredWordRizwan = OtherWordTranslationInfo::where('scholar_id',$authId)->where(function($query)use($wordId121) {
                    $query->where('word_id',$wordId121)
                    ->orWhere('reference_word',$wordId121);
                    })->delete();
            $values = array('word_id' => $wordId121,'quranic_lexicon' => $input['quranicLexicon'],'quranic_lexicon_type' => 'Source' ,'disable' => 0, 'scholar_id' => $authId, 'addressee' => $input['addressee'], 'addresser' => $input['addresser']);
            OtherWordTranslationInfo::insert($values);

               
            if(!empty($input['relatedWords'])){
                foreach($input['relatedWords'] as $dataRW){
                   // return response()->json($dataRW);
                    $values1 = array('word_id' => $dataRW,'quranic_lexicon_type' => 'Referred' ,'disable' => 1, 'scholar_id' => $authId, 'addressee' => $input['addressee'], 'addresser' => $input['addresser'] , 'reference_word' => $wordId121);
                    OtherWordTranslationInfo::insert($values1);
                }
            }
       // return response()->json("OtherWordTranslationInfo table insert data");
            $referredWordRizwan = OtherWordTranslationInfo::where('scholar_id',$authId)->where(function($query)use($wordId121) {
                                    $query->where('word_id',$wordId121)
                                    ->orWhere('reference_word',$wordId121);
                                    })->select('word_id')->get()->toArray();

            $referredWordRizwan = array_column($referredWordRizwan,'word_id');
            //return response()->json($referredWordRizwan);
            
            foreach($referredWordRizwan as $k => $wid){
            foreach($langId as $key => $transData){
                //return response()->json($key);
                if($key == 'urdu'){
                    $id = 1;
                }else{
                    $id = 2;
                }
                
                $valuesWT = array('word_id' => $wid, 'scholar_id'=> $authId, 'language_id' =>$id, 'translation'=> $transData);
                $wordTransTable = WordsTranslations::insert($valuesWT);}
                    // $wordTransTable = WordsTranslations::whereIn('word_id',$referredWordRizwan)
                    //     ->where('scholar_id', $authId)
                    //     ->where('language_id',$id)
                    //     ->update([
                    //         'translation'=> $transData 
                    //     ]);
                

            }
            
            
            
            
            $response = [
                'success' => true,
                'message' => 'Updated Successfully'
                // 'totalWords' => count($list),
                // 'list' => $list
            ];
            //$response = Utility::convertKeysToCamelCase($response);
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=> $e->getMessage());
        }
        return response()->json($response);
    }


    public function findWordsInfoRizwan(Request $request)
    {
        $input = $request->all();
        $scholarId = Auth::id();
        $scholar = User::find($scholarId)->toArray();
        $scholarAllowedLanguages = explode(',', $scholar['translated_language']);
        $languages = Languages::select('id', 'name')->get()->toArray();
        try {
            $wordId12 = $input['wordId'];
            $authId = Auth::id();

            $user = User::where('id', $authId)->first();
            // return response()->json($user);
            $settings = $user->translated_language;

            //return response()->json($settings);
            $referredWordRizwanData = [];

            $referredWordRizwanData = OtherWordTranslationInfo::where('scholar_id', $authId)->where(function ($query) use ($wordId12) {
                $query->where('word_id', $wordId12)
                    ->orWhere('reference_word', $wordId12);
            })->get()->toArray();

            $arrSourceWords = [];
            foreach ($referredWordRizwanData as $product) {
                if ($product['quranic_lexicon_type'] == 'Source') {
                    $word1 = Words::where('id', $product['word_id'])->first();
                    $arrSourceWords['word'] = $word1->word;
                    $arrSourceWords['word_id'] = $product['word_id'];
                    $arrSourceWords['addresser'] = $product['addresser'];
                    $arrSourceWords['addressee'] = $product['addressee'];
                    $arrSourceWords['disable'] = $product['disable'] == "0"?false:true;
                    $arrSourceWords['quranic_lexicon'] = $product['quranic_lexicon'];
                    $arrSourceWords['quranic_lexicon_type'] = $product['quranic_lexicon_type'];
                    foreach ($languages as $lang) {
                        $languageId = $lang['id'];
                        $languageName = strtolower($lang['name']);
                        $arrSourceWords[$languageName . 'Disabled'] = !in_array($languageId, $scholarAllowedLanguages);
                    }

                    $wordTransTable = WordsTranslations::select('translation')->where('word_id', $product['word_id'])
                        ->where('scholar_id', $authId)
                        ->where('language_id', 2)
                        ->first();
                    $arrSourceWords['english'] = @$wordTransTable->translation; //->translation;

                    $wordTransTable = WordsTranslations::select('translation')->where('word_id', $product['word_id'])
                        ->where('scholar_id', $authId)
                        ->where('language_id', 1)
                        ->first();
                    $arrSourceWords['urdu'] = @$wordTransTable->translation;
                }
            }
            $arrReferredWords = [];
            foreach ($referredWordRizwanData as $product) {
                if ($product['quranic_lexicon_type'] == 'Referred')
                    $arrReferredWords[] = $product['word_id'];
            }

            $arrSourceWords['relatedWords'] = $arrReferredWords;
            // $list['wordInfo'] = $arrSourceWords;
            // $list['reletedWords'] =$arrReferredWords;
            $data = array($arrSourceWords);


            $response = [
                'success' => true,
                'message' => 'Data Find Successfully',
                // 'totalWords' => count($arrSourceWords),
                'list' => $data, //$referredWordRizwanData
                // 'reletedWords' => $arrReletedWords

            ];
            $response = Utility::convertKeysToCamelCase($response);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    //------ Related Phrases ------
    public function getRelatedPhraseWords(Request $request)
    {
        $input = $request->all();
        try {
            $params1 = $request->get('params1');
            $wordids = explode(',', $input['wordId']);
            $scholarId = !empty($request->input('params2')) ? $request->input('params2') : Auth::id();

            $words = Words::WithFilters(null, $wordids, null)->get()->toArray();
            $words_name = implode(' ', array_column($words, 'word'));
            $words_ = array_column($words, 'word');
            // Split out first array element and get References
            $single_word = reset($words);

            $t_w = count($words_);
            if ($t_w > 1) {

                $phraseWords = $this->arrangePhraseWords($words_, $t_w);

                // echo $phraseWords;
                // exit;

                $words = [];
                $wordId = $single_word['id'];
                if (!empty($params1)) {
                    $references = array_filter($phrase_word_reference, fn($data) => $data['word_id'] == $wordId);
                    $words = array_column($references, 'phrase_word_id');
                }

                $phraseWords = json_decode($phraseWords, true);

                $list = [];
                if (!empty($phraseWords)) {
                    foreach ($phraseWords as $phraseWord) {
                        $phrase = '';
                        $id = '';
                        foreach ($phraseWord as $key => $value) {
                            if ($key === key($phraseWord)) {
                                $id = $value['id'];
                            }
                            $phrase .= $value['word'] . ' ';
                        }

                        $list[] = [
                            'id' => $id,
                            'word' => trim($phrase),
                            'surah_no' => $value['surah_no'],
                            'ayat_no' => $value['ayat_no'],
                            'reference' => $value['reference']
                        ];
                    }
                }

                $response = [
                    'success' => true,
                    'message' => 'Phrases Retrieved Successfully',
                    'totalWords' => count($phraseWords),
                    'list' => $list
                ];

                $response = Utility::convertKeysToCamelCase($response);
            }

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
    //------

    private function arrangeGetData($input)
    {
        $scholarId = $input['scholarId'];
        $scholar = User::find($scholarId)->toArray();
        $scholarAllowedLanguages = explode(',', $scholar['translated_language']);
        $languages = Languages::select('id', 'name')->get()->toArray();

        \DB::enableQueryLog();
        $row = InfoData::with([
            'ayatsTranslations' => fn($q) => $q->OfSpecialScholars($scholarId),
            'words' => fn($q) => $q->with([
                'otherTranslationInfo' => fn($otherinfoquery) => $otherinfoquery->OfSpecialScholars($scholarId)->with([
                    'referredword:id,surah_no,ayat_id,ayat_no,reference,word',
                    'referredUser:id,name'
                ]),
                'translations' => fn($newquery) => $newquery->OfSpecialScholars($scholarId),
                // 'relatedWords'=>fn($q)=> $q->OfSpecialScholars($scholarId)
                'relatedWords' => fn($newquery) => $newquery->OfSpecialScholars($scholarId),

            ])

        ])->where(['surah_id' => $input['surahId'], 'ayatNo' => $input['ayatId']])->first()->toArray();
        // dd(\DB::getQueryLog());die;

        // FORMAT AYAT TRANSLATIONS

        $ayatTranslations = [];
        $dbAyatTranslations = $row['ayats_translations'];
        foreach ($languages as $key => $value) {
            $ayatId = $row['id'];
            $languageId = $value['id'];

            $translation = array_filter($dbAyatTranslations, function ($item) use ($ayatId, $languageId, $scholarId) {
                return ($item['ayat_id'] == $ayatId && $item['scholar_id'] == $scholarId && $item['language_id'] == $languageId);
            });
            $translation = reset($translation);

            $ayatTranslations[] = [
                'ayat_id' => (int) $ayatId,
                'language_id' => (int) $languageId,
                'scholar_id' => (int) $scholarId,
                'translation' => @$translation['translation'] ?? '',
                'disabled' => !in_array($languageId, $scholarAllowedLanguages)
            ];
        }
        $row['ayats_translations'] = $ayatTranslations;

        // Arrange Word Data
        $words = [];
        foreach ($row['words'] as $key => $val) {
            $words[$key] = [
                'reference' => $val['surah_no'] . ':' . $val['ayat_no'] . ':' . $val['reference'],
                'word_id' => $val['id'],
                'word' => $val['word'],
            ];
            $translations = $val['translations'];
            foreach ($languages as $v) {
                $languageId = $v['id'];
                $languageName = strtolower($v['name']);

                $translation = array_filter($translations, function ($item) use ($languageId, $scholarId) {
                    return ($item['language_id'] == $languageId && $item['scholar_id'] == $scholarId);
                });
                $translation = reset($translation);
                $translation = @$translation['translation'] ?? '';
                $words[$key][$languageName] = $translation;
                $words[$key][$languageName . 'Disabled'] = !in_array($languageId, $scholarAllowedLanguages);
            }
            $otherTranslationInfo = reset($val['other_translation_info']);
            $words[$key]['quranic_lexicon'] = @$otherTranslationInfo['quranic_lexicon'] ?? '';
            $words[$key]['quranic_lexicon_type'] = @$otherTranslationInfo['quranic_lexicon_type'] ?? '';
            $words[$key]['quranic_lexicon_number'] = @$otherTranslationInfo['quranic_lexicon_number'] ?? '';
            $words[$key]['addresser'] = @$otherTranslationInfo['addresser'] ?? '';
            $words[$key]['addressee'] = @$otherTranslationInfo['addressee'] ?? '';
            $words[$key]['disable'] = (bool) @$otherTranslationInfo['disable'] ?? false;
            $words[$key]['reference_word'] = @$otherTranslationInfo['reference_word'] ?? '';

            // Checking for Reference Word
            $refferdWordDetail = [];
            $referenceWord = @$val['other_translation_info']['referredword'] ?? '';
            if (!empty($referenceWord)) {
                $refferdWordDetail['reference'] = $referenceWord['surah_no'] . ':' . $referenceWord['ayat_no'] . ':' . $referenceWord['reference'];
                $refferdWordDetail['word'] = $referenceWord['word'];
                $refferdWordDetail['author'] = @$val['other_translation_info']['referred_user']['name'] ?? '';
            }
            $words[$key]['referred_word_detail'] = !empty($refferdWordDetail) ? $refferdWordDetail : (object) $refferdWordDetail;

            // Getting Related Word Detail
            $relatedWords = !empty($val['related_words']) ? array_unique(array_column($val['related_words'], 'word_id')) : [];
            $words[$key]['related_words'] = $relatedWords;
        }
        $row['words'] = $words;
        return Utility::convertKeysToCamelCase($row);

    }

    public function destroy(Request $request, $id)
    {
        try {
            $authId = Auth::id();
            $ayat = InfoData::with('words')->find($id)->toArray();

            $word_ids = array_column($ayat['words'], 'id');
            DB::beginTransaction();
            if (!empty($word_ids)) {
                WordsTranslations::where('scholar_id', $authId)->whereIn('word_id', $word_ids)->delete();
                OtherWordTranslationInfo::where('scholar_id', $authId)->whereIn('word_id', $word_ids)->delete();
            }
            AyatsTranslation::where('scholar_id', $authId)->where('ayat_id', $id)->delete();
            DB::commit();
            $response = array('success' => true, 'message' => "Ayat Translation Removed Successfully");
        } catch (\Exception $e) {
            DB::rollback();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    public function removeSingleWordTranslation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'wordId' => 'required',
                'row' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
            }

            $input = $validator->valid();
            $wordId = $input['wordId'];
            $authId = Auth::id();
            $row = $input['row'];
            DB::beginTransaction();
            if ($row['disable'] == true) {
                WordsTranslations::where('word_id', $wordId)->where('scholar_id', $authId)->delete();
                OtherWordTranslationInfo::where([
                    'word_id' => $wordId,
                    'scholar_id' => $authId,
                    'quranic_lexicon_type' => 'Referred'
                ])->delete();
            } else {
                $otherInfo = OtherWordTranslationInfo::where('word_id', $wordId)->orWhere('reference_word', $wordId)->where('scholar_id', $authId)->get()->toArray();
                $wordIds = array_unique(array_column($otherInfo, 'word_id'));
                WordsTranslations::whereIn('word_id', $wordIds)->where('scholar_id', $authId)->delete();
                OtherWordTranslationInfo::whereIn('word_id', $wordIds)->where('scholar_id', $authId)->delete();
            }

            $row["urdu"] = "";
            $row["english"] = "";
            $row["quranicLexicon"] = "";
            $row["quranicLexiconType"] = "";
            $row["quranicLexiconNumber"] = "";
            $row["addresser"] = "";
            $row["addressee"] = "";
            $row["disable"] = false;
            $row["referenceWord"] = "";
            $row["referredWordDetail"] = "{}";
            $row["relatedWords"] = [];
            DB::commit();
            $response = [
                'success' => true,
                'message' => 'Word Translation Updated Successfully',
                'word_id' => $wordId,
                'row' => $row
            ];
        } catch (\Exception $e) {
            DB::rollback();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }

    //-------------
    private function arrangePhraseWords($words_, $t_w)
    {
        $t = DB::table('words')->whereIn('word', $words_)->orderBy('id')->get()->toArray();

        // echo "<pre>"; print_r( $t ); exit;

        $c = 0;
        $n = array();

        foreach ($t as $key => $value) {
            $c = (int) $key + 1;
            if (isset($t[$c])) {

                switch ($t_w) {
                    case '2':
                        if (
                            $value->word == $words_[0] &&
                            $t[$c]->word == $words_[1] &&
                            $value->ayat_id == $t[$c]->ayat_id &&
                            $value->id == $t[$c]->id - 1
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c]
                            );
                        }
                        break;
                    case '3':
                        if (
                            $value->word == $words_[0] &&
                            $t[$c]->word == $words_[1] &&
                            $t[$c + 1]->word == $words_[2] &&
                            $value->ayat_id == $t[$c]->ayat_id &&
                            $value->id == $t[$c]->id - 1 &&
                            $t[$c]->id == $t[$c + 1]->id - 1
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c],
                                $t[$c + 1]->id => $t[$c + 1]
                            );
                        }
                        break;
                    case '4':
                        if (
                            $value->word == $words_[0] &&
                            $t[$c]->word == $words_[1] &&
                            $t[$c + 1]->word == $words_[2] &&
                            $t[$c + 2]->word == $words_[3] &&
                            $value->ayat_id == $t[$c]->ayat_id &&
                            $value->id == $t[$c]->id - 1 &&
                            $t[$c]->id == $t[$c + 1]->id - 1 &&
                            $t[$c + 1]->id == $t[$c + 2]->id - 1
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
                            $value->word == $words_[0] &&
                            $t[$c]->word == $words_[1] &&
                            $t[$c + 1]->word == $words_[2] &&
                            $t[$c + 2]->word == $words_[3] &&
                            $t[$c + 3]->word == $words_[4] &&
                            $value->ayat_id == $t[$c]->ayat_id &&
                            $value->id == $t[$c]->id - 1 &&
                            $t[$c]->id == $t[$c + 1]->id - 1 &&
                            $t[$c + 1]->id == $t[$c + 2]->id - 1 &&
                            $t[$c + 2]->id == $t[$c + 3]->id - 1
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
                    case '6':
                        if (
                            $value->word == $words_[0] &&
                            $t[$c]->word == $words_[1] &&
                            $t[$c + 1]->word == $words_[2] &&
                            $t[$c + 2]->word == $words_[3] &&
                            $t[$c + 3]->word == $words_[4] &&
                            $t[$c + 4]->word == $words_[5] &&
                            $value->ayat_id == $t[$c]->ayat_id &&
                            $value->id == $t[$c]->id - 1 &&
                            $t[$c]->id == $t[$c + 1]->id - 1 &&
                            $t[$c + 1]->id == $t[$c + 2]->id - 1 &&
                            $t[$c + 2]->id == $t[$c + 3]->id - 1 &&
                            $t[$c + 3]->id == $t[$c + 4]->id - 1
                        ) {
                            $n[$value->ayat_id] = array(
                                $value->id => $value,
                                $t[$c]->id => $t[$c],
                                $t[$c + 1]->id => $t[$c + 1],
                                $t[$c + 2]->id => $t[$c + 2],
                                $t[$c + 3]->id => $t[$c + 3],
                                $t[$c + 4]->id => $t[$c + 4]
                            );
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
        return json_encode($n);
    }
}