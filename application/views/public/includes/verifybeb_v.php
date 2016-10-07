<script>
$(function(){

$(".searchbeb").click(function(){
  $(".details").html('<label class="label label-info"> Proccessing</label>');
 formdata = {};
 formdata['serialno'] = $('.searchtext').val();
     url = getBaseURL()+'page/searchbeb/verify';
     $.ajax({        
        url:  url,  
         type: 'POST',
        data: formdata,       
        success: function(data, textStatus, jqXHR){

            if(data == 0)
            {
               var msg = '<br/><br/> <div class="row-fluid col-md-8">'+
                  '<div class="span12">'+
                    '<div class="alert alert-info">'+
                       '<button type="button" class="close" data-dismiss="alert">×</button>'+
                      '<h4>Warning!'+
                      '</h4>  No records found for Serial number <strong>'+ formdata['serialno']+' </strong>.'+
                    '</div>'+
                  '</div>'+
                '</div>';
               $(".details").html(msg);
            }
            else
            {
                $(".details").html(data);
            }
 
          

        },
        error:function(data , textStatus, jqXHR)
        {
        console.log('Cant COnnect to the SErver <br/>');
             console.log(data);
        return 0;
        }
    });
    
})
})
</script
<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
                 
                
                <div class=""  >
                  
                   
                        <div class="form-group">
                            <input type="text" class="form-control col-md-8 searchtext" style="border:#ddd 1px solid; width:90%;" placeholder="Enter BEB Serial Number" />
                        </div> <button type="button" class="btn btn-default searchbeb">SEARCH</button>
                        <div class="details">

                        </div>
                   
                   
              
                
             
        </div>
    </div>
</div>