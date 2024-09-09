@extends('./layout/layout')

@section('content')

<div class="container-fluid mt-3">
	<div class="card">
		<div class="card-header">
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('{{$action}}/create');">
			  Add New {{@$singular}}
			</button>
			<div class="card-body">
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Short Name</th>							
							<th width="7%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{$val['name']}}</td>
							<td>{{!empty($val['short_name'])?$val['short_name']:'-'}}</td>
							<td>
								<div class="btn-group" role="group" aria-label="Basic example">
								  <button type="button" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('{{$action}}/{{$val['id']}}/edit')">Edit</button>
								  <button type="button" data-url="{{url($action.'/delete/'.$val['id'])}}" data-remove="list_{{$val['id']}}" class="btn btn-danger delete">Delete</button>
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
