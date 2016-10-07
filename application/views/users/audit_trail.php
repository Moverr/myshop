<script type="text/javascript">
     $(document).on('click','.printer',function(){
 
 
    $(".table").printArea();
    
  })


var event_date = '';
// event date 
function eventdate(eventdate){
	event_date = eventdate;
	console.log(event_date);
}



//var searchphrase = '';
//Search Phrase 
var search_phrase = '';
function searchphrase(searchphrase)
{

	search_phrase = searchphrase;
	console.log(search_phrase);
	 
}

//Search Audit Trail 
function search_audit_trail(){

	  $("#lightbox_heading").html("<strong>Proccessing .. </strong> ");

    var formdata = {};
    formdata['pde_id'] = pde_id;
     formdata['search_phrase'] = search_phrase;
     formdata['event_date'] = event_date;
     formdata['userid'] = userid;
      console.log(formdata);
	  var url = "<?=base_url().$search_url; ?>/level/advanced";

	  	 $.ajax({
	        type: "POST",
	        url:  url,
	        data:formdata,
	        success: function(data, textStatus, jqXHR){	  

	         
	            $('#results tbody').html(data);
	              console.log(data);

	               $("#lightbox_wrapper").fadeOut('slow');
	            

	        },
	        error:function(data , textStatus, jqXHR)
	        {
	            console.log("ERROR: GET_USER_PDE AUDIT TRAIL "+data);
	        }
	    });





}

var userid = 0;
//set User_user Id
function set_user_id(user_id)
{
	userid = user_id;
	console.log("USER ID"+userid );

}



var pde_id = 0;
function get_user(pde){
	
	if(pde <= 0 )
	{
			$('#select_user').empty().append('<option value="0">-- Select  --</option>');      
		return;
	}

	 pde_id = pde;

	var formdata = {};
    formdata['pde_id'] = pde;


	console.log(formdata);

	var url = baseurl()+"admin/get_user_by_pde";

	$('#select_user').empty().append('<option value="0">Proccessing .... </option>');            

	   $.ajax({
	        type: "POST",
	        url:  url,
	        data:formdata,
	        success: function(data, textStatus, jqXHR){
	          
	           console.log(data);
	            $('#select_user').empty().append(data);
	            

	        },
	        error:function(data , textStatus, jqXHR)
	        {
	            console.log("ERROR: GET_USER_PDE AUDIT TRAIL "+data);
	        }
	    });


}


 function fetch_pdes(){}

 function fetch_users(){}
 
 $(function(){

                       $('.table').dataTable({
                          "paging":   false,
                          "ordering": true,
                          "info":     false ,
                           "processing": true,
                          "serverSide": true,
                          "ajax": "<?=base_url().$search_url; ?>",
                          "dataSrc": "tableData"                          

                      });
                      
                      
                         
					var oTable = $('.table').dataTable();
					$("#results input").unbind();
					
					$('#filter').click(function(e){
						oTable.fnFilter($("div.dataTables_filter input").val());
					});




					    bid_invitation_calloff = 0;
    $(".toggleauditrail").click(function(){


        var url = baseurl()+"admin/load_advanced_search_form";
        
        $("#lightbox_wrapper").fadeIn('slow');
        $("#lightbox_heading").html(" ");
        $("#lightbox_body").html("Proccessing....");

         $.ajax({
        type: "POST",
        url:  url,        
        success: function(data, textStatus, jqXHR){
            console.log(data);
           $("#lightbox_body").html(data);

        },
        error:function(data , textStatus, jqXHR)
        {
            console.log(data);
        }
    });


       
 
       

    });

    // Search Audit Trail 
  

    

} );





</script><div class="widget" >
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;AUDIT TRAIL</h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>

<?php

	$userid =  $this->session->userdata('userid');		 
		$isadmin =  $this->session->userdata('isadmin');
 if($isadmin == 'Y')
	{
		?>
     <div class="span12  ">  
        <a href="#" class="btn toggleauditrail"> Advanced Search </a>                  
      </div>
  
  <?php
}
?>

    <div class="widget-body" id="results">
    	<?php 

     



			if(!empty($page_list['page_list'])):
				?>
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th class="hidden-480">ACTION</th>
				<th class="hidden-480">MESSAGE</th>
				<th class="hidden-480">CONTEXT</th>
				<th class="hidden-480">USER</th>
				<th class="hidden-480">PDE</th>
				<th>Date/Time</th>
				</tr>
			</thead>
			</tbody>
			<?php
			foreach ($page_list['page_list'] as $key => $row) {
				?>
				<tr>
					<td class="hidden-480">
						<?php
						switch(strtolower($row['action'])){
							case 'create':
								?>
									<i class="fa fa-plus"></i> New record added
								<?php
								break;
							case 'update':
								?>
								<i class="fa fa-minus"></i> Record updated
								<?php
								break;
							case 'delete':
								?>
								<i class="fa fa-trash"></i> Record deleted
								<?php
								break;
							case 'read':
								?>
								<i class="fa fa-eye"></i> Record accessed
								<?php
								break;
							case 'log out':
								?>
								<i class="fa fa-sign-out"></i> Log out
								<?php
								break;
							case 'log in':
								?>
								<i class="fa fa-sign-in"></i> log in
								<?php
								break;
							default:

								?>
								<i class="fa fa-alert"></i> <?=$row['action']?>
								<?php


						}
						?>
					</td>
					<td class="hidden-480"><?=$row['message']?></td>
					<td class="hidden-480"><?=$row['context']?></td>
					<td class="hidden-480"><?=$row['name']?></td>
					<td class="hidden-480"><?=$row['pde']?></td>
					<td><?=date('d M, Y H:i s',strtotime($row['dateadded'])); ?></td>
				</tr>

				<?php
			}
			?>
			</tbody>
		</table>
		<?php
				
				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().	
						"admin/audit_trail/p/%d")
					.'</div>';
		
			else:
        		print format_notice('WARNING: No users have been added to the system');
        	endif; 
        ?>
    </div>
</div>
