<?php

namespace App\Http\Controllers;

use App\Models\InfoData;
use App\Models\Story;
use Illuminate\Http\Request;
use Session;

class StoryController extends Controller
{
    protected $singular = "story";
    protected $plural = "stories";
    protected $action = "stories";
    protected $view = "stories.";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Story::all()->toArray();
        $data = [
            'singular' => $this->singular,
            'plural' => $this->plural,
            'page_title' => $this->singular.' List',
            'action' => $this->action,
            'list' => $list,
        ];

        return view($this->view.'list',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $storyAyats = Session::get('story_ayats') ?? [];
        $ayatIds = !empty($storyAyats)?array_column($storyAyats, 'ayat_id'):[];
        echo '<pre>';print_r($storyAyats);die;
        $ayatData = InfoData::whereIn('id',$ayatIds)->get()->toArray();
        $list = [];
        if(!empty($storyAyats)){
            foreach ($storyAyats as $key => $value) {
                $list[] = $value;
                $filterdata = array_filter($ayatData,function($q) use($value){
                    return $q['id']==$value['ayat_id'];
                });
                $ayat = reset($filterdata);
                $list[$key]['ayat_no'] = $ayat['ayatNo'] ?? '';
                $list[$key]['arabic'] = $ayat['arabic'] ?? '';
            }
        }
        
        $data = [
            'singular' => $this->singular,
            'plural' => $this->plural,
            'page_title' => 'Add New '.$this->singular,
            'action' => $this->action,
            'list' => $list
        ];
        return view($this->view.'create-view',$data);
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
     * @param  \App\Models\Story  $story
     * @return \Illuminate\Http\Response
     */
    public function show(Story $story)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Story  $story
     * @return \Illuminate\Http\Response
     */
    public function edit(Story $story)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Story  $story
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Story $story)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Story  $story
     * @return \Illuminate\Http\Response
     */
    public function destroy(Story $story)
    {
        //
    }

    public function getAyats(Request $request){
        $params = $request->get('params');
        $ayats = InfoData::where('surahNo',$params)->get()->toArray();
        $data = [
            'singular' => $this->singular,
            'plural' => $this->plural,
            'action' => 'dashboard/'.$this->action.'/add-ayats',
            'surah' => $params,
            'page_title' => 'Add Story Ayats',
            'ayats' => $ayats
        ];
        return view($this->view.'create',$data);
    }

    public function addStoryAyats(Request $request){
        $input = $request->all();       
        $sessiondata = Session::has('story_ayats')?Session::get('story_ayats'):[];
        foreach ($input['ayats_data'] as $key => $value) {
            if(isset($value['ayat_id']) && !empty($value['ayat_id'])){
                $sessiondata[] = [
                    'surah_id' => $input['surah_id'],
                    'ayat_id'  => $value['ayat_id'],
                    'roles'    => $value['roles'],
                    'sequence' => $value['sequence'],
                ];
            }
        }
        Session::put('story_ayats',$sessiondata);
        return response()->json([
            'success'=>true,
            'message'=>$this->singular.' Ayats Added Successfully',
            'action'=>'reload'
        ]);
    }
}
