<?php

namespace App\Http\Controllers;

use App\Models\Notes;
use App\Models\OtherWordsInfo;
use App\Models\User;
use App\Models\WordNotes;
use App\Models\WordReferences;
use App\Models\Words;
use App\Models\WordsTranslations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferenceWordsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authId = 16;// Auth::id();
        // $word_reference = WordReferences::get()->toArray();
        $word_reference = WordReferences::where('scholar', $authId)->get()->toArray();
        $word_reference_ids = array_unique(array_column($word_reference,'word_id'));

        $words = Words::with([
            'singleReferenceWord' => fn($q) => $q->where('scholar', $authId), 
            'single_translation' => fn($q) => $q->where('scholar', $authId),
            'otherWordInfo' => fn($q) => $q->where('scholar', $authId),
            'translations'=> fn($q) => $q->OfSpecialScholars( $authId )
                                         ->with('scholar:id,name,short_name')
                                         ->where('is_reference_word', 1),
            'wordNotes' => fn($q) => $q->where('scholar', $authId)->with('notes')
            //'wordReferences' 
        ])
        ->whereIn('id', $word_reference_ids)
        ->selectRaw('*,substr(word,1,1) as letter')
        ->orderBy('letter');
        // ->paginate(15);

        $letters = array_unique( array_column( $words->get()->toArray(), 'letter' ) );

        $scholars = User::where('role',3)->get()->toArray();

        // $links = $words->paginate(50)->links('pagination::bootstrap-5');

        // $words = Words::with('singleReferenceWord', 'translations', 'single_translation')->whereIn('id', $word_reference_ids)->paginate(15);
        // $words = Words::with('singleReferenceWord', 'translations')->where('word', 'like', '%يَا%')->whereIn('id', $word_reference_ids)->paginate(15)->toArray();

        $notes = Notes::all()->toArray();
        
        $notes_id = [];

        foreach( $words->get()->toArray() as $key => $val) {
            $notes_id[] = array_column($val['word_notes'],'note_id');
        }

        $word_ids = array_column($words->get()->toArray(),'id');

        // echo "<pre>";
        // print_r( $word_ids );
        // exit;
        
        return view('rootWords.referencewords', [
            'word_reference'    => $words->get(), 
            'word_ids'          => json_encode($word_ids),
            'notes'             => $notes,
            'note_id'           => $notes_id,
            'scholars'          => $scholars, 
            'scholar_id'        => $authId, 
            'letters'           => $letters,
            // 'links' => $links, 
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show( Request $request )
    {
        $input = $request->input();
        $authId = $input['scholar'];// Auth::id();

        // $word_reference = WordReferences::get()->toArray();
        $word_reference = WordReferences::where('scholar', $authId)->get()->toArray();
        $word_reference_ids = array_unique(array_column($word_reference,'word_id'));

        $words = Words::with([
            'singleReferenceWord' => fn($q) => $q->where('scholar', $authId), 
            'single_translation' => fn($q) => $q->where('scholar', $authId),
            'otherWordInfo' => fn($q) => $q->where('scholar', $authId),
            'translations'=> fn($q) => $q->OfSpecialScholars( $authId )->with('scholar:id,name,short_name')->where('is_reference_word', 1),
            'wordNotes' => fn($q) => $q->where('scholar', $authId)->with('notes')
            //'wordReferences' 
        ])
        ->whereIn('id', $word_reference_ids)
        ->selectRaw('*,substr(word,1,1) as letter')
        ->orderBy('letter');
        // ->paginate(15);

        $letters = array_unique( array_column( $words->get()->toArray(), 'letter' ) );

        $scholars = User::where('role',3)->get()->toArray();

        // $links = $words->paginate(50)->links('pagination::bootstrap-5');

        $notes = Notes::all()->toArray();
        
        $notes_id = [];

        foreach( $words->get()->toArray() as $key => $val) {
            $notes_id[] = array_column($val['word_notes'],'note_id');
        }

        $word_ids = array_column($words->get()->toArray(),'id');

        // echo "<pre>";
        // print_r( $word_ids );
        // exit;
        
        return view('rootWords.referencewords', [
            'word_reference'    => $words->get(), 
            'word_ids'          => json_encode($word_ids),
            'notes'             => $notes,
            'note_id'           => $notes_id,
            'scholars'          => $scholars, 
            'scholar_id'        => $authId, 
            'letters'           => $letters,
            // 'links' => $links, 
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  string  $key
     * @return \Illuminate\Http\Response
     */
    public function sort( $id, $key )
    {
        $authId = $id;// Auth::id();

        // echo "<pre>";
        // print_r( $input );
        // exit;

        // $word_reference = WordReferences::get()->toArray();
        $word_reference = WordReferences::where('scholar', $authId)->get()->toArray();
        $word_reference_ids = array_unique(array_column($word_reference,'word_id'));

        $words = Words::with([
            'singleReferenceWord' => fn($q) => $q->where('scholar', $authId), 
            'single_translation' => fn($q) => $q->where('scholar', $authId),
            'otherWordInfo' => fn($q) => $q->where('scholar', $authId),
            'translations'=> fn($q) => $q->OfSpecialScholars( $authId )->with('scholar:id,name,short_name')->where('is_reference_word', 1),
            'wordNotes' => fn($q) => $q->where('scholar', $authId)->with('notes')
            //'wordReferences' 
        ])
        ->whereIn('id', $word_reference_ids)
        ->selectRaw('*,substr(word,1,1) as letter')
        ->orderBy('letter');
        // ->paginate(15);

        $letters = array_unique( array_column( $words->get()->toArray(), 'letter' ) );

        $scholars = User::where('role',3)->get()->toArray();

        $words = $words->where('word', 'like', $key.'%');

        // $links = $words->paginate(50)->links('pagination::bootstrap-5');

        $notes = Notes::all()->toArray();
        
        $notes_id = [];

        foreach( $words->get()->toArray() as $key => $val) {
            $notes_id[] = array_column($val['word_notes'],'note_id');
        }

        $word_ids = array_column($words->get()->toArray(),'id');

        // echo "<pre>";
        // print_r( $word_ids );
        // exit;
        
        return view('rootWords.referencewords', [
            'word_reference'    => $words->get(), 
            'word_ids'          => json_encode($word_ids),
            'notes'             => $notes,
            'note_id'           => $notes_id,
            'scholars'          => $scholars, 
            'scholar_id'        => $authId, 
            'letters'           => $letters,
            // 'links' => $links, 
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $scholarId)
    {
        // $value = $request->input();

        // $reference_word_translations = array();

        // foreach ($value['meaning'] as $ikey => $ival) {
        //     $reference_word_translations[] = [
        //         'word_id' => $value['word_id'],
        //         'language' => $ikey,
        //         'translation' => $ival,
        //         'scholar'     => $scholarId,
        //         'um_ul_kitaab'=> $value['um-ul-kitaab'],
        //         'is_reference_word' => 1,
        //         'created_at' => \Carbon\Carbon::now()
        //     ];
        // }

        // // echo '<pre>';
        // // print_r( $reference_word_translations );
        // // exit;

        // if(!empty( $reference_word_translations )) {
        //     $references = WordReferences::where( 'scholar', $scholarId )->where('word_id', $value['word_id'])->get()->toArray();
        //     if(!empty($references)){
        //         $referencesIds = array_column($references,'word_id');
        //         WordsTranslations::where(['scholar' => $scholarId, 'is_reference_word' => 1 ])->whereIn('word_id', $referencesIds)->delete();
        //     }
        //     WordsTranslations::insert( $reference_word_translations );
        // }

        // return redirect()->back();

        //-----------------------------------------------------
        //-------*****************************-----------------
        //-----------------------------------------------------
        $input = $request->all();
        $requestdata = $this->arrangeRequestData($input);

        // echo '<pre>';print_r($requestdata);die;
        
        DB::beginTransaction();
        try {

            if(!empty($requestdata['reference_word_translations'])){
                $references = WordReferences::where('scholar',$input['ref_auth_id'])->where('word_id',$input['ref_word_id'])->get()->toArray();
                if(!empty($references)){
                    $referencesIds = array_column($references,'reference_word_id');
                    WordsTranslations::where(['scholar'=>$input['ref_auth_id'],'is_reference_word'=>1])->whereIn('word_id',$referencesIds)->delete();
                }
                WordsTranslations::insert($requestdata['reference_word_translations']);
            }

            if(!empty($requestdata['word_preferences_array'])){
                WordReferences::where('scholar',$input['ref_auth_id'])->where('word_id',$input['ref_word_id'])->delete();
                WordReferences::insert($requestdata['word_preferences_array']);
            }

            if(!empty($requestdata['otherinfo_array'])){
                OtherWordsInfo::where('scholar',$input['ref_auth_id'])->where('word_id',$input['ref_word_id'])->delete();
                OtherWordsInfo::insert($requestdata['otherinfo_array']);
            }

            if(!empty($requestdata['notes_array'])){
                foreach ($requestdata['notes_array'] as $key => $val) {
                    WordNotes::where(['word_id'=>$val['word_id'],'scholar'=>$val['scholar']])->delete(); // ,'note_id'=>$val['note_id']
                    WordNotes::insert($val);
                }
            }
            
            DB::commit();

            \Session::put('word_references',[]);

            return redirect('/dashboard/'.$input['previous_url'])->with('success','Data Updated');
            
        } catch (\Throwable $th) {
            DB::rollback();            
            return redirect()->back()->with('error',$th->getMessage());
        }
    }

    private function arrangeRequestData($input)
    {
        // echo json_encode( $input );exit;
        // echo '<pre>';print_r($input);die;

        $word_preferences_array = $word_numbers_array = $otherinfo_array = $notes_array = $reference_word_translations = $phrase_reference_word_translations = [];
        
        $references = WordReferences::where('scholar',$input['ref_auth_id'])->where('word_id',$input['ref_word_id'])->get()->toArray();

        $word_references = \Session::has('word_references') ? \Session::get('word_references') : [];
        $word_existing_references[$input['ref_word_id']] = array_column( $references, 'reference_word_id' );

        if ( empty( $word_references ) || !array_key_exists( $input['ref_word_id'], $word_references ) ) {
            $word_references = $word_existing_references;
        }
        
        $phrase_word_references = \Session::has('phrase_word_references') ? \Session::get('phrase_word_references') : [];
        // echo '<pre>';print_r($word_references);die;
        $scholarId = (isset($input['ref_auth_id']) && !empty($input['ref_auth_id'])) ? $input['ref_auth_id'] : Auth::id();

        if (!empty($input['words_translations'])) {
            foreach (@$input['words_translations'] as $key => $value) {
            
                // MAKE WORD PREFERENCES ARRAY
                if ($value['reference_type'] == "by_reference" && array_key_exists($key, $word_references)) {
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
                                'um_ul_kitaab'=> $value['um-ul-kitaab'] ?? '',
                                'is_reference_word' => 1,
                                'created_at' => \Carbon\Carbon::now()
                            ];
                        }
                    }
                }

                // Make WORD NUMBERS ARRAY
                if (@$value['reference_type_number'] > 1  && array_key_exists($key, $phrase_word_references)) {
                    $wordIndex = array_search($key, array_values($input['word_ids']));
                    $slice = array_slice($input['word_ids'], $wordIndex, $value['reference_type_number']);
                    array_shift($slice);


                    // foreach ($slice as $val) {
                    foreach ($phrase_word_references[$key] as $val) {
                        $word_numbers_array[] = [
                            'word_id' => $key,
                            'scholar' => $scholarId,
                            'phrase_word_id' => $val,
                            'created_at' => \Carbon\Carbon::now(),
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
                    'reference_type_number' => $value['reference_type_number'] ?? 1,
                    'created_at' => \Carbon\Carbon::now()
                ];
            }
        }

        // NOTES AGAINST WORDS AND SCHOLARS
        if (!empty($input['word_references'])) {
            $filter_references = array_filter($input['word_references']);
            foreach ($filter_references as $key => $value) {
                foreach (array_filter($value) as $ikey => $val) {
                    $notes_array[] = [
                        'word_id' => $key,
                        'note_id' => $val,
                        'scholar' => $scholarId,
                        'created_at' => \Carbon\Carbon::now()
                    ];
                }
            }
        }
        // echo 'word reference array <pre>';print_r($otherinfo_array);die;
        return [
            'notes_array'       => $notes_array,
            'word_numbers_array' => $word_numbers_array,
            'otherinfo_array' => $otherinfo_array,
            'word_preferences_array' => $word_preferences_array,
            'reference_word_translations' => $reference_word_translations,
            'phrase_reference_word_translations' => $phrase_reference_word_translations
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
