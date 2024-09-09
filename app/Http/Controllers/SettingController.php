<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use App\Models\AyatsTranslation;
use App\Models\User;
use App\Models\WordsTranslations;
use Illuminate\Http\Request;
use Auth;
class SettingController extends Controller
{
    public function index(Request $request){
        
       
        $arabic_words = \Config('constants.words');
        $columns = [
            'word'=>'Arabic Word',
            'grammatical_description'=>'Grammatical Descriptin',
            'contemporary_grammar' => 'CMPT Grammar',
            'root_word'=>'Root Word',
            'meaning_urdu' => 'Urdu Meaning',   // ------ col
            'meaning_eng' => 'English Meaning', // ------ col
            'prefix'=>'Prefix',
            'actual_word'=>'Actual Word',
            'filtered_word'=>'Filtered Word',
            'postfix'=>'PostFix',
            'grammer_detail' => 'Grammer Detail',
        ];
        foreach ($columns as $key => $value) {
            $columns[$key] = $value.' ('.$arabic_words[$key].')';
        }
        $languages = Languages::select('id','name')->get()->toArray();
        $scholarData = $this->formatScholarsData($languages, $request);
        $otherinfo = [
            'addresser' => 'Addresser',
            'addressee' => 'Addressee',
            'reference_type' => 'Abrahamic Locution Reference Type',
            'word_notes'  => 'Reference Notes'
        ];
        
        $previous_url =  url()->previous();
        $data = [
            'columns'      => $columns,
            'languages'    => $languages,            
            'previous_url' => $previous_url,  
            'word_translation_info' =>  $otherinfo,
            'formated_ayat_scholars_settings' => $scholarData['formated_ayat_scholars_settings'],
            'formated_word_scholars_settings' => $scholarData['formated_word_scholars_settings'],
        ];
        return view('settings.setting',$data);
    }

    private function formatScholarsData($languages, $request){
        
        $scholars = User::where(['role'=>3,'is_approved'=>1])->withCount(['ayatTranslations','wordTranslations'])->get()->toArray();
        $user_settings = json_decode(\Auth::user()->user_settings,true);
        if($request->has('id') && !empty($request->id)){
            $user = User::where('id', $request->id)->first();
            $user_settings = json_decode($user->user_settings, true);
        }
        $ayat_languages_settings = $user_settings['ayat_languages_settings'] ?? [];
        $ayat_scholars_settings = $user_settings['ayat_scholars_settings'] ?? [];
        $word_languages_settings = $user_settings['word_languages_settings'] ?? [];
        $word_scholars_settings = $user_settings['word_scholars_settings'] ?? [];
        $ayat_scholar_checked_languages = $user_settings['ayat_scholar_checked_languages'] ?? [];
        $word_scholar_checked_languages = $user_settings['word_scholar_checked_languages'] ?? [];

        $formated_ayat_scholars_settings = $formated_word_scholars_settings = [];
        foreach ($scholars as $key => $value) {
            $language_array = explode(',',$value['translated_language']);
            if(count($language_array) > 1){
                foreach ($language_array as $val) {
                    $key = array_search($val, array_column($languages, 'id'));
                    $language_name = $languages[$key]['name'] ?? '';

                    $ayat_disabled = in_array($val,$ayat_languages_settings);

                    $word_disabled = !in_array($val,$word_languages_settings);
                    if(!empty($value['ayat_translations_count'])){
                        $formated_ayat_scholars_settings[] = [
                            'id' => $value['id'],
                            'scholar_name' => !empty($value['team_name'])?$value['team_name']:$value['name'],
                            'language_id'  => $val,
                            'language_name' => $language_name,
                            'disabled' => !$ayat_disabled,
                            'checked'  => in_array($value['id'].'-'.$val,@$ayat_scholar_checked_languages)
                        ];
                    }
                    
                    if(!empty($value['word_translations_count'])){
                        $formated_word_scholars_settings[] = [
                            'id' => $value['id'],
                            'scholar_name' => !empty($value['team_name'])?$value['team_name']:$value['name'],
                            'language_id'  => $val,
                            'language_name' => $language_name,
                            'disabled' => $word_disabled,
                            // 'checked'  => @$word_scholar_checked_languages[$value['id'].'-'.$val]
                            'checked'  => in_array($value['id'].'-'.$val,@$word_scholar_checked_languages)
                        ];
                    }
                }
            }else{
                $key = array_search($value['translated_language'], array_column($languages, 'id'));
                $language_name = $languages[$key]['name'] ?? '';

                $ayat_disabled = (!in_array($value['id'],$ayat_scholars_settings) && !in_array($value['translated_language'],$ayat_languages_settings));

                $word_disabled = (!in_array($value['id'],$word_scholars_settings) && !in_array($value['translated_language'],$word_languages_settings));
                if(!empty($value['ayat_translations_count'])){
                    $formated_ayat_scholars_settings[] = [
                        'id' => $value['id'],
                        'scholar_name' => !empty($value['team_name'])?$value['team_name']:$value['name'],
                        'language_id'  => $value['translated_language'],
                        'language_name' => $language_name,
                        'disabled' => $ayat_disabled,
                        // 'checked'  => @$ayat_scholar_checked_languages[$value['id'].'-'.$value['translated_language']]
                        'checked'  => in_array($value['id'].'-'.$value['translated_language'],@$ayat_scholar_checked_languages)
                    ];
                }
                
                if(!empty($value['word_translations_count'])){
                    $formated_word_scholars_settings[] = [
                        'id' => $value['id'],
                        'scholar_name' => !empty($value['team_name'])?$value['team_name']:$value['name'],
                        'language_id'  => $value['translated_language'],
                        'language_name' => $language_name,                
                        'disabled' => $word_disabled,
                        'checked'  => in_array($value['id'].'-'.$value['translated_language'],@$word_scholar_checked_languages)
                    ];
                }
            }
        }
        return [
            'formated_ayat_scholars_settings' => $formated_ayat_scholars_settings,
            'formated_word_scholars_settings' => $formated_word_scholars_settings,
        ];
    }

    public function store(Request $request){
        $data = $request->all();
        $previous_url = $data['previous_url'];
        unset($data['previous_url']);

        $data['ayat_scholar_checked_languages'] = array_filter($data['ayat_scholar_checked_languages']);        
        $new_word_scholars_languages = [];
        if(!empty($data['word_scholar_checked_languages'])){
            foreach ($data['word_scholar_checked_languages'] as $key => $value) {
                if(!is_null($value)){
                    $new_word_scholars_languages[]=$value;
                }
            }
        }   
        $data['word_scholar_checked_languages'] = $new_word_scholars_languages;
        // echo json_encode($data);die;
        $authId = Auth::id();
        try{
            $settings = Auth::user()->user_settings;
            User::where('id',$authId)->update(['user_settings'=>json_encode($data)]);
            $message = !empty($settings)?'Settings Updated Successfully':"Setting Created";
            $response = array('success'=>true,'message'=>$message,'action'=>'redirect','url'=>$previous_url);
        }catch(\Exception $e){
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }
}
