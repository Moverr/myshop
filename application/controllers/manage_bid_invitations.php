<script type="text/javascript">
	 
	
	<?php 
	if(!empty($notifyrop))
	{
 	$bidinvitation = $notifyrop;
		#$bidinvitation = 99;
		
		//Deserves an Ajax Reaction on this :: 
		
		/*
		OPEN THE POP UP LIGHT BOX AND SEND DATA TO THE SERVER AND THEN WAIT JUST WAIT 
		*/
		?>

		$(function(){
		var a = document.createElement('a');
		
		formdata = {};
		
		/*
		Load Light Box :
		The Light Box is installed in the dashboard_v. which is used accross the entire. Dashboard  section 
		*/
		
		 $("#lightbox_wrapper").fadeIn('slow');
         $("#lightbox_heading").html(" ");
         $("#lightbox_body").html("Proccessing....");
		 urld ='<?=base_url().'page/notifyrop/'.$bidinvitation; ?>';
		
		$.ajax({

                              type: "POST",
                              url:  urld,
                              data: formdata,
                              success: function(data, textStatus, jqXHR){
                              $("#lightbox_body").html(data);
                              },
                              error:function(data , textStatus, jqXHR)
                              {
                                  console.log('Data Error'+data+textStatus+jqXHR);
                              }

           });
		
	/*	a.target = '_blank';
		document.body.appendChild(a);
		a.click();  */
		});
		
		
		
		<?php
	}
	?>

 
 // Data Tables Search Engine 
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
    var url  = '<?=base_url()?>bids/manage_bid_invitations/';
        url += 'level/<?=$level; ?>';

    $(".financial_year_selection").change(function(){
    	//alert($(this).val());
    	var financial_year = $(this).val().trim();    	

    	if(financial_year.length > 0)
    	{
    		url += '/financial_year/'+financial_year;
    		location.href =url;
    	}
    	


    })
});


				var formdata_bi = {};
				  //Function Cance Bid Invitations
				  /*
				  To Cancel Bid Invitation, we update the isactive (Y,N,C)  to C
				  */
				  function cancelbid(id,url){
					  
					  formdata_bi['bidid']= id;
					  formdata_bi['url']= url;
					  
					  
					  
					  
					    var result = confirm("You about to Cancel  this IFB \n Click Ok to Proceed ");
					    if(result == false)
						return;
						
						// Continue with Loading the FOrm to submit Credentials 
						
						 $("#lightbox_wrapper").fadeIn('slow');
						 $("#lightbox_heading").html(" ");
						 $("#lightbox_body").html("Proccessing....");

						 var strng = '<div style="width:80%;margin:auto; text-aligh:center;" >'+
								' <div class="control-group"><h2>CANCEL BID INVITATION </h2></div>'+

								'<div class="container-fluid">'+
								'<div class="row">'+
								'<div class="col-md-12">'+
								'<form role="form">'+
								'<div class="form-group">'+
							   
								'<label for="termination_reason">'+
								' ENTER REASON FOR TERMINATION '+
								'</label>'+
								 '<textarea type="text" class="form-control termination_reason" style="width:100%" id="termination_reason" >'+
								'</textarea>'+
								'</div>'+
						   
						   ' <div class="form-group">'+
							  '<label for="date_contract_terminated">'+
							   ' Date IFB Terminated'+
							  '</label>'+
							 ' <input type="date" class="form-control date_contract_terminated " style="width:100%" id="date_contract_terminated" />'+         
							'</div>'+
							
							'<button type="button" onClick="javascript:$(`#lightbox_wrapper`).fadeOut(`slow`);" class="btn btn-default" data-dismiss="modal">Close</button>'+
						   ' &nbsp;'+
							'<button onClick="javascript:cancel_beb();" type="button" class="btn  add_callof_order">Submit</button> '
									 
						  '</form>'+
						'</div>'+
					  '</div>'+
					  '</div>'+ '</div>';


					 $("#lightbox_body").html(strng);
 
						
					
					
				  }
				  
				  //
				  
				  //Cancel  BEB "" 
				function cancel_beb()
				{
				 
				  var termination_reason =      $("#termination_reason").val();
				  var date_contract_terminated = $("#date_contract_terminated").val();
				  var formdata = {};
				  //alert(date_contract_terminated);

					if((termination_reason.length > 0) && (date_contract_terminated.length > 0))
					{
					 formdata['termination_reason'] = termination_reason;
					 formdata['date_contract_terminated'] = date_contract_terminated;
					 
					  $("#lightbox_heading").html('PROCESSING ... ');
						// return false;
				      url =  formdata_bi['url'];
				
				
				      formdata['url']  =formdata_bi['url'] ;
					  formdata['bidid']  = formdata_bi['bidid'] ;					   
					  formdata['action']  = 'cancelifb' ;
					  
					  console.log(formdata);
				 
					  $.ajax({

							type: "POST",
							url:  url,
							data: formdata,
							success: function(data, textStatus, jqXHR){
								
								console.log(data);
							  if(data == 1)
							  {
									   $("#termination_reason").val('');
								       $("#date_contract_terminated").val('');
									   $("#lightbox_heading").html('<label class="warning" style="                border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">IFB  CANCELED SUCCESSFULLY</label>');

									   setTimeout(function(){  location.reload(0);}, 1000);
							  }
							  else
							  {
								  $("#lightbox_heading").html('<label class="warning" style="                border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">SOMETHING WENT WRONG, CONTACT SITE ADMINISTRATOR</label>'); return false;
							 
							  }
						  // $("#lightbox_body").html(data);
						//  alert("reached")
							},
							error:function(data , textStatus, jqXHR)
							{
								console.log('Data Error'+data+textStatus+jqXHR);
								//alert(data);
							}

						  });


					}
					else
					{
					   $("#lightbox_heading").html('<label class="warning alert alert-important emailalert " style=" background-color: #fcf8e3;    border: 1px solid #fbeed5; padding:5px;  width:110%;">Fill Blanks</label>'); return false;

					}

				}


