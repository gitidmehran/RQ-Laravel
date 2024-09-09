<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\QuranSurahs;
use Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {        
        view()->composer('*', function ($view) 
        {
            $user_settings = [];
            $user = request()->user();
            $user_settings = !empty($user->user_settings)?json_decode($user->user_settings,true):[];
            $surahs = cache()->remember('surahs',60*60,function(){
                return QuranSurahs::all()->toArray();
            });
            $default_languages = [1,2];
            $arabic_constants_words = \Config::get('constants.words');
            $scholars_setting = $user_settings['scholars_setting'] ?? [];
            $words_settings = $user_settings['words_settings'] ?? ['word','grammatical_description'];
            $ayat_languages_settings = $user_settings['ayat_languages_settings'] ?? $default_languages;
            $ayat_scholars_settings = $user_settings['ayat_scholars_settings'] ?? [];
            $word_languages_settings = $user_settings['word_languages_settings'] ?? $default_languages;
            $word_scholars_settings = $user_settings['word_scholars_settings'] ?? [];
            $word_translation_settings = $user_settings['word_translation_settings'] ?? [];
            $show_word_translation_settings = $user_settings['show_word_translation_settings'] ?? '';
            $ayat_scholar_checked_languages = $user_settings['ayat_scholar_checked_languages'] ?? [];
            $word_scholar_checked_languages = $user_settings['word_scholar_checked_languages'] ?? [];
            
            $view->with([
                'surahs'=>$surahs,
                'words_settings'            => $words_settings ,
                'ayat_languages_settings'   => $ayat_languages_settings,
                'ayat_scholars_settings'    => $ayat_scholars_settings,
                'word_languages_settings'   => $word_languages_settings,
                'word_scholars_settings'    => $word_scholars_settings,
                'arabic_constants_words'    => $arabic_constants_words,
                'word_translation_settings' => $word_translation_settings,
                'show_word_translation_settings' => $show_word_translation_settings,
                'ayat_scholar_checked_languages' => $ayat_scholar_checked_languages,
                'word_scholar_checked_languages' => $word_scholar_checked_languages,
            ]);
        });
    }
}
