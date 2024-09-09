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
								<label>From Verse</label>
								<select class="form-control from_verse" name="from_verse">
									@if(!empty($share_ayats) && !empty($filter))
									@foreach($share_ayats as $key => $val)
									<option value="{{$val['ayatNo']}}" @if(isset($filter) && @$filter['from_verse']==$val['ayatNo']) selected @endif>{{$val['ayatNo']}}</option>
									@endforeach
									@endif
								</select>
							</div>
							<div class="col-2">
								<label>To Verse</label>
								<select class="form-control to_verse" name="to_verse">
									@if(!empty($share_ayats) && !empty($filter))
									@foreach($share_ayats as $key => $val)
									<option value="{{$val['ayatNo']}}" @if(isset($filter) && @$filter['to_verse']==$val['ayatNo']) selected @endif>{{$val['ayatNo']}}</option>
									@endforeach
									@endif
								</select>
							</div>
							<div class="col-2">
								<label>Lines PerPage</label>
								<div class="input-group mb-3">
									<select class="form-control" name="per_page">
										<option value="50" @if(isset($filter) && @$filter['per_page']==50) selected @endif>50</option>
										<option value="100" @if(isset($filter) && @$filter['per_page']==100) selected @endif>100</option>
										<option value="500" @if(isset($filter) && @$filter['per_page']==500) selected @endif>500</option>
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
</div>