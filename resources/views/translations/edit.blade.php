@extends('layout.layout')
@section('content')
<div class="card bg-white mt-4">
<form method="post" action="{{url('dashboard/update-translation')}}"> 
    @csrf
<div class="card-header" dir="rtl">{{ $row['arabic'] }}</div>
<input type="hidden" name="previous_url" value="{{$previous_url}}">
<input type="hidden" name="ayat_id" value="{{$row['id']}}">
<input type="hidden" name="auth_id" value="{{$auth_id}}">
<div class="card-body">
    <div class="accordion mt-4" id="accordionExample">
        <div class="accordion-item mt-3">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button text-black" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse-ayat" aria-expanded="true" aria-controls="collapseOne">
                    Ayat Translation
                </button>
            </h2>
            <div id="collapse-ayat"
            class="accordion-collapse collapse show"
            aria-labelledby="headingOne" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <div class="row">
                    @foreach ($row['ayats_translations'] as $val)
                    <div class="col">
                        <div class="form-group">
                            <label for="language">{{ $val['language_name'] }}</label>
                            <input type="text" class="form-control" id="language"
                            aria-describedby="emailHelp" value="{{$val['translation']}}" 
                            name="ayat_translations[{{ $val['scholar'] }}][{{ $val['language'] }}]"
                            @if ($val['language'] == 1) dir="rtl" @endif>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
