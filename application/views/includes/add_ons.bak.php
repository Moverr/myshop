<?php
$table_HTML = "";
														
#*********************************************************************************
# Displays forms used in AJAX when processing data on other forms without 
# reloading the whole form.
#*********************************************************************************


#===============================================================================================
# Display for simple message results
#===============================================================================================
if(!empty($area) && in_array($area, array('save_recover_settings_results', 'add_delivery_data')))
{
	$table_HTML .= format_notice($msg);	
}

#===============================================================================================
# Show search results in combo-box
#===============================================================================================
else if(!empty($area) && $area == 'combo_list')
{	
	if(!empty($page_list))
	{		
		if(empty($select_text))
		{
			$select_text = 'Select';
		}
		
		$table_HTML .= get_select_options($page_list, $value_field, $text_field, '', 'Y', $select_text);	
			
	} else {
		$table_HTML .= "<option value=''>No items to show!</option>";	
	}
}


#===============================================================================================
# Search users
#===============================================================================================
else if(!empty($area) && $area == 'users_list')
{
	if(!empty($page_list))
	{		
		$table_HTML .= '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Name</th>'.
					  '<th class="hidden-480">PDE</th>'.
					  '<th class="hidden-480">User Group</th>'.
					  '<th class="hidden-480">Email Address</th>'.
					  '<th class="hidden-480">Phone No.</th>'.
					  '<th>Date Added</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';
				
		  foreach($page_list as $row)
		  {
			  #user's role(s)
			  $user_roles_arr_text = get_user_roles_text($this, $row['userid'], $usergroups);
			  $user_roles_text = (!empty($user_roles_arr_text)? implode(', ', $user_roles_arr_text) :  '<i>NONE</i>');
			  
			  $delete_str = '<a title="Delete user details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'admin/delete_user/i/'.encryptValue($row['userid']).'\', \'Are you sure you want to delete this user?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="icon-trash"></i></a>';
			  $edit_str = '<a title="Edit user details" href="'. base_url() .'user/load_user_form/i/'.encryptValue($row['userid']).'"><i class="icon-edit"></i></a>';
			  
			  $table_HTML .= '<tr>'.
					'<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
					'<td>'. (!empty($row['prefix'])? $row['prefix'] . ' ' : '') . $row['firstname'] . ' ' . $row['lastname'] .'</td>'.
					'<td>'. $row['pdename'] .'</td>'.
					'<td>'. $user_roles_text .'</td>'.
					'<td>'. $row['emailaddress'] .'</td>'.
					'<td>'. $row['telephone'] .'</td>'.
					'<td>'. custom_date_format('d M, Y', $row['dateadded']) .'</td>'.
					'</tr>';
		  }
		  
		  $table_HTML .=  '</tbody></table>';	
			
	} else {
		$table_HTML .= format_notice("ERROR: Your search criteria did not match any data");	
	}
}


#===============================================================================================
# Search user groups
#===============================================================================================
else if(!empty($area) && $area == 'user_groups_list')
{
	if(!empty($page_list))
	{		
		$table_HTML .= '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>User group</th>'.
					  '<th class="hidden-480">No. of Members</th>'.
					  '<th class="hidden-480">Author</th>'.
					  '<th class="hidden-480">Date added</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';
				
				foreach($page_list as $row)
				{
					$delete_str = '<a title="Delete user details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'admin/delete_user/i/'.encryptValue($row['usergroupid']).'\', \'Are you sure you want to delete this user group?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="icon-trash"></i></a>';
					$edit_str = '<a title="Edit user group details" href="'. base_url() .'admin/user_group_form/i/'.encryptValue($row['usergroupid']).'"><i class="icon-edit"></i></a>';
					
					$table_HTML .= '<tr>'.
						  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
						  '<td>'. $row['groupname'] .'</td>'.
						  '<td>'. $row['numOfUsers'] .'</td>'.
						  '<td>'. $row['authorname'] .'</td>'.
						  '<td>'. custom_date_format('d M, Y',$row['dateadded']) .'</td>'.
						  '</tr>';
				}
				
				$table_HTML .= '</tbody></table>';	
			
	} else {
		$table_HTML .= format_notice("ERROR: Your search criteria did not match any data");	
	}
}


