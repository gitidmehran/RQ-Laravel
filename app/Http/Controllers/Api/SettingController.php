<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Languages;
use App\Models\AyatsTranslation;
use App\Models\User;
use App\Models\WordsTranslations;
use Illuminate\Http\Request;
use Auth;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        // return $request->id;
        try {
            $user = Auth::user();
            if($request->has('id') && !empty($request->id) && $request->id != null){
                $user = User::where('id', $request->id)->first();
                $settings = $user->user_settings;
            }
            else {

                $settings = $user->user_settings;
            }
            $settings = json_decode($settings, true);
            $scholars = User::where('role', 3)->select(['id','name','short_name','translated_language'])->get()->toArray();
            $languages = Languages::select('id', 'name', 'short_name')->get()->toArray();

            // Arrange Languages Data
            $wordLanguages = $ayatLanguages = [];
            foreach ($languages as $key => $value) {
                $wordLanguages[] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'checked' => $this->languagesChecked('wordLanguages', $value['id'], $settings),
                ];

                $ayatLanguages[] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'checked' => $this->languagesChecked('ayatLanguages', $value['id'], $settings),
                ];
            }

            if ( $user->role == 3 || $user->role == 4 ) {
                $settingItem = $settings['allowedScholar'] ?? [];
                if($user->role==3){
                    $settingItem[] = $user->id;
                }

                $scholars = array_filter($scholars, function ($item) use ($settingItem) {
                    return ( in_array($item['id'], $settingItem) );
                });
            }
            
            
            // Arrange Scholars Data
            $wordScholars = $ayatScholars =[];
            foreach ($scholars as $key => $value) {
                $scholarAllowedLanguages = explode(',', $value['translated_language']);
                foreach ($languages as $k => $v) {
                    if(in_array($v['id'], $scholarAllowedLanguages)){
                        $wordScholarsChecked = $this->isScholarAlreadySelected('wordScholars', $v['id'], $value['id'], $settings);
                        $ayatScholarsChecked = $this->isScholarAlreadySelected('ayatScholars', $v['id'], $value['id'], $settings);

                        $wordScholars[] = [
                            'scholarId' => $value['id'],
                            'languageId' => $v['id'],
                            'label' => $value['name'].' - '.$v['name'],
                            'displayLabel' => $value['short_name'].'-'.$v['short_name'],
                            'checked' => $wordScholarsChecked['checked'],
                            'disable' => $wordScholarsChecked['disable'],
                        ];
                        $ayatScholars[] = [
                            'scholarId' => $value['id'],
                            'languageId' => $v['id'],
                            'label' => $value['name'].' - '.$v['name'],
                            'displayLabel' => $value['short_name'].'-'.$v['short_name'],
                            'checked' => $ayatScholarsChecked['checked'],
                            'disable' => $ayatScholarsChecked['disable'],
                        ];
                    }
                }
            }
            // Word Display Settings
            $displayWords = [];
            foreach (Config('constants.words') as $key => $val) {
                $displayWords[] =[
                    'label' => $key.' ('.$val.')',
                    'value' => $key,
                    'checked' => $this->isOtherInfoAlreadyChecked('displayWords', $key, $settings),
                ];
            }

            // Arrange OtherInfo Data
            $otherinfo = [
                'show_word_translation' => 'Show Word Translations',
                'show_non_arabic' => 'Show Simple Word And Ayat',
                'addresser' => 'Addresser',
                'addressee' => 'Addressee',
                'quranic_lexicon_type' => 'Quranic Lexicon',
                'word_notes'  => 'Reference Notes'
            ];
            $grammerSettings = [];
            foreach ($otherinfo as $key => $value) {
                $grammerSettings[] = [
                    'label' => $value,
                    'value' => $key,
                    'checked' => $this->isOtherInfoAlreadyChecked('grammerSettings', $key, $settings),
                ];
            }

            $data = [
                'wordLanguages' => $wordLanguages,
                'ayatLanguages' => $ayatLanguages,
                'wordScholars' => $wordScholars,
                'ayatScholars' => $ayatScholars,
                'displayWords' => $displayWords,
                'grammerSettings' => $grammerSettings
            ];

            $response = array('success'=>true,'message'=>"User Settings Retrieved Successfully",'list'=>$data);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    private function isScholarAlreadySelected($field, $languageId, $scholarId, $settings)
    {
        $settingItem = $settings[$field] ?? [];
        $filterItem = array_filter($settingItem, function ($item) use ($languageId, $scholarId) {
            return ($item['scholarId']==$scholarId && $item['languageId']==$languageId);
        });
        $resetItem = reset($filterItem);
        $data = [
            'checked' => $resetItem['checked'] ?? false,
            'disable' => $resetItem['disable'] ?? false,
        ];
        return $data;
    }

    private function languagesChecked($field, $id, $settings)
    {
        $settingItem = $settings[$field] ?? [];
        $filterItem = array_filter($settingItem, function ($item) use ($id) {
            return ($item['id']==$id);
        });
        $languageItem = reset($filterItem);
        return $languageItem['checked'] ?? false;
    }

    private function isOtherInfoAlreadyChecked($field, $value, $settings)
    {
        $settingItem = $settings[$field] ?? [];
        $filterItem = array_filter($settingItem, function ($item) use ($value) {
            return ($item['value']==$value);
        });
        $resetItem = reset($filterItem);
        return $resetItem['checked'] ?? false;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $settings = !empty($user->user_settings)?json_decode($user->user_settings, true):[];
        $authId = $user->id;
            if($request->has('id') && !empty($request->id) && $request->id != null){
                $user = User::where('id', $request->id)->first();
                // $settings = $user->user_settings;
                $settings = !empty($user->user_settings)?json_decode($user->user_settings, true):[];
        $authId = $user->id;
            }
        
        $input = [
            'ayatScholars' => $data['ayatScholars'],
            'wordScholars' => $data['wordScholars'],
            'wordLanguages' => $data['wordLanguages'],
            'ayatLanguages' => $data['ayatLanguages'],
            'displayWords' => $data['displayWords'],
            'grammerSettings' => $data['grammerSettings'],
        ];
        $record = array_merge($settings,$input);
        try {
            User::where('id', $authId)->update(['user_settings'=> json_encode($record)]);
            $response = array('success'=>true,'message'=> 'Settings saved Successfully');
        } catch(\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }
}
