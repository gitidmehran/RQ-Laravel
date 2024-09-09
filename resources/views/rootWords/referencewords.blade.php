@extends('layout/layout')

@section('header-scripts')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.0.2/css/searchPanes.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.4.0/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
@endsection

@section('content')

<div class="container-fluid">
	<div class="row mt-3 justify-content-center">
		<div class="col">
			<div class="card">
				<h5 class="card-header bg-secondary">
					Reference Word Meaning
				</h5>

				<form action="{{ url('/dashboard/get-reference-words-by-scholar') }}" method="get">
					<div class="row m-3">
						<div class="col-4">
							<label>Scholar Selection</label>
							<select class="form-control" name="scholar">
								<option value="">Select Scholars (From Settings)</option>
								@if(!empty($scholars))
									@foreach($scholars as $key => $val)
										<option value="{{$val['id']}}" @if(isset($scholar_id) && @$scholar_id==$val['id']) selected @endif>{{$val['name']}}</option>
									@endforeach
								@endif
							</select>
						</div>
						<div class="col-2">
							<button class="btn btn-primary mt-3">Search</button>
						</div>
					</div>
				</form>

				<!-- <div class="col-12">
					{!! isset($links)?$links:'' !!}
				</div> -->
				<!-- <button id="myInput" type="button" class="btn-link"  value="أ">أ</button> -->

			</div>

			<div class="sectionArea m-2 row justify-content-end">
				<div class="col-2">
					<label>Search by letter</label>
					<select id="myInput" class="form-control" name="letter">
						<option value="">Select letter</option>
						@if( isset( $letters ) )
							@foreach( $letters as $letter )
								<!-- <a href="{{url('/dashboard/get-reference-words-by-scholar/').'/'.$scholar_id.'/'.$letter}}">{{ $letter }}</a> -->
								<option style="text-align: right; padding-right: 5px;" value="{{ $letter }}">{{ $letter }}</option>
							@endforeach
						@endif
					</select>
				</div>
			</div>

			<div class="card-body">
				<table id="myTable" class="table table-striped table-hover table-condensed compact" dir="rtl">
					<thead>
						<tr>
							<th><strong>#</strong></th>
							<th><strong>Reference Word</strong></th>
							<th><strong>Urdu Meaning</strong></th>
							<th><strong>English Meaning</strong></th>
							<th><strong>Um-Al-Kitab</strong></th>
							<th><strong>addresser</strong></th>
							<th><strong>addressee</strong></th>
							<th><strong>References</strong></th>
							<th><strong>Action</strong></th>
						</tr>
					</thead>
					<tbody>

						@foreach($word_reference as $k => $r)
						<tr>
						<form id="{{ $r->id }}" method="post" action="{{ url('/dashboard/edit_reference_word_translation').'/'.$scholar_id }}">
							@csrf
							<input type="hidden" name="ref_word_id" value="{{ $r->id }}">
							<input type="hidden" name="ref_auth_id" value="{{ $scholar_id }}">
							<input type="hidden" name="words_translations[{{ $r->id }}][reference_type]" value="@if( @$r->otherWordInfo->reference_type == 'by_reference' ) by_reference @endif">
								<td>
									@if(@$r->otherWordInfo->reference_type == 'by_reference')
										<a style="font-size: 12px;" href="#" id="view-word-reference{{$r->id}}" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('/dashboard/get-related-words','{{ $r->id }}','view','{{ $scholar_id }}')">View Refered words</a>
									@endif
									({{ $r->surah_no }}:{{ $r->ayat_no }}:{{ $r->reference }})
								</td>
								<td>
									{{ $r->word }}
								</td>
								
								@for ($i = 0; $i < 2; $i++)
								<td @if( isset( $r->translations[$i] ) && $r->translations[$i]->language == 2 ) dir="ltr" @endif>
									<div class="form-group">
										<input type="text" value="@if( isset( $r->translations[$i] ) ) {{ $r->translations[$i]->translation }} @endif" name="words_translations[{{ $r->id }}][language][{{ $i+1 }}]" class="form-control" id="exampleInputEmail2">
									</div>
								</td>
								@endfor
								
								<td dir="ltr">
									<div class="form-group">
										<input type="text" value="@if( isset( $r->translations[0] ) ) {{ $r->translations[0]->um_ul_kitaab }} @endif" name="words_translations[{{ $r->id }}][um-ul-kitaab]" class="form-control" id="um-ul-kitaab">
									</div>
								</td>
								
								<td dir="ltr">
									<div class="form-group">
										<input type="text" value="@if( @$r->otherWordInfo->reference_type == 'by_reference' ) {{ $r->otherWordInfo->addresser }} @endif" name="words_translations[{{ $r->id }}][addresser]" class="form-control" id="ref_addresser">
									</div>
								</td>
								
								<td dir="ltr">
									<div class="form-group">
										<input type="text" value="@if( @$r->otherWordInfo->reference_type == 'by_reference' ) {{ $r->otherWordInfo->addressee }} @endif" name="words_translations[{{ $r->id }}][addressee]" class="form-control" id="ref_addressee">
									</div>
								</td>
								
								<td dir="ltr" style="max-width: 200px !important;">
									<select class="form-control notes my-2 word_reference" name="word_references[{{ $r->id }}][]" multiple="multiple">
										<option>Select References</option>
										@foreach($notes as $key => $ival)
											<option value="{{$ival['id']}}" @if(in_array($ival['id'],$note_id[$k])) selected @endif>{{$ival['note_label']}}</option>
										@endforeach
									</select>
								</td>
								
								<td>
									<button type="submit" class="btn btn-primary">Submit</button>
								</td>
							</form>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		
		</div>

	</div>
</div>
@endsection

@section('scripts')
<script src = "http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer ></script>
<script>

	$(document).ready(function(){

		$('.notes').select2({ width: '100%' });
		const wordIds = JSON.parse('{{$word_ids}}');
		$('.disabled').find('input,select').attr('disabled',true);

		var table = $('#myTable').DataTable({searching: true});

		$('#myInput').on( 'change', function () {

			table
				.columns( 1 ) // 0 based column
				.search( "^"+this.value, true, true, true )
				.draw();

		} );
	});
</script>
@endsection