#===============================================================================================
# Procurement plan report
#===============================================================================================
else if(!empty($area) && $area == 'procurement_plan_report')
{
	if(!empty($page_list)): 					
		$table_HTML .= "<table width='100%' border=0 cellpadding=5>
								  <tr>
									<td nowrap><strong>PDE procurement plans summary</strong></td>
									<td rowspan='3'>".
										#<img src=\"" . base_url() . "images/ug-arms.png\">
									"</td>
								  </tr>
								</table>";
	
		$table_HTML .= '<table width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Financial Year</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement entries</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Estimated value</th>'.					  
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Status</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Date approved</th>'.
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';
		
		foreach($page_list as $row)
		{	  
			$status_str = (!empty($row['approval_status'])? $row['approval_status'] : '<i>N/A</i>');
						
			$table_HTML .=  '<tr>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['plan_id'])? $row['financial_year'] : '<i>N/A</i>')  .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['numOfEntries'])? number_format($row['numOfEntries'], 0, '.', ',') : $row['numOfEntries']) .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['estimatedValue'])? number_format($row['estimatedValue'], 0, '.', ',') : $row['estimatedValue']) .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $status_str. '</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['plan_id'])? custom_date_format('d M, Y',$row['plan_dateadded']) : '<i>N/A</i>')  .'</td>'.
				  '</tr>';
		}
		
		$table_HTML .=  '</tbody></table>';
			
	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif; 

}


#===============================================================================================
# Late procurements report
#===============================================================================================
else if(!empty($area) && $area == 'late_procurements_report')
{
	if(!empty($page_list)): 					
		$table_HTML .= "<table width='100%' border=0 cellpadding=5>
								  <tr>
									<td nowrap><strong>Late procurements report</strong></td>
									<td rowspan='3'>".
										#<img src=\"" . base_url() . "images/ug-arms.png\">
									"</td>
								  </tr>
								</table>";
	
		$table_HTML .= '<table width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">#</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Financial Year</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement Ref. No</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Invitation to bid date</th>'.					  
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Actual IFB date</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Days delayed</th>'.
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';
			  
		$count = 0;
		
		foreach($page_list as $row)
		{	  
			$table_HTML .=  '<tr>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (++$count) .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['financial_year'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_ref_no'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y',$row['bid_issue_date']) .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'.custom_date_format('d M, Y',$row['invitation_to_bid_date']) . '</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['days_delayed'] .'</td>'.
				  '</tr>';
		}
		
		$table_HTML .=  '</tbody></table>';
			
	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif; 

}