<div class="accordion mt-4" id="accordionExample">
    <div class="accordion-item mt-3">
        <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button text-black" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse-words" aria-expanded="true" aria-controls="collapseOne">
            <h6>Words Translations</h6>
        </button>
    </h2>
    <div id="collapse-words" class="accordion-collapse collapse show" aria-labelledby="headingOne"
    data-bs-parent="#accordionExample">
    <div class="accordion-body">        
        <table class="table table-bordered table-striped" dir="rtl">
            <thead>
                <tr dir="ltr">
                    <td>#</td>
                    <td>Word</td>                    
                    @foreach ($languages as $val)
                    <td>{{ $val['name'] }}</td>
                    @endforeach
                    <td>AbrahamicLocution Reference</td>
                    <td>Addresser</td>
                    <td>Addressee</td>
                    <td width="20%">References</td>
                </tr>
            </thead>
            <tbody>
                @if(!empty($words))
                @foreach($words as $key => $val)
                <input type="hidden" name="word_ids[]" value="{{$val['id']}}">
                <tr class="mt-2 list_{{++$key}} @if(in_array($val['id'],$phrases_words) || !empty($val['single_reference_word'])) disabled @endif @if(!empty($val['single_reference_word'])) reference-disabled @endif" id="word_{{$val['id']}}">
                    <td dir="ltr">({{ $row['surahNo'] }}:{{ $row['ayatNo'] }}:{{ $val['reference'] }})</td>
                    <td dir="ltr">
                        {{ $val['word'] }}
                        @if(!empty($val['single_reference_word']['word']['word']))
                            <div class="refered_word">
                                <div>
                                    <span class="badge bg-info">
                                        {{$val['single_reference_word']['word']['word']}}

                                        ({{$val['single_reference_word']['word']['surah_no'].':'.$val['single_reference_word']['word']['ayat_no'].':'.$val['single_reference_word']['word']['reference']}})
                                    </span>
                                </div>
                                <div>
                                    <span class="badge bg-info">
                                        Auth: {{$val['single_reference_word']['scholar_info']['short_name'] ?? ''}}
                                    </span>
                                </div>
                                <div class="remove-preference-word" data-id="{{$val['id']}}" data-url="remove-word-preference" data-auth="{{$auth_id}}" style="cursor:pointer;">
                                    <span class="badge bg-danger"><i class="bi bi-x"></i> Delete</span>
                                </div>
                            </div>
                        @endif

                        @if(!empty($val['phrase_reference_word']['word']['word']))
                            <div class="refered_word">
                                <div>
                                    <span class="badge bg-info">
                                        {{$val['phrase_reference_word']['word']['word']}}

                                        ({{$val['phrase_reference_word']['word']['surah_no'].':'.$val['phrase_reference_word']['word']['ayat_no'].':'.$val['phrase_reference_word']['word']['reference']}})
                                    </span>
                                </div>
                                <div>
                                    <span class="badge bg-info">
                                        Auth: {{$val['phrase_reference_word']['scholar_info']['short_name'] ?? ''}}
                                    </span>
                                </div>
                                
                                @if ( $val[ 'word' ] == $val[ 'phrase_reference_word' ][ 'word' ][ 'word' ] )
                                <div class="remove-phrase-preference-word" data-id="{{$val['id']}}" data-ref-id="{{ join( ',', $phrases_words ) }}" data-url="remove-phrase-word-preference" data-auth="{{$auth_id}}" style="cursor:pointer;">
                                    <span class="badge bg-danger"><i class="bi bi-x"></i> Delete</span>
                                </div>
                                @endif
                                
                            </div>
                        @endif
                    </td>                    
                    @foreach ($languages as $value)
                    <td>
                        <input type="text"
                        name="words_translations[{{ $val['id'] }}][language][{{ $value['id'] }}]"
                        class="form-control" value="{{@$val['translations'][$value['id']]}}" @if ($value['id'] === 2) dir="ltr" @endif>
                    </td>
                    @endforeach
                    <td dir="ltr">
                        @if(@$val['other_word_info']['reference_type']=='by_reference')
                            <a href="#" id="view-word-reference" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('/dashboard/get-related-words','{{$val['id']}}','view','{{$auth_id}}')">View Refered words</a>
                        @endif
                        <select class="form-control word_references_types" data-id="{{$val['id']}}" data-previous-value="{{@$val['other_word_info']['reference_type']}}" name="words_translations[{{ $val['id'] }}][reference_type]">
                            <option value="">---Select---</option>
                            @foreach(\Config('constants.references') as $refkey => $refval)
                                <option value="{{$refkey}}" @if($refkey==@$val['other_word_info']['reference_type']) selected @endif>{{$refval}}</option>
                            @endforeach
                        </select>
                        <select class="form-control search-select2 @if(!in_array(@$val['other_word_info']['reference_type'],['by_number','No','both']))d-none @endif" id="words_number" data-id="{{$val['id']}}" name="words_translations[{{ $val['id'] }}][reference_type_number]">
                            @for($i=1;$i<=20;$i++)
                                <option value="{{$i}}" @if($i==@$val['other_word_info']['reference_type_number']) selected @endif>{{$i}}</option>
                            @endfor
                        </select>
                    </td>
                    <td><input type="text" class="form-control" name="words_translations[{{ $val['id'] }}][addresser]" value="{{@$val['other_word_info']['addresser']}}"></td>
                    <td><input type="text" class="form-control" name="words_translations[{{ $val['id'] }}][addressee]" value="{{@$val['other_word_info']['addressee']}}"></td>
                    <td dir="ltr">
                        <select class="form-control notes w-100 my-2 word_reference" name="word_references[{{ $val['id'] }}][]" multiple="multiple">
                            <option>Select References</option>
                            @foreach($notes as $key => $ival)
                            <option value="{{$ival['id']}}" @if(in_array($ival['id'],$val['notes'])) selected @endif>{{$ival['note_label']}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <div id="ayat_word_ids" class="d-none">{{$word_ids}}</div>
        </table>
    </div>
</div>
</div>
</div>
<div class="row">
    <div class="col-12 mt-3">
        <button class="btn btn-primary" type="submit">Update</button>
    </div>
</div>
</div>
</form>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.notes').select2({ width: '100%' });
        const wordIds = JSON.parse('{{$word_ids}}');
        $('.disabled').find('input,select').attr('disabled',true);
    });
</script>
@endsection