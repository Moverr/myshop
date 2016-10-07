function getBaseURL(){
    var pageURL=document.location.href;
    var urlArray=pageURL.split("/");
    var BaseURL=urlArray[0]+"//"+urlArray[2]+"/";

    if(urlArray[2]=='localhost') BaseURL=BaseURL+'ppda/';
    
    return BaseURL;
}



$(document).ready(function(){
    $(".dropdown").hover(
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideDown("fast");
            $(this).toggleClass('open');
        },
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideUp("fast");
            $(this).toggleClass('open');
        }
    );
	
	$('#nav_src').val('Tender Portal Search..');
	
	$('#nav_src').blur(function(){
			if (this.value == '') {this.value = 'Tender Portal Search..';}
		});
		
	$('#nav_src').focus(function(){
			if (this.value == 'Tender Portal Search..') {this.value = '';}
		});
		
	$('.date-picker')
		.datepicker({dateFormat: "yyyy-mm-dd"})
		.on('changeDate', function(e){
    		$(this).datepicker('hide');
	});
	
$('.beb a').click(function(){
  formdata = {};
  var receiptid = $(this).closest('.beb').attr('data-value');
  var url = $(this).closest('.beb').attr('data-ref');
  formdata['receiptid'] = receiptid;
  console.log(formdata);
  $(".modal-body").html("proccessing ...");
  $.ajax({
          type: "POST",
          url:  url,
          data:formdata,
          success: function(data, textStatus, jqXHR){
          $(".modal-body").html(data);
          },
          error:function(data , textStatus, jqXHR)
          {
              alert(data);
          }
      });
})


	
	
	
});