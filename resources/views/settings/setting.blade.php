@extends('layout/layout')
@section('content')
<div class="container-fluid">
	<div class="row mt-3">
		<div class="col-12">
			<div class="card">
				<h5 class="card-header bg-secondary">
					User Settings
				</h5>
				<div class="card-body">
					<form method="post" action="{{url('/dashboard/settings')}}" class="make_ajax">
						<input type="hidden" name="previous_url" value="{{$previous_url}}">
						<div class="accordion" id="accordionExample">
							<div class="row">
								<div class="col-6">
									<div class="accordion-item">
										<h2 class="accordion-header" id="headingOne">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#wordscholars" aria-expanded="true" aria-controls="wordscholars">
												Word By Word Quran Scholars
											</button>
										</h2>
										<div id="wordscholars" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
											<div class="accordion-body pt-0">
												<div class="row">
													<div class="col ">
														<div class='main'>Grammar Settings</div>
														<div class="form-check small-checkboxes">
															<input class="form-check-input" name="show_word_translation_settings" value="Yes" type="checkbox" value="" @if($show_word_translation_settings) checked @endif id="defaultCheckword">
															<label class="form-check-label" for="defaultCheckword">Show Word Translation</label>
														</div>
														@if(!empty($word_translation_info))
														@foreach($word_translation_info as $key => $val)
														<div class="form-check small-checkboxes">
															<input class="form-check-input" name="word_translation_settings[]" value="{{$key}}" type="checkbox" value="" id="defaultCheck{{$key}}" @if(in_array($key, $word_translation_settings)) checked @endif>
															<label class="form-check-label" for="defaultCheck{{$key}}">{{$val}}</label>
														</div>
														@endforeach
														@endif
														<div class='main'>Language</div>
														@if(!empty($languages))
														@foreach($languages as $key => $val)
														<div class="form-check small-checkboxes">
															<input 
																class="form-check-input word_languages_settings" 
																name="word_languages_settings[]" 
																value="{{$val['id']}}" 
																type="checkbox" 
																id="defaultCheck{{$val['id']}}" 
																@if(in_array($val['id'], $word_languages_settings)) checked @endif
															/>
															<label 
																class="form-check-label" 
																for="defaultCheck{{$val['id']}}"
															>
																{{$val['name']}}
															</label>
														</div>
														@endforeach
														@endif	
													</div>
													<div class="col ">
														<div class='main'>
														Scholars
                                                              </div>
                                                              
														
														@if(!empty($formated_word_scholars_settings))
														@foreach($formated_word_scholars_settings as $val)
														<div class="form-check small-checkboxes">
															<input 
																type="hidden" 
																name="word_scholar_checked_languages[]" 
																class="word-scholar-checked-languages" 
																value="{{!empty($val['checked'])?$val['id'].'-'.$val['language_id']:''}}"
															/>
															<input 
																class="form-check-input word-scholars-settings" 
																name="word_scholars_settings[]" 
																value="{{$val['id']}}" 
																type="checkbox" 
																@if(!empty($val['checked'])) checked @endif 
																id="defaultCheck{{$val['id']}}" 
																data-language="{{$val['language_id']}}" 
																@if(!empty($val['disabled'])) disabled @endif
															/>
															<label 
																class="form-check-label" 
																for="defaultCheck{{$val['id']}}"
															>
																{{$val['scholar_name']}}-
																<span class="text-info">
																	{{$val['language_name']}}
																</span>
															</label>
														</div>
														@endforeach
														@endif
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-6">
									<div class="accordion-item">
										<h2 class="accordion-header" id="headingOne">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ayatscholars" aria-expanded="true" aria-controls="ayatscholars">
												Scholar Translations
											</button>
										</h2>
										<div id="ayatscholars" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
											<div class="accordion-body pt-0">
												<div class="row">
													<div class="col">
														<div class='main'>Language</div>
														@if(!empty($languages))
														@foreach($languages as $key => $val)
														<div class="form-check small-checkboxes">
															<input 
																class="form-check-input ayat_languages_settings" 
																name="ayat_languages_settings[]" value="{{$val['id']}}" 
																type="checkbox"
																id="defaultCheck{{$val['id']}}" 
																@if(in_array($val['id'], $ayat_languages_settings)) checked @endif
															/>
															<label 
																class="form-check-label" 
																for="defaultCheck{{$val['id']}}"
															>
																{{$val['name']}}
															</label>
														</div>
														@endforeach
														@endif	
													</div>
													<div class="col">
														<div class='main'>Scholars</div>
														@if(!empty($formated_ayat_scholars_settings))
														@foreach($formated_ayat_scholars_settings as $val)
														<div class="form-check small-checkboxes">
															<input 
																type="hidden" 
																name="ayat_scholar_checked_languages[]" 
																class="ayat-scholar-checked-languages" 
																value="{{!empty($val['checked'])?$val['id'].'-'.$val['language_id']:''}}"
															/>
															<input 
																class="form-check-input ayat-scholars-settings" 
																name="ayat_scholars_settings[]" value="{{$val['id']}}" 
																type="checkbox" 
																id="defaultCheck{{$val['id']}}" 
																data-language="{{$val['language_id']}}" 
																@if(!empty($val['checked'])) checked @endif 
																@if(!empty($val['disabled'])) disabled @endif
															/>
															<label 
																class="form-check-label" 
																for="defaultCheck{{$val['id']}}"
															>
																{{$val['scholar_name']}}-
																<span class="text-info">
																	{{$val['language_name']}}
																</span>
															</label>
														</div>
														@endforeach
														@endif
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row mt-2">
								<div class="col-6">
									<div class="accordion-item">
										<h2 class="accordion-header" id="headingOne">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#scholars" aria-expanded="true" aria-controls="scholars">
												Word By Word Display Settings
											</button>
										</h2>
										<div id="scholars" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
											<div class="accordion-body pt-0">
												<div class="row">
													<div class="col">
														<div class="main">Word Columns</div>
														@if(!empty($columns))
														@foreach($columns as $key => $val)
														<div class="form-check small-checkboxes form-check-inline">
															<input class="form-check-input" name="words_settings[]" value="{{$key}}" type="checkbox" value="" id="defaultCheck{{$key}}" @if(in_array($key, $words_settings)) checked @endif>
															<label class="form-check-label" for="defaultCheck{{$key}}">{{$val}}</label>
														
														</div>
														@endforeach
														@endif	
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<button class="btn btn-primary mt-2" type="submit">Save</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection