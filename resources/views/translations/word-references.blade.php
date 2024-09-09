<form method="post" action="{{url('dashboard/save-word-references')}}" class="make_ajax" enctype="multipart/form-data">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">{{$title}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
    <input type="hidden" name="word_id" value="{{$single_word['id']}}">
    <div class="row">
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
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Save</button>
</div>
</form>
{{-- <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script> --}}
<script type="text/javascript">
    if(typeof  jQuery === "undefined"){
        const script_tag = document.createElement('script');
        script_tag.setAttribute("type","text/javascript");
        script_tag.setAttribute("src","{{ asset('js/jquery-3.6.0.min.js') }}");
    }
    $(document).ready(function(){
        var selectedClass = 'matching-words';
        $(`.${selectedClass}`).find('#defaultCheck' + $('input[name=word_id]').val()).prop('checked', true).change().hide();
        getCheckedWordsCount();
        
        $(document).on('change','#exactWordCheckbox',function(){
            if($(this).is(':checked')){
                $('.matching-words').addClass('d-none');
                $('.exact-words').removeClass('d-none');
                selectedClass = 'exact-words';
            }else{
                $('.matching-words').removeClass('d-none');
                $('.exact-words').addClass('d-none');
                selectedClass = 'matching-words';
            }
            $(`.${selectedClass}`).find('input').attr('disabled',false);
            $(`.${selectedClass}`).find('#defaultCheck' + $('input[name=word_id]').val()).prop('checked', true).change().hide();
            disableHiddenInputs();
        });
        function disableHiddenInputs(){
            $('.d-none').find('input').prop('checked',false).attr('disabled',true);
        }
        disableHiddenInputs();

        // ON WORD REFERENCE CHECKBOX CHECKED CHECK OTHER RELATED WORDS
        $(document).on('change','.word-reference-checked',function(){
            $(`.${selectedClass}`).find('.related-words').prop('checked',$(this).is(':checked'));
            $(`.${selectedClass}`).find('#defaultCheck' + $('input[name=word_id]').val()).prop('checked', true).change().hide();
            getCheckedWordsCount();
        });
        $(document).on('change','.form-check-input',function(){
            getCheckedWordsCount();
        });

        function getCheckedWordsCount(){
            const total=$(`.${selectedClass}`).find(".related-words:checked").length;            
            $(`.${selectedClass}`).find('#countNew').text(total);
        }
    });
</script>