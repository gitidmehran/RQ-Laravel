@extends('layout/layout')
@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
	$(document).ready(function myFunction() {
		$("#myInput").on("change", function() {
			var value = $(this).val();
			const myArray = value.split(" ", 5);
			$(document).ready("myInput").innerHTML = myArray;
			$("#myTable tr").filter(function() {
				$(this).toggle($(this).text().indexOf(value) > -1)

			});
		});
	});
</script>
<div class="container-fluid">
	<div class="row mt-3">
		<div class="col-2">
		</div>
		<div class="col-8">
			<div class="card">
				<h5 class="card-header bg-secondary">
					Add Root Word Meaning
				</h5>


				<!-- <button id="myInput" type="button" class="btn-link"  value="أ">أ</button> -->

			</div>

			<div align="right" class="sectionArea m-2">
				<a href="{{url('/dashboard/word/A')}}" class="selected">أ</a>
				<a href="{{url('/dashboard/word/b')}}">ب</a>
				<a href="{{url('/dashboard/word/t')}}">ت</a>
				<a href="{{url('/dashboard/word/v')}}">ث</a>
				<a href="{{url('/dashboard/word/j')}}">ج</a>
				<a href="{{url('/dashboard/word/hh')}}">ح</a>
				<a href="{{url('/dashboard/word/x')}}">خ</a>
				<a href="{{url('/dashboard/word/d')}}">د</a>
				<a href="{{url('/dashboard/word/st')}}">ذ</a>
				<a href="{{url('/dashboard/word/r')}}">ر</a>
				<a href="{{url('/dashboard/word/z')}}">ز</a>
				<a href="{{url('/dashboard/word/s')}}">س</a>
				<a href="{{url('/dashboard/word/dl')}}">ش</a>
				<a href="{{url('/dashboard/word/ss')}}">ص</a>
				<a href="{{url('/dashboard/word/dd')}}">ض</a>
				<a href="{{url('/dashboard/word/tt')}}">ط</a>
				<a href="{{url('/dashboard/word/zz')}}">ظ</a>
				<a href="{{url('/dashboard/word/ee')}}">ع</a>
				<a href="{{url('/dashboard/word/g')}}">غ</a>
				<a href="{{url('/dashboard/word/f')}}">ف</a>
				<a href="{{url('/dashboard/word/q')}}">ق</a>
				<a href="{{url('/dashboard/word/k')}}">ك</a>
				<a href="{{url('/dashboard/word/l')}}">ل</a>
				<a href="{{url('/dashboard/word/m')}}">م</a>
				<a href="{{url('/dashboard/word/n')}}">ن</a>
				<a href="{{url('/dashboard/word/h')}}">ه</a>
				<a href="{{url('/dashboard/word/w')}}">و</a>
				<a href="{{url('/dashboard/word/y')}}">ي</a>
			</div>
			<div align="right" class="ml-2">
				<select id="myInput" dir="rtl" onchange="myFunction()" />
				@foreach($root_word_meanings as $r)
				<option value="{{$r->root_word}}">{{$r->root_word}}</option>
				@endforeach
				</select>
				<div>
					<div class="card-body">
						<table class="table table-striped table-hover table-condensed" dir="rtl">
							<thead>
								<tr>
									<th><strong>Root Word</strong></th>
									<th><strong>Add Urdu Meaning</strong></th>
									<th><strong>Add English Meaning</strong></th>
									<th><strong>English Root Word</strong></th>
									<th><strong>Action</strong></th>
								</tr>
							</thead>
							<tbody id="myTable">

								@foreach($root_word_meanings as $r)
								<form id="{{$r->id}}" method="post" action="{{url('/dashboard/add-meanings').'/'.$r->id}}">
									@csrf
									<tr>
										<td>
											{{$r->root_word}}
										</td>
										<td>
											<div class="form-group">
												<input type="text" value="{{$r->meaning_urdu}}" name="rootwordmeaningurdu" class="form-control" id="exampleInputEmail2">
											</div>
										</td>
										<td dir="ltr">
											<div class="form-group">
												<input type="text" value="{{$r->meaning_eng}}" name="rootwordmeaningeng" class="form-control" id="exampleInputEmail1">
											</div>
										</td>
										<td>
											{{$r->english_root_word}}
										</td>
										<td>
											<button type="submit" class="btn btn-primary">Submit</button>
										</td>
									</tr>
								</form>
								@endforeach
							</tbody>
							<div class="col-2">
							</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endsection