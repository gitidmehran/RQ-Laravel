@extends('layout.layout')
@section('content')
<form method="post" action="{{url('dashboard/save-word-references')}}" class="make_ajax" enctype="multipart/form-data">
<div class="row mt-3">
	<div class="col-12 my-2">
		<div class="form-check mx-3">
		  <input class="form-check-input" type="checkbox" value="" id="exactWordCheckbox">
		  <label class="form-check-label" for="exactWordCheckbox">
		    Exact Words
		  </label>
		</div>
	</div>
	<div class="col-12">
        <div class="matching-words">
            <table class="table table-bordered table-striped" dir="rtl">
                <thead>
                    <th col="1">
                        <input type="checkbox" class="form-check-input word-reference-checked" value="" @if($checked_all) checked @endif>
                    </th>
                    <th col="1">Reference</th>
                    <th col="10">Word</th>
                    <th dir="ltr" class="right">
                        <div>Total Words: {{count(@$related_words)}}</div>
                        <div>Selected Words: <span id="countNew">0</span></div>
                    </th>
                </thead>
                <tbody>
                    @foreach(@$related_words as $key => $val)
                        <tr>
                            <td width="1%">
                                <div class="form-check">
                                  <input class="form-check-input related-words" name="reference_words[]" type="checkbox" value="{{$val['id']}}" id="defaultCheck{{$val['id']}}" @if(in_array($val['id'],$words)) checked @endif>
                                </div>
                            </td>
                            <td width="2%">{{$val['ayat']['surahNo'].':'.$val['ayat']['ayatNo'].':'.$val['reference']}}</td>
                            <td>{{$val['word']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="exact-words d-none">
            <table class="table table-bordered table-striped" dir="rtl">
                <thead>
                    <th col="1">
                        <input type="checkbox" class="form-check-input word-reference-checked" value="" @if($checked_all) checked @endif>
                    </th>
                    <th col="1">Reference</th>
                    <th col="10">Word</th>
                    <th dir="ltr" class="right">
                        <div>Total Words: {{count(@$exact_words)}}</div>
                        <div>Selected Words: <span id="countNew">0</span></div>
                    </th>
                </thead>
                <tbody>
                    @foreach(@$exact_words as $key => $val)
                        <tr>
                            <td width="1%">
                                <div class="form-check">
                                  <input class="form-check-input related-words" name="reference_words[]" type="checkbox" value="{{$val['id']}}" id="defaultCheck{{$val['id']}}" @if(in_array($val['id'],$words)) checked @endif>
                                </div>
                            </td>
                            <td width="2%">{{$val['ayat']['surahNo'].':'.$val['ayat']['ayatNo'].':'.$val['reference']}}</td>
                            <td>{{$val['word']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
	</div>
</div>
<button type="submit" class="btn btn-primary">save</button>
</form>
@endsection
<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function(){
        $(document).on('change','#exactWordCheckbox',function(){
            let selectedClass = '';
            if($(this).is(':checked')){
                $('.matching-words').addClass('d-none');
                $('.exact-words').removeClass('d-none');
                selectedClass = 'exact-words';
            }else{
                $('.matching-words').removeClass('d-none');
                $('.exact-words').addClass('d-none');
                selectedClass = 'matching-words';
            }
            const count  = $('.'+selectedClass).find('input[type="checkbox"]:checked').length;
            $(`.${selectedClass}`).find('input').attr('disabled',false);
            $(`.${selectedClass} > #countNew`).text(count)
            disableHiddenInputs();
        });
		function disableHiddenInputs(){
            const selectedClass = $('.d-none');
            $(selectedClass).find('input').attr('disabled',true);
        }
        disableHiddenInputs();
	});
</script>