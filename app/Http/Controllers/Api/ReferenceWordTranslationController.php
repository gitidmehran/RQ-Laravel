<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\OtherWordTranslationInfo;
use App\Models\User;
use App\Models\Words;
use App\Models\WordsTranslations;
use App\Utility\Utility;
use Auth;
use DB;
use Illuminate\Http\Request;

class ReferenceWordTranslationController extends Controller
{
    public function index(Request $request)
    {
        // try {
            $input = $request->all();
            $result = OtherWordTranslationInfo::with([
                'word.translations' => fn($q)=> $q->OfSpecialScholars($input['scholarId']),
                'word.relatedWords' => fn($q)=> $q->OfSpecialScholars($input['scholarId'])->with('word')
            ])
            ->where('scholar_id', $input['scholarId'])
            ->where('quranic_lexicon', 'like', '%_al_word')
            ->get()->toArray();
        // ->where(['scholar_id'=> $input['scholarId'],'quranic_lexicon'=> 'single_al_word'])->get()->toArray();

        $languages = Languages::select('id', 'name')->get()->toArray();

        $list = [];
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $word = $value['word'];
                $list[$key]['id'] = $value['id'];
                $list[$key]['word_id'] = $word['id'];
                $list[$key]['surah_no'] = $word['surah_no'];
                $list[$key]['ayat_no'] = $word['ayat_no'];
                $list[$key]['reference'] = $word['surah_no'] . ':' . $word['ayat_no'] . ':' . $word['reference'];
                $list[$key]['word'] = $word['word'];

                if (isset($value['quranic_lexicon_number']) && $value['quranic_lexicon_number'] >= 2) {
                    for ($i = 1; $i < $value['quranic_lexicon_number']; $i++) {
                        $next_word_id = $value['word_id'] + $i;
                        $next_word = Words::where('id', $next_word_id)->get()->toArray();
                        $next_word = reset($next_word);
                        $list[$key]['word'] .= ' ' . $next_word['word'];
                    }
                }

                $wordTranslations = $word['translations'];
                foreach ($languages as $val) {
                    $translation = array_filter($wordTranslations, function ($item) use ($val) {
                        return ($item['language_id'] == $val['id']);
                    });
                    $translation = reset($translation);
                    $list[$key][strtolower($val['name'])] = @$translation['translation'] ?? '';
                }


                $list[$key]['um_ul_kitaab'] = $value['um_ul_kitaab'];
                $list[$key]['addresser'] = $value['addresser'];
                $list[$key]['addressee'] = $value['addressee'];

                $relatedWords = [];
                if (!empty($word['related_words'])) {
                    foreach ($word['related_words'] as $val) {

                        $temp_word = $val['word']['word'] ?? '';
                        if (isset($val['quranic_lexicon_number']) && $val['quranic_lexicon_number'] >= 2) {
                            for ($i = 1; $i < $val['quranic_lexicon_number']; $i++) {
                                $r_next_word_id = $val['word_id'] + $i;
                                $r_next_word = Words::where('id', $r_next_word_id)->get()->toArray();
                                $r_next_word = reset($r_next_word);
                                $temp_word .= ' ' . @$r_next_word['word'] ?? '';
                            }
                        }

                        $relatedWords[] = [
                            'id' => $val['word']['id'] ?? '',
                            'surah_no' => $val['word']['surah_no'] ?? '',
                            'ayat_no' => $val['word']['ayat_no'] ?? '',
                            'reference' => $val['word']['reference'] ?? '',
                            'word' => $temp_word,
                            'scholar_id' => $val['scholar_id'],
                            'disable' => $val['disable'],
                            'quranic_lexicon_type' => $val['quranic_lexicon_type']
                        ];
                    }
                }
                $list[$key]['relatedWords'] = $relatedWords;
            }
        }

        $scholars = User::where('role', 3)->select('id', 'name')->get()->toArray();

        $response = [
            'success' => true,
            'message' => 'Reference Word Translations Retrieved Successfully',
            'letters' => \Config('constants.word_characters'),
            'total' => count($list),
            'list' => Utility::convertKeysToCamelCase($list),
            'scholars' => $scholars
        ];
        // } catch (\Exception $e) {
        //     $response = array('success'=> false, 'message'=> $e->getMessage());
        // }
        return response()->json($response);
    }

    public function update(Request $request)
    {
        try {
            $input = $request->all();
            $scholarId = $input['scholarId'];
            $inputRow = $input['row'];
            $row = [
                "um_ul_kitaab" => $inputRow['umUlKitaab'],
                'addresser' => $inputRow['addresser'],
                'addressee' => $inputRow['addressee']
            ];

            $relatedWordIds = $translationWordIDs = array_column($inputRow['relatedWords'], 'id');
            $languages = Languages::select('id', 'name')->get()->toArray();

            array_push($translationWordIDs, $inputRow['wordId']);
            $translationWordIDs = !empty($translationWordIDs) ? array_unique($translationWordIDs) : [];

            $translations = [];
            foreach ($languages as $key => $value) {
                $translations[] = [
                    'word_id' => $inputRow['wordId'],
                    'scholar_id' => $scholarId,
                    'language_id' => $value['id'],
                    'translation' => $inputRow[strtolower($value['name'])]
                ];

                if (!empty($relatedWordIds)) {
                    foreach ($relatedWordIds as $val) {
                        $translations[] = [
                            'word_id' => $val,
                            'scholar_id' => $scholarId,
                            'language_id' => $value['id'],
                            'translation' => $inputRow[strtolower($value['name'])]
                        ];
                    }
                }
            }

            DB::beginTransaction();
            WordsTranslations::whereIn('word_id', $translationWordIDs)->where('scholar_id', $scholarId)->delete();
            WordsTranslations::insert($translations);
            OtherWordTranslationInfo::where('id', $inputRow['id'])->update($row);

            if (!empty($relatedWordIds)) {
                OtherWordTranslationInfo::where('scholar_id', $scholarId)->whereIn('word_id', $relatedWordIds)->update($row);
            }
            DB::commit();
            $response = array('success' => true, 'message' => 'Reference Word Translations Updated Successfully');
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    public function delete(Request $request)
    {
        try {
            // word jis sy wo update karna cahata h 
            // relatedWordIds wo ho ga jo checked hn un word ke id send karni h 
            // oldRelatedWordIds pahly sy page load hony py jo ids the wo send karni hn 

            $languages = Languages::all();
            $input = $request->all();
            $scholarId = Auth::id();
            $wordId = $input['wordId'];
            $relatedWordIds = $input['relatedWordIds'];
            $oldRelatedWordIds = $input['oldRelatedWordIds'];
            $otherTranslationInfo = OtherWordTranslationInfo::where('reference_word', $wordId)->where('scholar_id', $scholarId)->first()->toArray();
            $translation = WordsTranslations::where('word_id', $wordId)->where('scholar_id', $scholarId)->get()->toArray();

            $wordTranslation = $otherTranslation = [];

            if (!empty($relatedWordIds)) {
                foreach ($relatedWordIds as $ival) {
                    $otherTranslation[] = [
                        'word_id' => (int) $ival,
                        "scholar_id" => $scholarId,
                        "quranic_lexicon" => "",
                        "quranic_lexicon_type" => "Referred",
                        "quranic_lexicon_number" => $otherTranslationInfo['quranic_lexicon_number'],
                        "addresser" => $otherTranslationInfo['addresser'],
                        "addressee" => $otherTranslationInfo['addressee'],
                        "disable" => (int) true,
                        "reference_word" => $wordId
                    ];

                    foreach ($languages as $language) {
                        $languageTranslation = array_filter($translation, function ($item) use ($language) {
                            return ($item['language_id'] == $language['id']);
                        });
                        $languageTranslation = reset($languageTranslation);
                        $wordTranslation[] = [
                            'word_id' => (int) $ival,
                            'scholar_id' => $scholarId,
                            'language_id' => $language['id'],
                            'translation' => @$languageTranslation['translation'] ?? '',
                        ];
                    }
                }
            }

            DB::beginTransaction();
            WordsTranslations::whereIn('word_id', $oldRelatedWordIds)->where('scholar_id', $scholarId)->delete();
            OtherWordTranslationInfo::where('reference_word', $wordId)->where('scholar_id', $scholarId)->delete();
            if (!empty($wordTranslation)) {
                WordsTranslations::insert($wordTranslation);
                OtherWordTranslationInfo::insert($otherTranslation);
            }
            DB::commit();
            $response = array('success' => true, 'message' => 'Reference Word Deleted Successfully');
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

}