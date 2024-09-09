@extends('./layout/layout')
@section('header-scripts')
<style type="text/css">
  table,
  th,
  td {
    border: 1px solid black;
  }

  td {
    padding: 0px 0px !important;
    text-align: center;
  }
  td p{
    margin: 0px !important;
    padding: 0px !important;
    border-bottom: 1px solid;
  }
</style>
@endsection
@section('content')
<div class="container-fluid">
  @include('filterMenu')
  <div class="row mt-3">
    @if (!empty($data))
    @foreach ($data as $key => $val)
    <div class="col-12 mt-3">
      <div class="card card-sm">
        <div class="card-body">
          <h5 class="card-title arabic-word-font" dir="rtl">
            ({{ $val['ayatNo'] }}:{{ $val['surahNo'] }})
            .
          {{ $val['arabic'] }}</h5>
          @if (!empty($val['words']))
          <div class="row mt-3">
            <div class="col-12">
              <table class="table table-bordered table-striped" dir="rtl">
                <tr>
                  @foreach ($val['words'] as $ikey => $value)
                  <td>
                    @if(empty($selectedColumns))
                    <p class="arabic-word-font">{{ $value['word'] }}</p>
                    <p class="urdu-word-font">
                      {{ !empty($value['grammatical_description']) ? $value['grammatical_description'] : '-' }}
                    </p>
                    @else
                    @foreach($selectedColumns as $key => $ival)
                    <p class="{{$ival=='grammatical_description'?'urdu-word-font':'arabic-word-font'}}">{{!empty($value[$ival])?$value[$ival]:'-'}}</p>
                    @endforeach
                    @endif
                    @if(!empty($value['regular_meaning']))
                    <p>{{$value['regular_meaning']['english_translation']}}</p>
                    @endif
                  </td>
                  @endforeach
                </tr>
              </table>
            </div>
          </div>
          @endif

          @if (!empty($val['ayats_translations']))
          @foreach ($val['ayats_translations'] as $ikey => $value)
            <div class="row mt-3">
              <div class="col-1 w-auto border">
                <strong>{{ $value['scholar'] }}</strong>
              </div>
              <div class="col-1 w-auto border">
                <span class="badge badge-pill badge-dark"
                style="background:black">{{$value['language']}}</span>
              </div>
              <div class="col-10 col-width border">
                {{ !empty($value['translation']) ? $value['translation'] : '-' }}
              </div>
            </div>
          @endforeach
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @endif

    @if (!empty($related))
    @foreach ($related as $key => $val)
    <div class="col-12 mt-3">
      <div class="card card-sm">
        <div class="card-body">
          <h5 class="card-title arabic-word-font" dir="rtl">({{ $val['ayatNo'] }}:{{ $val['surahNo'] }}).
          {{ $val['arabic'] }}</h5>
          @if (!empty($val['words']))
          <div class="row mt-3">
            <div class="col-12">
              <table class="table table-bordered table-striped" dir="rtl">
                <tr>
                  @foreach ($val['words'] as $ikey => $value)
                  <td>
                    
                    @if(empty($selectedColumns))
                    <p class="arabic-word-font">{{ $value['word'] }}</p>
                    <p class="urdu-word-font">
                      {{ !empty($value['grammatical_description']) ? $value['grammatical_description'] : '-' }}
                    </p>
                    @else
                    @foreach($selectedColumns as $key => $ival)
                    <p class="{{$ival=='grammatical_description'?'urdu-word-font':'arabic-word-font'}}">{{!empty($value[$ival])?$value[$ival]:'-'}}</p>
                    @endforeach
                    @endif
                  </td>
                  @endforeach
                </tr>
              </table>
            </div>
          </div>
          @endif
          @if (!empty($val['ayats_translations']))
          @foreach ($val['ayats_translations'] as $ikey => $value)
            <div class="row mt-3">
              <div class="col-1 w-auto border">
                <strong>{{ $value['scholar'] }}</strong>
              </div>
              <div class="col-1 w-auto border">
                <span class="badge badge-pill badge-dark"
                style="background:black">{{$value['language']}}</span>
              </div>
              <div class="col-10 col-width border">
                {{ !empty($value['translation']) ? $value['translation'] : '-' }}
              </div>
            </div>
          @endforeach
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @endif

  </div>
</div>
@endsection
