<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AyatNotesController;
use App\Http\Controllers\Api\GrammarNotesController;
use App\Http\Controllers\Api\ImpersonationsController;
use App\Http\Controllers\Api\LanguagesController;
use App\Http\Controllers\Api\NotesController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\QuranAyatsController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\Api\ReferenceWordController;
use App\Http\Controllers\Api\ReferenceWordTranslationController;
use App\Http\Controllers\Api\ReferenceWordsController;
use App\Http\Controllers\Api\RootwordsController;
use App\Http\Controllers\Api\ScholarController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StoriesController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\TranslationDisplayController;
use App\Http\Controllers\Api\TranslationFormController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\WordsNotesController;
use App\Models\User;
use App\Http\Controllers\Api\HelpsController;
use Illuminate\Support\Facades\Route;

use App\Models\AyatsTranslation;

use App\Http\Controllers\Api\ImporterController;
use App\Utility\Utility;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:passport')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('get-users', function () {
    $users = User::all()->toArray();
    $response = [
        'success' => true,
        'host' => env('DB_HOST'),
        'list' => $users,
    ];
    return response()->json($response);
});

Route::get('get-quran-data', [QuranAyatsController::class, 'quranicData']);
Route::post('login', [AuthController::class,'login']);
Route::group(['middleware'=>['auth:api','expire.token']], function () {

    // GET LANGUAGES
    Route::apiResource('languages', LanguagesController::class);

    // USERS ROUTES
    Route::get('get-scholars', [UsersController::class,'getAllScholars']);
    Route::apiResource('users', UsersController::class);

    // Logout Route
    Route::post('logout', [AuthController::class,'logout']);

    // Quran Ayats
    Route::post('search-ayat', [QuranAyatsController::class,'index']);

    // Settings Routs
    Route::get('settings', [SettingController::class,'index']);
    Route::post('settings', [SettingController::class,'store']);

    // Translation Routes
    Route::post('/translation/create', [TranslationFormController::class,'create']);
    Route::post('/translation/save', [TranslationFormController::class,'store']);
    Route::post('/translation/edit', [TranslationFormController::class, 'edit']);
    Route::post('/translation/update', [TranslationFormController::class, 'update']);
    Route::post('/translation/find-words-info', [TranslationFormController::class,'findWordsInfoRizwan']);
    Route::post('/translation/words-rizwan', [TranslationFormController::class,'WordsRizwan']);
    Route::post('/translation/related-words', [TranslationFormController::class,'getRelatedWords']);
    Route::post('/translation/related-phrase-words', [TranslationFormController::class,'getRelatedPhraseWords']);
    Route::delete('translation/delete/{id}', [TranslationFormController::class,'destroy']);
    Route::post('translation/remove-single-word-translation', [TranslationFormController::class,'removeSingleWordTranslation']);

    // // Translations Rizwan

    /*OK*/ Route::get('/translation', [TranslationController::class,'translationAyat']);
    Route::post('get-translation', [TranslationController::class,'getTranslation']);
    /*OK*/ Route::get('get-related-words', [TranslationController::class,'getRelatedWords']);
    Route::post('save-translation', [TranslationController::class,'saveTranslation']);
    Route::post('save-word-references', [TranslationController::class,'saveWordPreferences']);
    Route::post('save-phrase-word-references', [TranslationController::class,'savePhraseWordPreferences']);

    // /*OK*/ Route::get('my-translations', [TranslationController::class,'translationsByScholars']);
    Route::get('translation/{id}/edit/{scholar}', [TranslationController::class,'edit']);
    Route::post('update-translation', [TranslationController::class,'update']);
    Route::get('single-translation', [TranslationController::class,'singleTranslationView']);
    Route::post('remove-word-preference', [TranslationController::class,'removePreference']);
    Route::post('remove-phrase-word-preference', [TranslationController::class,'removePhrasePreference']);

    // REFERENCE WORD TRANSLATION ROUTES
    Route::post('reference-words', [ReferenceWordTranslationController::class,'index']);
    Route::post('save-reference-word-translation', [ReferenceWordTranslationController::class,'update']);
    Route::post('reference-words-delete', [ReferenceWordTranslationController::class,'delete']);



    Route::get('rq-notes/delete/{id}', [NotesController::class,'destroy']);
    Route::post('rq-notes/{id}', [NotesController::class,'update']);
    Route::post('add-rq-notes', [NotesController::class,'store']);
    Route::get('rq-notes', [NotesController::class,'index']);

    // AYAT NOTES ROUTES
    Route::post('ayat-notes/{id}', [AyatNotesController::class,'update']);
    Route::apiResource('ayat-notes', AyatNotesController::class);

    // WORD NOTES ROUTES
    Route::post('word-notes/search', [WordsNotesController::class,'search']);
    Route::post('word-notes/{id}', [WordsNotesController::class,'update']);
    Route::apiResource('word-notes', WordsNotesController::class);

    // GRAMMAR NOTES ROUTES
    Route::get('grammar-notes', [GrammarNotesController::class, 'index']);
    Route::post('grammar-notes/update-arabic-note', [GrammarNotesController::class, 'updateGrammarNote']);
    Route::post('grammar-notes/update-usmani-note', [GrammarNotesController::class, 'updateUsmaniNote']);
    
    //Mehran

    // Phrase Words
    Route::get('/get-reference-words', [ReferenceWordsController::class, 'index']);
    Route::get('/get-reference-words-by-scholar', [ReferenceWordsController::class, 'show']);
    Route::get('/get-reference-words-by-scholar/{id}/{key}', [ReferenceWordsController::class, 'sort']);
    // Pending dependancy Ahmad Raza
    Route::post('/edit_reference_word_translation/{id}', [ReferenceWordsController::class, 'update']);

    // Root Word
    Route::get('/addmeaning', [RootwordsController::class,'index']);
    Route::get('/root-word-meanings', [RootwordsController::class,'index']);
    Route::post('/add-root-word-meanings/{id}', [RootwordsController::class,'store']);

    // Search Roots
    Route::post('/add-meaning', [RootwordsController::class,'store_root_word']);
    Route::get('/get-root-word', [RootwordsController::class,'show']);

    // IMPERSONATE PERMISSION ROUTES
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);

    // IMPERSONATE ROUTES
    Route::get('/impersonate/{userId}', [ImpersonationsController::class, 'impersonate']);
    Route::get('/leave-impersonation', [ImpersonationsController::class, 'leaveImpersonate']);

    // New Word By Word Api
    Route::get('word-by-word', [TranslationDisplayController::class,'index']);
    Route::get('word-search', [TranslationDisplayController::class,'wordSearch']);
    Route::get('my-translations', [TranslationDisplayController::class, 'myTranslations']);
    Route::get('scholar-translations', [TranslationDisplayController::class, 'getTranslationByScholars']);
    Route::post('update-contemporary-grammar', [TranslationDisplayController::class, 'updateContemporaryGrammar']);

    // STOREIS ROUTES
    Route::get('stories/get-all', [StoriesController::class,'getAllStories']);
    Route::post('stories/update-status/{id}', [StoriesController::class, 'updateStatus']);
    Route::apiResource('stories', StoriesController::class);

    // TOPICS ROUTES
    Route::post('published-topics', [TranslationDisplayController::class, 'publishedTopics']);

    Route::get('get-scholars', [UsersController::class,'getAllScholars']);

    //Helps
    Route::get('helps', [HelpsController::class,'index']);
    Route::post('helps', [HelpsController::class,'store']);
    Route::post('helps/{id}', [HelpsController::class,'delete']);
    Route::post('update-helps/{id}', [HelpsController::class,'update']);
});

Route::get('add_new_translation', [ImporterController::class, 'importXMLTranslation']);
Route::get('import-from-json', [ImporterController::class, 'importFromJson']);
Route::get('import-single', [ImporterController::class, 'importSingleScholar']);
Route::post('upload-file', [UploadFileController::class, 'storeFile']);
Route::get('update-files', [UploadFileController::class, 'updateFilesName']);
Route::get('remove-old-files', [UploadFileController::class, 'removeOldFiles']);
Route::get('import-missing-data', [ImporterController::class, 'importMissingWordData']);
Route::get('upload-dotted-less-data', [UploadFileController::class, 'uploadDottedLessData']);
Route::get('find-missing-data', [UploadFileController::class, 'missingWordData']);