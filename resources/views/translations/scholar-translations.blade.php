@extends('layout.layout')
@section('content')
<div class="container-fluid">
	<div class="row mt-3 mb-3">
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
									<option value="{{$value['id']}}" @if(isset($filter) && @$filter['surah']==$value['id']) selected @endif>{{$value['arabic']}}-{{$value['id']}}</option>
									@endforeach
								</select>
							</div>
							<div class="col-3">
								<label>Select Verse</label>
								<select class="form-control from_verse" name="verse">
									@if(!empty($share_ayats) && !empty($filter))
									@foreach($share_ayats as $key => $val)
									<option value="{{$val['ayatNo']}}" @if(isset($filter) && @$filter['verse']==$val['ayatNo']) selected @endif>{{$val['ayatNo']}}</option>
									@endforeach
									@endif
								</select>
							</div>
							<div class="col-2">
								<label>Search By Word</label>
								<input type="text" name="search" class="form-control" value="{{@$filter['search']}}">
							</div>
							<div class="col-2">
								<label>Lines PerPage</label>
								<div class="input-group mb-3">
									<select class="form-control" name="per_page">
										@foreach(\Config('constants.pagination') as $val)
											<option value="{{$val}}" @if(isset($filter) && @$filter['per_page']==$val) selected @endif>{{$val}}</option>
										@endforeach
									</select>
									<div class="input-group-append">
										<span class="input-group-text" id="basic-addon2">{{$total_records ?? count($list)}}</span>
									</div>
								</div>
							</div>
							<div class="col-1">
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
		<div class="col-12">
			<div class="card card-body">
				@if(\Session::has('success'))
	                <div class="alert alert-success" role="alert">{{\Session::get('success')}}</div>
	            @endif
	            <table class="table table-bordered" dir="rtl">
					<thead>
						<th>#</th>
						<th width="30%">Ayat</th>
						@foreach(@$language_names as $key => $name)
							<th>{{$name}}</th>
						@endforeach
						@if(\Auth::user()->role !==3)
						<th>Scholar</th>
						@endif
						<th width="7%">Action</th>
					</thead>
					<tbody class="list">
						@foreach(@$list as $key => $val)
							<tr class="list_{{$val['id']}}">
								<td>{{$val['surahNo'].':'.$val['ayatNo']}}</td>
								<td class="arabic-word-font">{{$val['arabic']}}</td>
								@foreach($val['languages'] as $ikey => $value)
									<td class="@if($ikey=="Urdu") urdu-word-font @endif text-justify"  @if($ikey !="Urdu") dir="ltr" @endif>
										{!! !empty($value)?$value:'<span class="badge bg-danger">N/A</span>' !!}
									</td>
								@endforeach
								@if(\Auth::user()->role !==3)
									<td>{{$val['scholar_name'] ?? ''}}</td>
								@endif
								<td dir="ltr">
									<div class="dropdown">
									    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
									    <i class="bi bi-gear"></i> Actions
									    </button>
									    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
										    <li>
										    	<a class="dropdown-item" href="{{url('/dashboard/translation/'.$val['id'].'/edit/'.$val['scholar_id'])}}"><i class="bi bi-pencil-square"></i> Edit</a>
										    </li>
										    <li>
										    	<a class="dropdown-item delete" data-url="{{url('/dashboard/translation/delete/'.$val['id'])}}" data-remove="list_{{$val['id']}}" href="#"><i class="bi bi-x-square"></i> Delete</a>
										    </li>
									    </ul>
									 </div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection