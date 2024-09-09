@extends('./layout/layout')

@section('content')

<div class="container-fluid mt-3">
	<div class="card bg-white">
		<div class="card-header">
			<a class="btn btn-primary" href="{{url('/dashboard/'.$action.'/create')}}">
			  Add New {{@$singular}}
			</a>
			<div class="card-body">
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Title</th>
							<th>Number of Ayats</th>
							<th width="7%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{$val['title']}}</td>
							<td>{{$val['id']}}</td>
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
