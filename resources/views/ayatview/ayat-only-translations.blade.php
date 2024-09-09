@extends('./layout/layout')
@section('content')
<div class="container-fluid">
  <div class="row my-3">
    <div class="col-12">
      <div class="card">
        <h5 class="card-header bg-success">
          Search
        </h5>
        <div class="card-body">
          <form method="get" action="{{@$search_action}}">
            <div class="row mt-3">
              <div class="col-3">
                <label>Surahs</label>
                <select class="form-control surah" name="surah">
                  <option value="">Select Surah</option>
                  @foreach($surahs as $key => $value)
                  <option value="{{$value['id']}}" @if(isset($filters) && @$filters['surah']==$value['id']) selected @endif>{{$value['arabic']}}-{{$value['id']}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-3">
                <label>Scholar Selection</label>
                <select class="form-control" name="scholar">
                  <option value="">Select Scholars (From Settings)</option>
                  @if(!empty($scholars))
                  @foreach($scholars as $key => $val)
                  <option value="{{$val['id']}}" @if(isset($filters) && @$filters['scholar']==$val['id']) selected @endif>{{$val['name']}}</option>
                  @endforeach
                  @endif
                </select>
              </div>
              <div class="col-3">
                <label>Lines PerPage</label>
                <div class="input-group mb-3">
                  <select class="form-control" name="per_page">
                    <option value="50" @if(isset($filters) && @$filters['per_page']==50) selected @endif>50</option>
                    <option value="100" @if(isset($filters) && @$filters['per_page']==100) selected @endif>100</option>
                    <option value="500" @if(isset($filters) && @$filters['per_page']==500) selected @endif>500</option>
                  </select>
                  <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2">{{@$total_records}}</span>
                  </div>
                </div>
              </div>
              <div class="col-2">
                <button class="btn btn-primary mt-3">Search</button>
              </div>
            </div>
          </form>
          <div class="col-12">
            {!! isset($links)?$links:'' !!}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-3">
    @if (!empty($list))
    @foreach ($list as $key => $val)
    <div class="col-12 mt-3">
      <div class="card card-sm">
        <div class="card-body">
          <h5 class="card-title arabic-word-font fw-bold" dir="rtl">
            ({{ $val['surahNo'] }}:{{ $val['ayatNo'] }}). {{ $val['arabic'] }}</h5>
          
          {{-- AYAT TRANSLATIONS --}}
          @if (!empty($val['ayats_translations']))
          <div class="row">
            <div class="col-12">
              @foreach($val['ayats_translations'] as $ikey => $value)
                @if(in_array($value['scholarinfo']['id'].'-'.$value['language']['id'],$ayat_scholar_checked_languages))
                  <div class="d-flex border {{$value['language']['id']==1?'flex-row-reverse':'flex-row'}}">
                    <div class="p-2 border fw-bold">{{$value['scholarinfo']['short_name']}}</div>
                    <div class="p-2 border">
                      <span class="badge bg-dark">{{$value['language']['short_name']}}</span>
                    </div>
                    <div class="p-2 @if($value['language']['id']==1) urdu-word-font @endif">{{$value['translation']}}</div>
                  </div>
                @endif  
              @endforeach
              {{-- <table class="table table-bordered">
                <tbody>
                  @foreach ($val['ayats_translations'] as $ikey => $value)
                  <tr class="">
                    <td width="6%"><strong>{{$value['scholarinfo']['short_name']}}</strong></td>
                    <td width="2%"><span class="badge bg-dark">{{$value['language']['short_name']}}</span></td>
                    <td style="text-align: {{$value['language']['id']==1?'right':'left'}}" class="@if($value['language']['id']==1) urdu-word-font @endif">{{!empty($value['translation'])?$value['translation']:'-'}}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table> --}}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @endif
  </div>
</div>
@endsection
