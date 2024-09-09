<?php

use App\Exports\WordExport;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImporterController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\QuranAyatsController;
use App\Http\Controllers\ReferenceWordsController;
use App\Http\Controllers\RootwordsController;
use App\Http\Controllers\ScholarController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WordController;
use App\Models\InfoData;
use App\Models\Words;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    // return redirect()->away('http://54.89.55.179:30006/');
    return view('auth.login');
});

Route::get('export-missings', function () {
    $words = Words::all()->toArray();
    $newWords = \DB::table('words_new')->get()->toArray();
    $wordIds = array_column($newWords, 'id');

    $diff = [];
    foreach ($words as $key => $value) {
        if(!in_array($value['id'], $wordIds)) {
            $diff[] = [
                'Ayat Id' => $value['ayat_id'],
                'Word Reference' => $value['reference'],
                'Word' => $value['word']
            ];
        }
    }
    return Excel::download(new WordExport($diff), 'words-15-02-23.xlsx');
});

Route::get('refresh-data', [ImporterController::class, 'refreshData']);

Route::get('create-view', function () {
    $query = \DB::table('words_translations')
            ->select('words_translations.word_id', 'words_translations.scholar_id', 'words_translations.language_id', 'words_translations.translation', 'languages.short_name as language_name', 'users.short_name as scholar_name', )
            ->join('languages', 'languages.id', '=', 'words_translations.language_id')
            ->join('users', 'users.id', '=', 'words_translations.scholar_id')
            ->toSql();
    echo $query;
});
Route::get('view-words', function () {
    $words = InfoData::withCount('words')->paginate(1000)->toArray();
    dd($words);
    die;
});

Route::group(['prefix'=>'dashboard','middleware'=>'auth'], function () {
    Route::get('/', function () {
        return view('quranAyats.list');
    })->name('dashboard');
    Route::get('logout', [AuthenticatedSessionController::class,'destroy']);

    // Teams
    Route::get('teams/delete/{id}', [TeamsController::class,'destroy']);
    Route::resource('teams', TeamsController::class);

    // Users
    Route::get('users/delete/{id}', [UsersController::class,'destroy']);
    Route::resource('users', UsersController::class);

    // Quran Ayats
    Route::get('ayats-list', [QuranAyatsController::class,'index']);
    Route::post('/filter-query', [QuranAyatsController::class,'search']);
    Route::post('/search-ayat', [QuranAyatsController::class,'searchAyats']);
    Route::get('/detail/{id}', [QuranAyatsController::class,'detailAyat']);
    Route::get('search-by-ayats', [QuranAyatsController::class,'filterByVerses']);

    // Scholars
    Route::get('word-by-word-quran', [ScholarController::class,'byScholars']);
    Route::get('word-search', [ScholarController::class,'wordSearch']);
    Route::get('scholar-translations', [ScholarController::class,'viewTranslation']);

    // Translations
    Route::get('/translation', [TranslationController::class,'translationAyat']);
    Route::post('get-translation', [TranslationController::class,'getTranslation']);
    Route::get('get-related-words', [TranslationController::class,'getRelatedWords']);
    Route::post('save-translation', [TranslationController::class,'saveTranslation']);
    Route::post('save-word-references', [TranslationController::class,'saveWordPreferences']);
    Route::post('save-phrase-word-references', [TranslationController::class,'savePhraseWordPreferences']);
    Route::get('my-translations', [TranslationController::class,'translationsByScholars']);
    Route::get('translation/{id}/edit/{scholar}', [TranslationController::class,'edit']);
    Route::post('update-translation', [TranslationController::class,'update']);
    Route::get('single-translation', [TranslationController::class,'singleTranslationView']);
    Route::post('remove-word-preference', [TranslationController::class,'removePreference']);
    Route::post('remove-phrase-word-preference', [TranslationController::class,'removePhrasePreference']);
    Route::get('translation/delete/{id}', [TranslationController::class,'destroy']);

    // Stories
    Route::get('stories/create', [StoriesController::class,'index']);

    // Settings
    Route::get('settings', [SettingController::class,'index']);
    Route::post('settings', [SettingController::class,'store']);

    // Research Quran Notes
    Route::get('rq-notes/delete/{id}', [NotesController::class,'destroy']);
    Route::post('rq-notes/{id}', [NotesController::class,'update']);
    Route::resource('rq-notes', NotesController::class);

    // Stories Routes
    Route::get('stories/get-ayats', [StoryController::class,'getAyats']);
    Route::post('stories/add-ayats', [StoryController::class,'addStoryAyats']);
    Route::resource('stories', StoryController::class);

    // Words Routes
    Route::get('reference-words', [WordController::class,'referenceWords']);

    // Root Word
    Route::get('/addmeaning', [RootwordsController::class,'index']);

    // Search Roots
    Route::get('/word/{key}', [RootwordsController::class,'search']);
    Route::post('/add-meanings/{id}', [RootwordsController::class,'store']);
    Route::post('/add-meaning', [RootwordsController::class,'store_root_word']);
    Route::get('/get-root-word', [RootwordsController::class,'show']);

    // Phrase Words
    Route::get('/get-reference-words', [ReferenceWordsController::class, 'index']);
    Route::get('/get-reference-words-by-scholar', [ReferenceWordsController::class, 'show']);
    Route::get('/get-reference-words-by-scholar/{id}/{key}', [ReferenceWordsController::class, 'sort']);
    Route::post('/edit_reference_word_translation/{id}', [ReferenceWordsController::class, 'update']);

});



require __DIR__.'/auth.php';
