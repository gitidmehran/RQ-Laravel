<div class="card bg-white">
    <div class="card-header" dir="rtl">{{ $ayat['arabic'] }}</div>
    <div class="card-body">
        <input type="hidden" name="ayat_id" value="{{$ayat['id']}}">
        <input type="hidden" name="auth_id" value="{{$auth_id}}">
        <div class="accordion mt-4" id="accordionExample">
            @foreach ($scholars as $key => $list)
            <div class="accordion-item mt-3">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button text-black" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $list['id'] }}" aria-expanded="true" aria-controls="collapseOne">
                    <h6>{{ $list['name'] }}</h6>
                </button>
            </h2>
            <div id="collapse-{{ $list['id'] }}" class="accordion-collapse collapse @if ($key === 0) show @endif" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="row" dir="rtl">
                        @foreach ($languages as $val)
                        <div class="col">
                            <div class="form-group" @if($val['id']==2) dir="ltr" @endif>
                                <label for="language">{{ $val['name'] }}</label>
                                <input type="text" class="form-control" id="language"
                                aria-describedby="emailHelp"
                                name="ayat_translations[{{ $list['id'] }}][{{ $val['id'] }}]"
                                @if ($val['id'] == 1) dir="rtl" @endif>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        @endforeach
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
                <thead class="fixed">
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
                    @foreach ($ayat['words'] as $key => $val)
                    <input type="hidden" name="word_ids[]" value="{{$val['id']}}">
                    <tr class="mt-2 list_{{++$key}} @if( !empty($val['phrase_reference_word']) || !empty($val['single_reference_word'])) disabled @endif" id="word_{{$val['id']}}">
                        <td dir="ltr">({{ $ayat['surahNo'] }}:{{ $ayat['ayatNo'] }}:{{ $val['reference'] }})</td>
                        <td dir="ltr">
                            {{ $val['word'] }}
                            @if(!empty($val['single_reference_word']['word']['word']))
                                <div class="refered_word">
                                    <div>
                                        <span class="badge bg-info">
                                            {{$val['single_reference_word']['word']['word']}}

                                            ({{$val['single_reference_word']['word']['ayat']['surahNo'].':'.$val['single_reference_word']['word']['ayat']['ayatNo'].':'.$val['single_reference_word']['word']['reference']}})
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

                                            ({{$val['phrase_reference_word']['word']['ayat']['surahNo'].':'.$val['phrase_reference_word']['word']['ayat']['ayatNo'].':'.$val['phrase_reference_word']['word']['reference']}})
                                        </span>
                                    </div>
                                    <div class="remove-preference-word" data-id="{{$val['id']}}" data-url="remove-word-preference" data-auth="{{$auth_id}}" style="cursor:pointer;">
                                        <span class="badge bg-danger"><i class="bi bi-x"></i> Delete</span>
                                    </div>
                                </div>
                            @endif
                        </td>  
                        @foreach ($languages as $value)
                        <td>
                            <input type="text"
                            name="words_translations[{{ $val['id'] }}][language][{{ $value['id'] }}]"
                            class="form-control"
                            @if ($value['id'] === 2) dir="ltr" @endif>
                        </td>
                        @endforeach
                        <td dir="ltr">
                            <select class="form-control word_references_types" data-id="{{$val['id']}}" name="words_translations[{{ $val['id'] }}][reference_type]">
                                <option value="">---Select---</option>
                                @foreach(\Config('constants.references') as $ikey => $ival)
                                    <option value="{{$ikey}}" @if(array_key_exists($val['id'], $word_references) && $ikey=="by_reference") selected @endif>{{$ival}}</option>
                                @endforeach
                            </select>
                            <select class="form-control search-select2 d-none" id="words_number" data-id="{{$val['id']}}" name="words_translations[{{ $val['id'] }}][reference_type_number]">
                                @for($i=1;$i<=20;$i++)
                                <option value="{{$i}}">{{$i}}</option>
                                @endfor
                            </select>
                        </td>
                        <td><input type="text" class="form-control" name="words_translations[{{ $val['id'] }}][addresser]"></td>
                        <td><input type="text" class="form-control" name="words_translations[{{ $val['id'] }}][addressee]"></td>
                        <td dir="ltr">
                            <select class="form-control notes w-100 my-2 word_reference" name="word_references[{{ $val['id'] }}][]" multiple="multiple">
                                <option>Select References</option>
                                @foreach($notes as $key => $val)
                                <option value="{{$val['id']}}">{{$val['note_label']}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div id="ayat_word_ids" class="d-none">{{$word_ids}}</div>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.notes').select2({ width: '100%' });
        $('.disabled').find('input,select').attr('disabled',true);
    });
</script>
</div>    
</div>
