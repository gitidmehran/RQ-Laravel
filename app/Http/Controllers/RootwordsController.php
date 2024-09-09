<?php

namespace App\Http\Controllers;

use App\Models\AyatsTranslation;
use App\Models\InfoData;
use App\Models\Languages;
use App\Models\Notes;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\Words;
use App\Models\WordsTranslations;
use App\Models\QuranSurahs;
use App\Models\RootWordMeaning;
use Illuminate\Http\Request;
use App\Models\WordMeaning;
use Auth;
use DB;



class RootwordsController extends Controller
{

    public function index()
    {

        $root_word_meanings = DB::table('root_word_meanings')->get();
        $data = ['previous_url' => '', 'root_word_meanings' => $root_word_meanings];
        return view('rootWords.rootwords', $data);
    }

    public function show(Request $request)
    {
        $id = $request->params;
        $root_word_meanings = DB::table('root_word_meanings')->where('id', $id)->get();
        $data = [
            'title'         => 'RootWord References',
            'root_word_meanings' => $root_word_meanings
        ];
        return view('rootWords.rootword', $data);
    }

    public function store(Request $resquest)
    {

        $meaningurdu = $resquest->rootwordmeaningurdu;
        $meaningeng = $resquest->rootwordmeaningeng;

        $id = $resquest->id;

        DB::update('update root_word_meanings set meaning_urdu = ?,meaning_eng = ? where id = ?', [$meaningurdu, $meaningeng, $id]);
        return redirect()->back();
    }

    public function store_root_word(Request $resquest)
    {
        DB::beginTransaction();
        try {
            $meaningurdu = $resquest->rootwordmeaningurdu;
            $meaningeng = $resquest->rootwordmeaningeng;

            $id = $resquest->rootword_id;

            DB::update('update root_word_meanings set meaning_urdu = ?,meaning_eng = ? where id = ?', [$meaningurdu, $meaningeng, $id]);

            DB::commit();
            $response = array('success' => true, 'message' => 'Data Updated', 'action' => 'reload');
        } catch (\Exception $e) {
            DB::rollback();
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    public function search(Request $request)
    {

        $word = '';
        $key = $request->key;

        switch ($key) {
            case 'A':
                $word = 'أ';
                break;
            case 'b':
                $word = 'ب';
                break;
            case 't':
                $word = 'ت';
                break;
            case 'v':
                $word = 'ث';
                break;
            case 'j':
                $word = 'ج';
                break;
            case 'hh':
                $word = 'ح';
                break;
            case 'x':
                $word = 'خ';
                break;
            case 'd':
                $word = 'د';
                break;
            case 'st':
                $word = 'ذ';
                break;
            case 'r':
                $word = 'ر';
                break;
            case 'z':
                $word = 'ز';
                break;
            case 's':
                $word = 'س';
                break;
            case 'dl':
                $word = 'ش';
                break;
            case 'ss':
                $word = 'ص';
                break;
            case 'dd':
                $word = 'ض';
                break;
            case 'tt':
                $word = 'ط';
                break;
            case 'zz':
                $word = 'ظ';
                break;
            case 'ee':
                $word = 'ع';
                break;
            case 'g':
                $word = 'غ';
                break;
            case 'f':
                $word = 'ف';
                break;
            case 'q':
                $word = 'ق';
                break;
            case 'k':
                $word = 'ك';
                break;
            case 'l':
                $word = 'ل';
                break;
            case 'm':
                $word = 'م';
                break;
            case 'n':
                $word = 'ن';
                break;
            case 'h':
                $word = 'ه';
                break;
            case 'w':
                $word = 'و';
                break;
            case 'y':
                $word = 'ي';
                break;
            default:
                $word = 'أ';
                break;
        }

        $root_word_meanings = DB::table('root_word_meanings')->where('root_word', 'LIKE', '' . $word . '%')->get();
        $sql = ['previous_url' => '', 'root_word_meanings' => $root_word_meanings];
        return view('rootWords.rootwords', $sql);
    }
}
