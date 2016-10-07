	
<script type="text/javascript">
$(function(){
 


// MANAGE DELETE RESORE AND UPDATE FUC

// MANAGE DELETE RESORE AND UPDATE FUC

// MANAGE DELETE RESORE AND UPDATE FUC
$('.savedelpdetype').on('click', function(){


    var decider = this.id;
    var idq =  decider.split('_');

     switch(idq[0])
     {
        case 'savedelpdetype':
        url = baseurl()+'pdetypes/delpdetype_ajax/archive/'+idq[1];
        console.log(url);

        var b = confirm('You Are About to Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'restore':
        url = baseurl()+'pdetypes/delpdetype_ajax/restore/'+idq[1];
        console.log(url);
        var b = confirm('You Are About to Restore a Record')
        if(b == true){           
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'del':
        url = baseurl()+'pdetypes/delpdetype_ajax/delete/'+idq[1];
        console.log(url);
        var b = confirm('You Are About to Paramanently Delete a Record')
        if(b == true){           
         var rslt = ajx_delete(url,decider);
        }
        break;
        default:
         
        break;
     }
     
});

// MANAGE DELETE RESORE AND UPDATE FUC
// function savedelpdetype(id){
// alert(id);

//     var decider = id;
//     var idq =  decider.split('_');

//      switch(idq[0])
//      {
//         case 'savedelpdetype':
//         url = baseurl()+'pdetypes/delpdetype_ajax/archive/'+idq[1];
//         console.log(url);

//         var b = confirm('You Are About to Delete a Record')
//         if(b == true){
//          var rslt = ajx_delete(url,decider);
//         }
//         break;
//         case 'restore':
//         url = baseurl()+'pdetypes/delpdetype_ajax/restore/'+idq[1];
//         console.log(url);
//         var b = confirm('You Are About to Restore a Record')
//         if(b == true){           
//          var rslt = ajx_delete(url,decider);
//         }
//         break;
//         case 'del':
//         url = baseurl()+'pdetypes/delpdetype_ajax/delete/'+idq[1];
//         console.log(url);
//         var b = confirm('You Are About to Paramanently Delete a Record')
//         if(b == true){           
//          var rslt = ajx_delete(url,decider);
//         }
//         break;
//         default:
         
//         break;
//      }
     
// });
// }
});
</script>
<?php

switch ($status) {
	case 'active':
		# code...

	?>


	 <table class="table  table-striped">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							 <em class="glyphicon glyphicon-user"></em>
						</th>
						<th>
							PDE Type
						</th>
						<th>
						Date Created
						</th>
						<th>Author </th>
						 
						
						 
					</tr>
				</thead>
				<tbody>
				<?php
				$xx = 0;
foreach($page_list as $row)
{
	$xx ++;
	?>
	<tr  id="active_<?=$row['pdetypeid']; ?>">

		<td>
						 <a href="<?=base_url().'pdetypes/load_edit_pde_form/'.base64_encode($row['pdetypeid']); ?>"> <i class="icon-edit"></i></a>
						 <a href="#" id="savedelpdetype_<?=$row['pdetypeid'];?>" class="savedelpdetype"> <i class="icon-trash"></i></a>

		</td>

						<td  class="actived">
							<?=$xx; ?>
						</td>
						<td  class="actived">
							<?=$row['pdetype']; ?>
						</td>
						 
						<td  class="actived">
							<?=$row['datecreated']; ?>
						</td>
						<td></td>
						 
						
					</tr>
				 
	<?php
}
				?>
					 
					 
				</tbody>
			</table>
	<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
						"admin/manage_pdetypes/p/%d")
						.'</div>'; 

						?> 


	<?php
		break;
		case 'archive':
?>

			<table class="table  table-striped disabled">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							PDE Type  
						</th>
						<th>
						Date Created
						</th>
						 

						<th>
							Author
						</th>
						<th>
							 <em class="glyphicon glyphicon-user"></em>
						</th>
						 
					</tr>
				</thead>
				<tbody>
						
		<?php
		$x = 0;
	foreach($page_list as $row)
	{
		$x ++;
		?>
		<tr  id="archive_<?=$row['pdetypeid']; ?>">
							<td  class="actived">
							<?=$x; ?>
						</td>
						<td  class="actived">
							<?=$row['pdetype']; ?>
						</td>
						 
						<td  class="actived">
							<?=$row['datecreated']; ?>
						</td>
						<td  class="actived">
							 
						</td>
						 
							<td>
							<a href="#" id="restore_<?=$row['pdetypeid'];?>" class="savedelpdetype"> <i class="icon-share-alt"></i></a>
						 <a href="#" id="del_<?=$row['pdetypeid'];?>" class="savedelpdetype"> <i class="icon-remove-sign"></i></a>


							</td>
						</tr>
					 
				<?php
			}
			 ?>
				 
				
					 
					 
					 
				</tbody>
			</table>
			<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
						"admin/manage_pdetypes/p/%d")
						.'</div>'; 

						?> 
						
<?php
		break;
	
	default:
		# code...
		break;
}
?>
<script type="text/javascript">
//managing pdetypes