</script>

<div class="widget ">

			<!-- Widget Title -->
			<div class="widget-title">
				<!--  Manage Bid Invitations   -->         
			</div>
   
			<div class="tabbable" style="padding-left:30px; " id="tabs-45158">
			
			    <!-- Navigation Tabs Active Archived and Cancelled and Financial Year -->
				<ul class="nav nav-tabs">
				
				   <!-- Active -->
					<li class=" <?php if(!empty($level) && ($level == 'active'))  {  echo "active"; } ?> " onClick="javascript:location.href='<?=base_url().'bids/manage_bid_invitations/level/active/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'">
						<a href="<?=base_url().'bids/manage_bid_invitations/level/active/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder-open"> </i> ACTIVE <span class="badge badge-info"><?=$activecount[0]['numbids']; ?> </span></a>
					</li>
					
					<!-- Archived -->
					<li onClick="javascript:location.href='<?=base_url().'bids/manage_bid_invitations/level/archive/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'"  class="<?php if(!empty($level) && ($level == 'archive'))  {  echo "active"; } ?>">
						<a href="<?=base_url().'bids/manage_bid_invitations/level/archive/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder"> </i>  ARCHIVED  <span class="badge badge-info"><?=$archivecount[0]['numbids']; ?></span></a>
					
					</li>
					
					<!-- Canceled IFBS -->
						<li onClick="javascript:location.href='<?=base_url().'bids/manage_bid_invitations/level/cacnceled/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'"  class="<?php if(!empty($level) && ($level == 'cacnceled'))  {  echo "active"; } ?>">
						<a href="<?=base_url().'bids/manage_bid_invitations/level/cacnceled/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder"> </i>  CANCELED  <span class="badge badge-info"><?=$canceledcount[0]['numbids']; ?></span></a>
					
					</li>
					
					<!-- Financial years -->
					<li>
					<select class="chosen financial_year_selection">
						   <?=get_select_options($financial_years, 'fy', 'label', (!empty($current_financial_year)? $current_financial_year : '' ))?> 

					</select>
					</li>
				</ul>
			 
			</div>
    
   	

    <div class="widget-body tab-content results " id="results">
    	<?php 
    	
			/*
			If Not Empty Page List: record_set
			*/
			if(!empty($page_list['page_list'])): 
				
				print '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Procurement Ref. No</th>'.
					  '<th class="hidden-480">Subject of procurement</th>'.
					   '<th class="hidden-480">Method of procurement</th>'.
					  '<th class="hidden-480">Bid security</th>'.
					  '<th class="hidden-480">Bid invitation date</th>'.
					  '<th class="hidden-480">Addenda</th>'.						  
					  '<th>Status</th>'.
					  '<th>Published by</th>'.
					  '<th>Date Added</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';

				 
				 /*				 
				 Iterating through the Pagelist  to fetch Records
				 */
				foreach($page_list['page_list'] as $row)
				{	
					 
                  $add_lots = check_user_access($this, 'add_lots');

					$status_str = '';
					$addenda_str = '[NONE]';
					$delete_str ='';
					$edit_str  = '';
					$cancel_str ='';
				    
					if(!empty($level) && ($level == 'active'))  { 
				    if($this->session->userdata('isadmin') == 'N')
						{ 	
					$delete_str = '<a title="Delete bid invitation" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'bids/delete_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'\', \'Are you sure you want to delete this bid invitation?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
					
					$edit_str = '<a title="Edit bid details" href="'. base_url() .'bids/load_bid_invitation_form/i/'.encryptValue($row['bidinvitation_id']).'"><i class="fa fa-edit"></i></a>';					
					
					$url_string = base_url().'bids/cancel_bid_invitation';
					$cancel_str =' <a href="javascript:void(0);"
						id="'.$row['id'].'"
						dataurl="'.base_url().'bids/cancel_bid_invitation" 
						databidid="'.$row['bid_id'].'"
						dataid="'.$row['bid_id'].'" 
						data-placement="bottom" 
						data-toggle="tooltip" 					 
						data-original-title="Cancel All BEB Lots" 
						title="Cancel All BEB Lots"
						widget-title="Cancel All BEB Lots "  
						class="cancel_bebx" 
						onClick="javascript:cancelbid('.$row['bidinvitation_id'].',`'.$url_string.'`)"; >
						<i class="fa fa-arrows-alt"></i> </a>';
						
						
				
									

					
					}}
					
					if(!empty($level) && ($level == 'cacnceled'))  { 
					
					
					if($this->session->userdata('isadmin') == 'N')
						{ 	
					//Cancel String will Equal to Revert String and Revert Functionality
					/*
					  Revert Functionality is mearnt to  Revert the Cancelled IFB issue back to normal ;
					*/
						$cancel_str =	$revert_str = '<a href="javascript:void(0);" 
										id="'.$row['id'].'" 
										dataurl="'. base_url().'bids/ajax_ifbaction_action"
										databidid="'.$row['bidinvitation_id'].'"
										dataid="'.$row['bidinvitation_id'].'" 
										data-toggle="tooltip" 
										data-original-title="Revert Bid Invitation" 
										title=""
										widget-title="Revert Bid Invitation " 
										class="revert_ifb"> <i class="fa fa-reply"></i> </a>';
						}
					}
				
					 
					
					/*
					Methods Meant to Be Approvaed indexes from the Procurement Methods Table
					*/
					$approved_methods = array("9", "1", "2","11");
				
						if($row['bid_approved'] == 'N')
						{
							 if($this->session->userdata('isadmin') == 'N')
						    { 
							$status_str = 'Not published | <a title="Publish IFB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Publish IFB]</a>';
						   }
					   }
					 
                  if($add_lots)
				    {
						$addenda_str =  '<a title="view addenda list" href="'. base_url() .'bids/view_addenda/b/'.encryptValue($row['bidinvitation_id']).'">[View Addenda]</a> ';
					    if($this->session->userdata('isadmin') == 'N')
						{ 	
						$addenda_str .= '| <a title="Add addenda" href="'. base_url() .'bids/load_ifb_addenda_form/b/'.encryptValue($row['bidinvitation_id']).'">[Add Addenda]</a>';
					    }
					}
					#&& get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')>0
					if($row['bid_approved'] == 'Y' )
					{
						if(get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')>0)
						{
							$status_strs = 'Bidding closes in '. get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days') .' days';
						}
						else
						{
							$status_strs = 'Bidding closed  '. get_date_diff( $row['bid_submission_deadline'], date('Y-m-d'),'days') .' days  ago';
						}
						$status_str =  $status_strs.'| <a title="view IFB document" href="'. base_url() .'bids/view_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[View IFB]</a>';
						
						$addenda_str =  '<a title="view addenda list" href="'. base_url() .'bids/view_addenda/b/'.encryptValue($row['bidinvitation_id']).'">[View Addenda]</a> ';
						if($this->session->userdata('isadmin') == 'N')
						{ 	
						$addenda_str .= '| <a title="Add addenda" href="'. base_url() .'bids/load_ifb_addenda_form/b/'.encryptValue($row['bidinvitation_id']).'">[Add Addenda]</a>';
					    }
					}
					else
					{
											
					}
					

					 if (in_array($row['procurement_method_id'], $approved_methods)) {
					 }else
					 {
						 $status_str = ' <a title="view IFB document" href="'. base_url() .'bids/view_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[View IFB]</a> ';					 	
					 }
					
					print '<tr>'.
						  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'.$cancel_str.'</td>'.
						  '<td>'. format_to_length($row['procurement_ref_no'], 40) .'</td>'.
						  '<td>'. format_to_length($row['subject_of_procurement'], 50);
						  

						 $lots_count = 0;
						 $str = '';
						  if($row['haslots'] == 'Y')
						  {
						  $lots_count = $this->db->query("SELECT COUNT(*) as num FROM lots where lots.bid_id='".$row['bidinvitation_id']."' AND lots.isactive='Y' ")->result_array();
						  #print_r($lots_count[0]['num']);
						 
						   if($add_lots)
						   {
						     $str .= '<br/><a href="'.base_url().'bids/add_lots/i/'.encryptValue($row['bidinvitation_id']).'">Add Lots </a>';												   	
						   }
						   
						  }
						   
						 
						  if($lots_count > 0)
						  {
						  	$str .= ' | <a href="'.base_url().'bids/manage_lots/i/'.encryptValue($row['bidinvitation_id']).'">View Lots <span class="badge badge">'.$lots_count[0]['num'].'</span></a>';
						  }
				   		print  $str;



						 print '</td>'.
						 '<td>'. format_to_length($row['procurement_method'], 50).'</td>'.

						  '<td>'. (is_numeric($row['bid_security_amount'])? number_format($row['bid_security_amount'], 0, '.', ',') . ' ' . $row['bid_security_currency_title'] : 
						  		  (empty($row['bid_security_amount'])? '<i>N/A</i>' : $row['bid_security_amount'])) .'</td>'.
						  '<td>'. display_date( $row['invitation_to_bid_date']) .'</td>'.
						  '<td>'. $addenda_str .'</td>'.
						  '<td>'. $status_str .'</td>'.
						  '<td>'. (empty($row['approver_fullname'])? 'N/A' : $row['approver_fullname']).'</td>'.
						  '<td>'. display_date( $row['bid_dateadded']) .' <br/> Author : '.(empty($row['bidauthor_fullname'])? 'N/A' : '<b>'.$row['bidauthor_fullname']).'</b>  </td>'.
				  '</tr>';
				}
				
				print '</tbody></table>';
				  
				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().	
						"bids/manage_bid_invitations/level/".$level.'/'.(!empty($current_financial_year) ?'financial_year/'.$current_financial_year.'/' :'' )."p/%d")
					.'</div>';
		
			else:
        		print format_notice('WARNING: No bid invitations have been added to the system');
        	endif; 
        ?>
    </div>

</div>
