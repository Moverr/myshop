	$(document).on('click','.placefield',function(){
		var fieldId = $(this).attr('id');
		//If the place div is available, then just show that, else, load a new div content
		if($('#'+fieldId+'__div').length > 0)
		{
			$('#'+fieldId+'__div').fadeIn('fast');
		} 
		else 
		{
			//Now specify where the callouts pick their forms
			if($(this).hasClass('placefield'))
			{
				var physicalOnly = $('#'+fieldId).hasClass('physical')? "/physical_only/Y": "";
				$('#'+fieldId).after("<div id='"+fieldId+"__div' class='callout'></div>");
				$('#'+fieldId+'__div').css('min-height',(physicalOnly == ''? 265: 230)+'px');
				$('#'+fieldId+'__div').css('min-width',$(this).outerWidth());
				updateFieldLayer(getBaseURL()+"page/address_field_form/field_id/"+fieldId+physicalOnly,'','',fieldId+'__div','');
			}
			
			//TODO: Add more call out options here
		}
		// Minus 10 for the pointer
		var offsetTop = $('#'+fieldId).offset().top - $('#'+fieldId+'__div').outerHeight();// - 10;
		var offsetLeft = $('#'+fieldId).offset().left;
		$('#'+fieldId+'__div').offset({ top: offsetTop, left: offsetLeft });
	});




	