$(function(){


//working on tab select
$(".tabselction").on('click', function(event){
  
  var id = $(this).closest('li').attr('id');
  var dataurl =  baseurl()+$(this).closest('li').attr('dataurl');
  var datadiv =   $(this).closest('li').attr('datadiv');
   
 
  
  //get dataurl
  $("."+datadiv).html('Proccessing...');

  $.ajax({
                  type: "POST",
                  url:  dataurl,                   
                  success: function(data, textStatus, jqXHR){ 
                  console.log(data);                                       
                  $("."+datadiv).html(data); 
          
                  },
                  error:function(data , textStatus, jqXHR)
                  {
                      console.log('Data Error'+data+textStatus+jqXHR);  
                      alertmsg(data);
                  }
              });  


});

 
//handle submit events
$(".pdetypeform").on('submit', function(event){
   event.preventDefault();
	  mailesr(this);
   
});

//simple movervs
function mailesr(e){
cancelmsg();
 var form = e;
        var formid = form.id;
        var datatype = $("#"+formid).attr('data-type');
         //action url to commit your data
        var dataaction =   $("#"+formid).attr('data-action');
        // alert(dataaction);
        // return false;
        var dataelements =   $("#"+formid).attr('data-elements');
        console.log(dataelements);
       // alert(dataelements);
          //server side checks ::-- 
         var serversidecheckss =  $("#"+formid).attr('data-cheks');
         //the url where you are going to use for server side checks on data
         var datacheckaction =  $("#"+formid).attr('data-check-action');

             url = dataaction;

            if(dataelements.length > 0)
            {
                var fieldNameArr=dataelements.split("<>");
            }
            else
            {
                fieldNameArr = Array();
            }
             var elementfield  ='';
             var formdata = {};
             var commitcheckdata = {};

            if((dataelements!= ' ') &&(dataelements.length > 0))
            {
                //ITERATE THROUGH DATA ELEMENTS 
                 for(var i=0;i<fieldNameArr.length;i++)
                 {
                    //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED 
                     var lke = fieldNameArr[i].split("*");
                      elementfield = lke[1];
                      formvalue = $("#"+elementfield).val();
                         if(fieldNameArr[i].charAt(0)=="*"){
                          if(formvalue.length <= 0)
                          {
                          alertmsg('Fill Blanks'); return false;
                          }   
                      }
                
                        else
                        {
                            elementfield = fieldNameArr[i];
                            formvalue = $("#"+elementfield).val();             
                        }
               
                   var datatype =  $("#"+elementfield).attr('datatype');
                 

                    switch(datatype)
                    {
                        case 'text':
                        break;
                        case 'tel':
                       
                        if(formvalue.length > 0)
                        {
                        var valu = validatephone(formvalue);
                        if(valu == false)
                        {
                            alertmsg('Invalid Phone, Digits are allowed, Not More than 10 digits'); return false;
                        }
                       
                         
                        }                       
                        break;
                        case 'web':

                        if(formvalue.length > 0)
                        {
                        var valu = validateweb(formvalue);                        
                        if(valu == false)
                        {
                             alertmsg('Invalid Web Url '); return false;
                        }
                        
                        }
                        break;
                        case 'email': 

                        if(formvalue.length > 0)
                        {
                            var valu = validateemail(formvalue);
                            if(valu == false)
                            {
                                 
                                alertmsg('Ivalid Email Address'); return false;
                            }
                             
                            
                        }                       
                        break;
                        case 'phone':                         
                        if(formvalue.length > 0)
                        {
                        var valu = validatephone(formvalue);
                        if(valu == false)
                        {
                            alertmsg('Invalid Phone, Digits are allowed, Not More than 10 digits'); return false;
                        }
                                             
                        }   
                        break;
                        default:
                        break;
                    }

                    // ADDING ELEMETNTS TO THE FORM
                    formdata[elementfield] =formvalue; 
                     }
                  console.log(formdata);
                     
           $.ajax({
                  type: "POST",
                  url:  url,
                  data: formdata,
                  success: function(data, textStatus, jqXHR){ 
                  console.log(data); 
                    
                      if(data == 1){
                      alertmsg("Record Saved Succesfully");
                      location.href = baseurl()+'admin/manage_pdetypes';
                      }
                      else{
                         alertmsg(data);
                      }
          
                  },
                  error:function(data , textStatus, jqXHR)
                  {
                      console.log('Data Error'+data+textStatus+jqXHR);  
                      alertmsg(data);
                  }
              });


    }

           
		}
//end 

//submit form to the server
var submitf2orm = function(formdata,url){
  
    $.ajax({
        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){  
//alert(data);
            return data;
            console.log(data+textStatus+jqXHR);  
        },
        error:function(data , textStatus, jqXHR)
        {
            console.log('Data Error'+data+textStatus+jqXHR);  
            console.log(data);
        }
    });
}
 var alertmsg = function(msg){
    $(".alert").fadeOut('slow');
    $(".alert").fadeIn('slow');$(".alert").html(msg);
    scrollers();
   
    }
    var cancelmsg = function(){
        $(".alert").fadeOut('slow');
    }

    
     //scroll to top :: 
     var scrollers = function(){
    $('html, body').animate({scrollTop : 0},800);
     }





	</script>