#===============================================================================================
# Invitation for bids report
#===============================================================================================
else if(!empty($area) && $area == 'invitation_for_bids_reports')
{
	if(!empty($page_list)): 					
		$table_HTML .= "<table width='100%' border=0 cellpadding=5>
						  <tr>
							<td nowrap><strong>IFB report</strong></td>
							<td rowspan='3'>".
								#<img src=\"" . base_url() . "images/ug-arms.png\">
							"</td>
						  </tr>
						</table>";
	
		$table_HTML .= '<table width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Financial Year</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement ref. no.</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Subject of procurement</th>'.					  
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">IFB Date</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Bid submission dead line</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">No. of bids</th>'.
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';
		
		foreach($page_list as $row)
		{	
			$table_HTML .= '<tr>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['financial_year'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_ref_no'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['subject_of_procurement'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y',$row['invitation_to_bid_date']) . '</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y',$row['bid_submission_deadline']) . ' at ' . custom_date_format('h:i A',$row['bid_submission_deadline']). '</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['numOfBids'] .'</td>'.
				  '</tr>';
		}
		
		$table_HTML .=  '</tbody></table>';
			
	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif; 

}



#===============================================================================================
# Procurement record details
#===============================================================================================
else if(!empty($area) && $area == 'procurement_record_details')
{
	if(!empty($procurement_details))
	{		
		$table_HTML .= '<div class="control-group">'.
                       '<label class="control-label">Financial year:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['financial_year'])? $procurement_details['financial_year'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[financial_year]" value="'.$procurement_details['financial_year'] .'" />'.
                       '</div>'.
                       '</div>'.
                       '<div class="control-group">'.
                       '<label class="control-label">Type of procurement:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['procurement_type'])? $procurement_details['procurement_type'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[procurement_type]" value="'.$procurement_details['procurement_type'].'" />'.
					   '</div>'.
                       '</div>'.
                       '<div class="control-group">'.
                       '<label class="control-label">Method of procurement:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[procurement_method]" value="'. $procurement_details['procurement_method'].'" />'.
					   '</div>'.
                       '</div>'.
					   '<input type="hidden" name="proc_no" value="'. encryptValue($procurement_details['procurement_id']) .'" />'.
                       '<div class="control-group">'.
                       '<label class="control-label">Subject of procurement:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['subject_of_procurement'])? $procurement_details['subject_of_procurement'] : '<i>undefined</i>').
					   '</div>'.
                       '</div>'.
                       '<div class="control-group">'.
                       '<label class="control-label">Source of funding:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['funding_source'])? $procurement_details['funding_source'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[funding_source]" value="'.$procurement_details['funding_source'].'"/>'.
					   '</div>'.
                       '</div>';
			
	} else {
		$table_HTML .= format_notice("ERROR: Could not find the procurement record details.");	
	}
}







#===============================================================================================
# Manage PDEs record details
#===============================================================================================
else if(!empty($area) && $area == 'pde_list')
{
#	print_r($page_list);
	if(!empty($page_list))
	{		

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
							PDE Name
						</th>
						<th>
						Abbreviation
						</th>
						<th>
							Category
						</th>

						<th>
							Code
						</th>
						
						 
					</tr>
				</thead>
				<tbody>
				<?php
				$xx = 0;
				//print_r($active['page_list']); exit();
foreach($page_list as $row)
{
	$xx ++;
	?>
	<tr  id="active_<?=$row['pdeid']; ?>">

		<td>
						 <a href="<?=base_url().'pdes/load_edit_pde_form/'.base64_encode($row['pdeid']); ?>"> <i class="icon-edit"></i></a>
						 <a href="#" id="savedelpde_<?=$row['pdeid'];?>" class="savedelpde"> <i class="icon-trash"></i></a>

		</td>

						<td  class="actived">
							<?=$xx; ?>
						</td>
						<td  class="actived">
							<?=$row['pdename']; ?>
						</td>
						<td  class="actived">
							<?=$row['abbreviation']; ?>
						</td>
						<td  class="actived">
							<?=$row['category']; ?>
						</td>
						<td  class="actived">
							<?=$row['code']; ?>
						</td>
						
					</tr>
				 
	<?php
}
				?>
					 
					 
				</tbody>
			</table>
 
<?php
					 
	} else {
		$table_HTML .= format_notice("ERROR: Could not find the PDE record details.");	
	}
}






#===============================================================================================
# Manage Pdetypes record details
#===============================================================================================
else if(!empty($area) && $area == 'pdetype_list')
{
#	print_r($page_list);
	if(!empty($page_list))
	{		

?><table class="table  table-striped">
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
 
<?php
					 
	} else {
		$table_HTML .= format_notice("ERROR: Could not find the PDE Type record details.");	
	}
}






#===============================================================================================
# Manage Receipts record details
#===============================================================================================
else if(!empty($area) && $area == 'receipts_list')
{
#	print_r($page_list);
	if(!empty($page_list))
	{		

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
							Procurement Ref No
						</th>
						<th>
						Service Provider
						</th>
						<th>
							Date Submitted
						</th>

						<th>
							Received By
						</th>
						<th>
							Nationality
						</th>

						<th>
							Date Added
						</th>
						<th>
							Evaluated
						</th>
						
						 
					</tr>
				</thead>
				<tbody>
					<?php
					$numcount = 0;

					#print_r($receiptinfo); exit();
foreach ($page_list as $key => $value) {
	# code...
	$numcount  ++;
	?>
	 <tr>
						<td>
							<?=$numcount; ?>

						</td>
						<td width="10">
							 <?php
							  
							 
							  switch ($value['beb']) {
							  	case 'p':
							  		# code...
							  	?>
							  		<a href="<?=base_url().'receipts/load_edit_receipt_form/'.encryptValue($value['receiptid']); ?>"> <i class="icon-edit"></i></a>
					
							  	<?php
							   
							  		break;
							  		case 'Y':
							  		# code...
							  		?>
							  			<a href="<?=base_url().'receipts/load_edit_receipt_form/'.encryptValue($value['receiptid']); ?>"> <i class="icon-edit"></i></a>
					
							  		<?php
							  		 
							  		break;
							  			case 'N':
							  			 ?>
							  			 	<a href="<?=base_url().'receipts/load_edit_receipt_form/'.encryptValue($value['receiptid']); ?>"> <i class="icon-edit"></i></a>
					
							  			 <?php
							  		# code...
							  		break;
							  	default:
							  	?>
							 	<a href="<?=base_url().'receipts/load_edit_receipt_form/'.encryptValue($value['receiptid']); ?>"> <i class="icon-edit"></i></a>
						  <a href="#" id="savedelreceipt_<?=$value['receiptid'];?>" class="savedelreceipt"> <i class="icon-trash"></i></a>

							  	<?php
							  		# code...
							  		break;
							  }
 
							  ?>
						  
						</td>
						<td>
							 <?=$value['procurement_ref_no']; ?>
						</td>
						<td>
							 <?=$value['providernames']; ?>						 
						</td>
						<td>
							 <?=$value['datereceived']; ?>								 
						</td>  							
						<td>
							 <?=$value['received_by']; ?>	 
						</td>
						<td>
							   <?=$value['nationality']; ?>
						</td>
						<td>
							  <?=$value['dateadded']; ?>
							 
						</td>

						<td>
							  
							  <?php
							  
							 
							  switch ($value['beb']) {
							  	case 'p':
							  		# code...
							  	?>
							  	<span class="label label-info">Pending</span>
							  	<?php
							  		break;
							  		case 'Y':
							  		# code...
							  		?>
							  		<span class="label label-success">Approved</span>
							  		<?php
							  		break;
							  			case 'N':
							  			?>
							  			<span class="label label-warning">Unsuccessful </span>
							  			<?php
							  		# code...
							  		break;
							  	default:
							  		# code...

							  	?>
							  			<span class="label label-info">Pending </span>
							  			<?php
							  		break;
							  }
 
							  ?>
							 
						</td>

						
						 
					</tr>
					 

	<?php
}


					?>
				
				 
					 
					 
				</tbody>
			</table>
 
<?php
					 
	} else {
		$table_HTML .= format_notice("ERROR: Could not find the Receipts record details.");	
	}
}




if(!empty($table_HTML))
{	
	#echo htmlentities($table_HTML);
	echo $table_HTML;
}
?>



<script>
 
	 // MANAGE DELETE RESORE AND UPDATE FUC
$('.savedelpde').on('click', function(){
 
 
    var decider = this.id;
    var idq =  decider.split('_');
      
     switch(idq[0])
     {
        case 'savedelpde':
        url = baseurl()+'pdes/delpdes_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'restore':
        url = baseurl()+'pdes/delpdes_ajax/restore/'+idq[1];
        var b = confirm('You Are About to Restore a Record')
        if(b == true){           
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'del':
        url = baseurl()+'pdes/delpdes_ajax/delete/'+idq[1];
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


 
</script>