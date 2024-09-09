var siteUrl = $('#site-url').text();

toastr.options = {
    "closeButton": true,
    "debug": false,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "1000",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

// SELECT2
$('.select2').select2({ width: '100%' });

// USER MODULE
$(document).on('change','#role',function(){
	const value = $(this).val();	
	if(value==3 && $('.scholar-section').hasClass('d-none')){
		$('.scholar-section').removeClass('d-none');
	}else{
		$('.scholar-section').addClass('d-none');
	}
});

// Search Ayats By Surha Number
$(document).on('change', '.surah', async function(){
	const value = $(this).val();
	const data = {surah: value};
	const response = await makeAjaxCall(data,'search-ayat');
	if(response.success){
		let form_ayats = '';
		let to_ayats  = '';
		response.ayats.length > 0 && response.ayats.forEach((item,index)=>{
			
			form_ayats += `<option value="${item.ayatNo}">${item.ayatNo}</option>`
			to_ayats += `<option value="${item.ayatNo}" ${index===response.ayats.length-1?'selected':''}>${item.ayatNo}</option>`
		})
		$('.from_verse').html(form_ayats);
		$('.to_verse').html(to_ayats);
	}else{
		console.log('response data is ',response.message);	
	}
});

// Get Translations By Ayat
$(document).on('change', '#from-verse', async function() {
	$('.translation-div').removeClass('d-none');
	$('#translation').addClass('d-none');
	$('.loader').removeClass('d-none');
	$('#translation').html('')
	$('#savebtn').addClass('d-none');
	const user = $('.scholar').val() ?? '';
	const surahNo = $('.surah').val();
	const data = {surahNo,ayat:$(this).val(),user:user};
	const response = await makeAjaxCall(data,'get-translation');
	$('.loader').addClass('d-none');
	$('#translation').removeClass('d-none');
	if(response.success){
		$('#savebtn').removeClass('d-none');
		$('#translation').html(response.view)
	}else{
		toastr["error"](response.message);
	}
});

// STORIES MODULE$('.modal-dialog')
$(document).on('change','.story-surah',function(){
	const storyId = $(this).val();
	$('.modal-dialog').addClass('modal-lg');
	$('#data_modal').modal('show');
	loadModal('/dashboard/stories/get-ayats',storyId);
});

// Add RootWordMeaning MODULE
// $(document).on('click','.admin-can-add',function(){
// 	const rootId = $(this).text();

// 	console.log( rootId.trim() );
// 	$('.modal-dialog').addClass('modal-lg');
// 	$('#data_modal').modal('show');
// 	loadModal('/dashboard/get-root-word',rootId.trim());
// });

// Common Ajax Post Call
const makeAjaxCall = (data,url) =>{
	const endPoint = siteUrl+'/dashboard/'+url;
	return $.ajax({
		type: 'POST',
		url: endPoint,
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		data: data,
		success: function(response) {
			return response;
		},
		error: function(err) {
			return err;
		}
	})
}

function loadModal(url,params=null,params1=null,params2=null){
	$('.modal-content').html(`<div class="d-flex justify-content-center my-3"><div class="spinner-border" role="status"></div></div>`);
	console.log( url );
	params  = params ?? '';
	params1 = params1 ?? '';
	params2 = params2 ?? '';
	const endPoint = `${siteUrl}${url}?params=${params}&params1=${params1}&params2=${params2}`;	
	$.ajax({
		type:"GET",
		url:endPoint,
		success:function(response){
			$('.modal-content').html(response);
		}
	});
}

// Form Submission with File Uploading
$(document).on('submit','form.make_file_ajax',function(e){
	e.preventDefault();
	const btn = $(this).find('button[type="submit"]');
	const btnText = $(btn).text();
	addWait(btn)
	$.ajax({
		type: $(this).attr('method'),
		contentType: false,
		cache: false,
		processData: false,
		dataType: "json",
		url: $(this).attr('action'),
		data: new FormData(this),
		headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		success:function(response){
			removeWait(btn,btnText);
			if(response.success){
				toastr["success"](response.message, "Completed!");
			}else{
				afterAjaxAction(response);
			}

			if(response.action==="redirect"){
				window.location.href = response.url;	
			}else if(response.action=="reload"){
				window.location.reload();
			}
		}
	});
	return false;
});

// Form Submission without file
$(document).on('submit','form.make_ajax',function(e) {
	e.preventDefault();
	const btn = $(this).find('button[type="submit"]');
	const btnText = $(btn).text();
	addWait(btn)
	$.ajax({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
		data: $(this).serialize(),
		headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		success:function(response){
			removeWait(btn,btnText)
			if(response.success){
				toastr['success'][response.message];
				const {action} = response;
				if(action==="reload"){
					window.location.reload();
				} else if(action==="redirect"){
					window.location.href = response.url;
				} else if(action==="close"){
					$('#data_modal').modal('hide');
				}
			} else{
				if(response.errors){					
					afterAjaxAction(response);
				}else{
					toastr['error'](response.message);
				}
			}
		}
	});
	return false;
});

function afterAjaxAction(response){
	if(response.errors){
		$('.form-group').removeClass('has-validation');
		$('.is-invalid').removeClass('is-invalid');
		$('.invalid-feedback').remove();
		Object.entries(response.errors).forEach(([key,value])=>{
			$('#'+key).parent('.form-group').addClass('has-validation')
			$('#'+key).addClass('is-invalid').after(`<div class="invalid-feedback">${value.toString()}</div>`);
		});
	}
}

function addWait(dom){
	$(dom).attr('disabled',"disabled");
	const str = `<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> ...loading`;
	$(dom).html(str);
}

function removeWait(dom,label){
	$(dom).attr('disabled',false);
	$(dom).html(label);
}

// DELETE API CALL
$(document).on('click','.delete',function(){
	const remove = $(this).attr('data-remove');
	$.ajax({
		url: $(this).attr('data-url'),
		type: 'GET',
		headers: {
		    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		 },
		success:function(response){
			if(response.success){
				toastr['success'](response.message,'Completed!');
				if(response.action==="reload"){
					window.location.reload();
				}else{
					$('.'+ remove).remove()
				}
			}
		}
	});
	return false;
});

// REMOVE WORD PREFERENCE
$(document).on('click','.remove-preference-word',async function(){
    const wordId = $(this).attr('data-id');
    const url = $(this).attr('data-url');
    const authId = $(this).attr('data-auth');
    const data = {word:wordId,scholar:authId};
    const response = await makeAjaxCall(data,url);
    if(response.success){
        toastr['success'](response.message);
        const tr = $(`#word_${wordId}`).closest('tr');
        $(tr).removeClass('disabled');
        $(tr).find('input,select').attr('disabled',false);
        $(this).parent('.refered_word').remove();
    }else{
        toastr['error'](response.message);
    }
});

// REMOVE PHRASE WORD PREFERENCE
$(document).on('click','.remove-phrase-preference-word',async function(){
	// alert('testing');
    const wordId = $(this).attr('data-id');
    const refId = $(this).attr('data-ref-id');
    const url = $(this).attr('data-url');
    const authId = $(this).attr('data-auth');
    const data = {word:wordId,scholar:authId,refWord:refId};
    const response = await makeAjaxCall(data,url);
    if(response.success){
        toastr['success'](response.message);
        // const tr = $(`#word_${wordId}`).closest('tr');
        // $(tr).removeClass('disabled');
        // $(tr).find('input,select').attr('disabled',false);
        // $(this).parent('.refered_word').remove();

		const c = refId.split(',');
		c.forEach(e => {
			let tr = $( '#word_'+ e ).closest('tr');
			$(tr).removeClass('disabled');
			$(tr).find('input,select').attr('disabled',false);
			$(tr).find('.refered_word').remove()
		});
    }else{
        toastr['error'](response.message);
    }
});

// Trigger Event When Word Abrahamic Locuation Change
$(document).on('change','.word_references_types',function(){
    const td = $(this).closest('td');
    const value = $(this).val();
    const word = $(this).attr('data-id');
    
    if(value==""){
    	return '';
    }

    $(td).find('#words_number').addClass('d-none');
    $(td).find('#view-word-reference').hide();

    if(value==="by_reference"){
    	$(td).find('#view-word-reference').show();
        $('#data_modal').modal('show');
        loadModal('/dashboard/get-related-words',word);
    }else if(value==="both"){
    	$(td).find('#view-word-reference').show();
    	$(td).find('#words_number').removeClass('d-none');
    }else{
        $(td).find('#words_number').removeClass('d-none');
    }
});
// Trigger Event When Word Abrahamic Locuation Number Change
$(document).on('change','#words_number',function(){        	
	const wordIds = JSON.parse($('#ayat_word_ids').text());
    const td = $(this).closest('td');
    const value = $(this).val();
    const wordId = $(this).attr('data-id');
    const authId = $('#auth_id').val();
    const index = wordIds.indexOf(Number(wordId));
    const referenc_type = $(td).find('.word_references_types').val();

    const previousValue = $(td).find('.word_references_types').attr('data-previous-value');
    const wordNumberValue = $(td).find('#words_number').val();
    console.log('preview data is ', previousValue, wordNumberValue ,value)

    if(previousValue && previousValue ==="by_number" || previousValue==="No" && value !==wordNumberValue){
        releaseDisableWords(wordId,wordNumberValue,wordIds);
        // td.find('#words_number').val('1')
    }

    enableRows(wordId,wordIds);
    disableRows(wordId,value,wordIds);
    const newwords = [...wordIds];
    const slice = newwords.splice(index,Number(value));
    if(referenc_type==="by_number" && slice.length > 0){
		const data = {
			'word_id': slice[0],
			'reference_phrase_words': slice
		}
		makeAjaxCall( data, 'save-phrase-word-references' );
	}

    if(referenc_type==="both" && slice.length > 0){
        $('#data_modal').modal('show');
        loadModal('/dashboard/get-related-words',slice,'',authId);
    }
});

function releaseDisableWords(wordId,wordNumberValue,wordIds){
	
   const index = wordIds.indexOf(Number(wordId));
    if(index > -1){
        for(let i=index;i<wordIds.length;i++){
        	const tr = $(`#word_${wordIds[i]}`).closest('tr');
        	if(!$(tr).hasClass('reference-disabled')){
        		$(tr).removeClass('disabled');
            	$(tr).find('input,select').attr('disabled',false);
        	}
        }
    } 
}

function enableRows(wordId,wordIds){
    const index = wordIds.indexOf(Number(wordId));

    if(index > -1){
        for(let i=index;i<wordIds.length;i++){
        	const tr = $(`#word_${wordIds[i]}`).closest('tr');
        	if(!$(tr).hasClass('disabled')){
        		$(tr).find('input,select').attr('disabled',false);	
        	}
        }
    }
}

function disableRows(wordId,value,wordIds){
    const index = wordIds.indexOf(Number(wordId));
    const newwords = [...wordIds];
    if(index > -1 && value > 1){
        const slice = newwords.splice(index+1,Number(value)-1);
        slice.forEach(item=>{
            $(`#word_${item}`).find('#words_number').hide();
            $(`#word_${item}`).find('#view-word-reference').hide();
            $(`#word_${item}`).find('select').val(null).trigger("change");
            $(`#word_${item}`).find('input,select').val(null).attr('disabled',true)
        });
    }
}

// SETTINGS MODULE
$(document).on('change','.ayat-scholars-settings',function(){
	const scholarId = $(this).val();
	const languageId = $(this).attr('data-language');
	const scholarLanguage = `${scholarId}-${languageId}`;
	const form = $(this).closest('.form-check');
	const input = $(form).find('.ayat-scholar-checked-languages');
	if(this.checked){
		$(input).val(scholarLanguage);
	}else{
		$(input).val("");
	}
});

$(document).on('change','.word-scholars-settings',function(){
	const scholarId = $(this).val();
	const languageId = $(this).attr('data-language');
	const scholarLanguage = `${scholarId}-${languageId}`;
	const form = $(this).closest('.form-check');
	const input = $(form).find('.word-scholar-checked-languages');
	if(this.checked){
		$(input).val(scholarLanguage);
	}else{
		$(input).val("");
	}
});

$(document).on('change','.ayat_languages_settings',function(){
	enableScholars('ayat_scholars_settings')
	var values = [];
	$('input[name="ayat_languages_settings[]"]:checked').each((index,language)=>{
		values.push($(language).val());
	});
	console.log('data is ', values)       	
	disalbeScholars('ayat_scholars_settings',values);
});

$(document).on('change','.word_languages_settings',function(){
	enableScholars('word_scholars_settings')
	var values = [];
	$('input[name="word_languages_settings[]"]:checked').each((index,language)=>{
		values.push($(language).val());
	});        	
	disalbeScholars('word_scholars_settings',values);
});

const enableScholars = (name)=>{
	$(`input[name="${name}[]"]`).each((index,item)=>{
		$(item).attr('disabled',false)
	});
};
const disalbeScholars = (name,values)=>{
	$(`input[name="${name}[]"]`).each((index,item)=>{
		const language = $(item).attr('data-language');
		const isExist = values.includes(language);
		if(!isExist){
			$(item).attr('checked',false);
			$(item).attr('disabled',true)
			$(item).closest('.form-check').find('input[type="hidden"]').val('');
		}
	});
}


function showLoading() {
  document.querySelector('#loading').classList.add('loading');
  document.querySelector('#loading-content').classList.add('loading-content');
}

function hideLoading() {
  document.querySelector('#loading').classList.remove('loading');
  document.querySelector('#loading-content').classList.remove('loading-content');
}

document.onreadystatechange = function () {
  var state = document.readyState
  if (state == 'interactive') {
       showLoading();
  } else if (state == 'complete') {
      setTimeout(function(){
        hideLoading();
      },1000);
  }
}