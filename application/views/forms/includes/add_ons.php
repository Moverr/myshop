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
# Procurement record details Contracts
#===============================================================================================
else if(!empty($area) && $area == 'procurement_record_details_contracts')
{
	//print_r($procurement_details);
	if(!empty($procurement_details))
	{
	#	print_r($procurement_details);
		$table_HTML .= '<div class="control-group subject_of_procurement">'.
                       '<label class="control-label">Subject of procurement:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['subject_of_procurement'])? $procurement_details['subject_of_procurement'] : '<i>undefined</i>').
					   '<input type="hidden" name="procurement_details[subject_of_procurement]" value="'.$procurement_details['subject_of_procurement'] .'" />'.
					   '</div>'.
                       '</div>'.

					   '<div class="control-group">'.
                       '<label class="control-label">Financial year:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['financial_year'])? $procurement_details['financial_year'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[financial_year]" value="'.$procurement_details['financial_year'] .'" />'.
                       '</div>'.
                       '</div>'.

					   '<div class="control-group">'.
                       '<label class="control-label">Source of funding:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['funding_source'])? $procurement_details['funding_source'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[funding_source]" value="'.$procurement_details['funding_source'].'"/>'.
					   '</div>'.
                       '</div>';

									#		print_r($procurement_details['bidinvitationid']);
					 	if(!empty($procurement_details['bidinvitationid'])){
						$table_HTML .='<input type="hidden" name="bidinvitationid" value="'.$procurement_details['bidinvitationid'].'" />';
					}


  


		   $table_HTML .='<div class="control-group">'.
                       '<label class="control-label">Method of procurement:</label>'.
                       '<div class="controls">'.
					    '<input name="beb_contract_price" id="beb_contract_price"  class="beb_contract_price" type="hidden" value="'.(!empty($procurement_details['contractprice']) ?$procurement_details['contractprice'] : '').'" />'.
                       '<input name="beb_contract_currency" id="beb_contract_currency"  class="beb_contract_currency" type="hidden" value="'.(!empty($procurement_details['bebcurrency']) ?$procurement_details['bebcurrency'] : '').'" />'.
                     
                       (!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[procurement_method]" value="'. $procurement_details['procurement_method'].'" />'.
					   '</div>'.
                       '</div>';
							#Check out to see if  it has lots
							//bidinvitationid
							#receiptid
								$num_records = 0;
							if(!empty($procurement_details['bidinvitationid']))
							{
							$records = $this->db->query("SELECT COUNT(*) as cnt FROM bestevaluatedbidder INNER JOIN receipts ON bestevaluatedbidder.pid =   receipts.receiptid  WHERE  bestevaluatedbidder.lotid > 0 AND  receipts.bid_id = '".$procurement_details['bidinvitationid']."'")->result_array();

								$num_records = $records[0]['cnt'];
							}
						if($num_records > 0)
						{}
							else {

					   if(!empty($procurement_details['providers'])):
					   #$st = 'SELECT * FROM providers WHERE providerid in('.$procurement_details['providers'].')';
					   # print_r($st);

					  $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN ('.$procurement_details['providers'].') ' ) -> result_array();
					  #print_r($procurementdetails);
					  $providers = '<ul>';
					  $xc = '';
					 # $suspended = '';
					  $status = 0;
					  foreach ($procurementdetails as $key => $value) {
					 	# code...
					 	//check provider
					 	 $xc = '';
					 	 // searchprovidervalidity($value['providernames']);


							if(!empty($xc))
							{
								$status =1;
								 $providers .= "<li><div class='label label-warning' title='Suspended Provider' >".$value['providernames']."</div>".'&nbsp; &nbsp; <div class="alert alert-important " style="width:150px; margin-left:5px;">   <button data-dismiss="alert" class="close">×</button> This is a suspended provider    </div> </li>';
								# $suspended .= $value['providernames'].',';
							}
							else
							{
							 $providers .= "<li>".$value['providernames']."</li>";
							}

					 }

					  $providers .= '<ul>';
					 # print_r($procurement_details);
					  $str = '';
					  $vailiditystatus = '0';
					if($procurement_details['bidvalidity'] == 'y')
					{
					 	$enddatebidvalidity =  strtotime($procurement_details['bidvalidityperiod']);
					 	#echo "<BR/>:::::::<BR/>";
					 	#print_r($enddatebidvalidity);
					 	$vailiditystatus = '0';
					 	if(strtotime($enddatebidvalidity) < strtotime(date('Y-m-d')))
					 	{
					 		$vailiditystatus = '1';
					 			 		$str ='<div class="alert alert-info " style="width:250px; margin-left:5px;">   <button data-dismiss="alert" class="close">×</button> Validity Period Expired  on '.date('d M, Y',strtotime($procurement_details['bidvalidityperiod'])).'  </div>' ;
					 	}
					 #	echo "<BR/>:::::::<BR/>";
					 }


					#notify in case of suspended provider
					   $table_HTML .= '<input type="hidden" value="'.$vailiditystatus.'" id="bidvaliditystatus" > <input type="hidden" value="'.$status.'" id="providerstatus" >';

					   $table_HTML .= '<div class="control-group">'.
                       		'<label class="control-label">Selected provider:</label>'.
                       		'<div class="controls">'.
                       		 rtrim($providers,',').
                       		'<input type="hidden" name="provider" value="'.$procurement_details['providers'].'"/>'.
							'<input type="hidden" name="provider_info" value="'.(empty($procurement_details['id'])? 0 :$procurement_details['id']).'"/>'.
					   		'</div>'.
                       		'</div>';
                       $table_HTML .= $str;
					   endif;
					 }

					  #if contracts load the contract amount
					 if($level ==  'award_contracts')
					 {
					 	?>
					 	<script type="text/javascript">
						console.log('Addons Triggering  trigger_amount_contracts Method ');
					 	trigger_amount_contracts(<?=(!empty($procurement_details['contractprice']) ?$procurement_details['contractprice'] : '');?>,'<?=(!empty($procurement_details['bebcurrency']) ?$procurement_details['bebcurrency'] : '');?>');
					 	</script>
					 	<?php
					 }
					 
	} else {
		$table_HTML .= format_notice("ERROR: Could not find the procurement record details.");
	}
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
	

		  foreach($page_list as $row)
		  {
			  #user's role(s)
			  $user_roles_arr_text = get_user_roles_text($this, $row['userid'], $usergroups);
			  $user_roles_text = (!empty($user_roles_arr_text)? implode(', ', $user_roles_arr_text) :  '<i>NONE</i>');

			  $delete_str = '<a title="Delete user details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'admin/delete_user/i/'.encryptValue($row['userid']).'\', \'Are you sure you want to delete this user?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
			  $edit_str = '<a title="Edit user details" href="'. base_url() .'user/load_user_form/i/'.encryptValue($row['userid']).'"><i class="fa fa-edit"></i></a>';

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

		

	} else {
		$table_HTML .= '<tr><td colspan="100%">'.format_notice("ERROR: Your search criteria did not match any data").'</td></tr>';
	}
}




#===============================================================================================
# Search bid invitations
#===============================================================================================
else if(!empty($area) && $area == 'bid_invitations')
{
	#print_r($page_list);
	#exit();
	if(!empty($page_list)):

		/*$table_HTML .=  '<table class="table table-striped table-hover">'.
			  '<thead>'.
			  '<tr>'.
			  '<th width="5%"></th>'.
			  '<th>Procurement Ref. No</th>'.
			  '<th class="hidden-480">Procurement subject</th>'.
			  '<th class="hidden-480">Bid security</th>'.
			  '<th class="hidden-480">Bid invitation date</th>'.
			  '<th class="hidden-480">Addenda</th>'.
			  '<th>Status</th>'.
			  '<th>Published by</th>'.
			  '<th>Date Added</th>'.
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';  */
			  //$table_HTML .= '<tbody>'; 

		foreach($page_list as $row)
		{
			$delete_str = '<a title="Delete bid invitation" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'bids/delete_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'\', \'Are you sure you want to delete this bid invitation?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';

			$edit_str = '<a title="Edit bid details" href="'. base_url() .'bids/load_bid_invitation_form/i/'.encryptValue($row['bidinvitation_id']).'"><i class="fa fa-edit"></i></a>';

			$status_str = '';

			$delete_str = '<a title="Delete bid invitation" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'bids/delete_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'\', \'Are you sure you want to delete this bid invitation?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';

			$edit_str = '<a title="Edit bid details" href="'. base_url() .'bids/load_bid_invitation_form/i/'.encryptValue($row['bidinvitation_id']).'"><i class="fa fa-edit"></i></a>';

			$status_str = '';
			$addenda_str = '[NONE]';

			if($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')<0)
			{
				$status_str = 'Bid evaluation | <a title="Select BEB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Select BEB]</a>';
			}
			elseif($row['bid_approved'] == 'N')
			{
				$status_str = 'Not published | <a title="Publish IFB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Publish IFB]</a>';
			}
			elseif($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')>0)
			{
				$status_str = 'Bidding closes in '. get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days') .' days | <a title="view IFB document" href="'. base_url() .'bids/view_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[View IFB]</a>';

				$addenda_str =  '<a title="view addenda list" href="'. base_url() .'bids/view_addenda/b/'.encryptValue($row['bidinvitation_id']).'">[View Addenda]</a> | <a title="Add addenda" href="'. base_url() .'bids/load_ifb_addenda_form/b/'.encryptValue($row['bidinvitation_id']).'">[Add Addenda]</a>';
			}
			else
			{

			}

			$table_HTML .=  '<tr>'.
				  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
				  '<td>'. $row['procurement_ref_no'] .'</td>'.
				  '<td>'. format_to_length($row['subject_of_procurement'], 80) .'</td>'.
				  '<td>'. format_to_length($row['procurement_method'], 80) .'</td>'.
				  '<td>'. (is_numeric($row['bid_security_amount'])? number_format($row['bid_security_amount'], 0, '.', ',') . ' ' . $row['bid_security_currency_title']  : $row['bid_security_amount']) .'</td>'.
				  '<td>'. custom_date_format('d M, Y', $row['invitation_to_bid_date']) .'</td>'.
				  '<td>'. $addenda_str .'</td>'.
				  '<td>'. $status_str. '</td>'.
				  '<td>'. (empty($row['approver_fullname'])? 'N/A' : $row['approver_fullname']).'</td>'.
				  '<td>'. custom_date_format('d M, Y',$row['dateadded']) .'</td>'.
				  '</tr>';
		}
        // $table_HTML .= '</tbody>'; 
		//$table_HTML .=  '</tbody></table>';

	else:
		$table_HTML .= '<tr><td colspan="100%">'.format_notice('WARNING: No bid invitations have been added to the system').'</td></tr>';
	endif;
}


#===============================================================================================
# Search signed contracts
#===============================================================================================
else if(!empty($area) && $area == 'signed_contracts')
{
	if(!empty($page_list)):

		
$stack = array( );
		foreach($page_list as $row)
		{

			 $haslots = $row['haslots'];
             $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON received_lots.receiptid = receipts.receiptid INNER JOIN contracts on contracts.lotid = lots.id   WHERE lots.bid_id = ".$row['bidinvitation_id']." AND receipts.beb='Y' AND contracts.isactive='Y' ")->result_array();
             $lotcounting = $lot_count[0]['nums'];


                $bidd = $row['bidinvitation_id'];
                if($haslots  =='Y' ){
                if (in_array(  $bidd, $stack))
                continue;
                array_push( $stack, $bidd);
                }



          $edit_str = '';
          $delete_str = '';
          $termintate_str = '';

          if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
          {
           $delete_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'contracts/delete_contract/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
            

          }
          else
          {
            if(  $lotcounting <= 0 && $haslots  =='N')
            {

               $termintate_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="terminateContract(\''.base_url().'contracts/contract_termination/i/'.encryptValue($row['id']).'\', \'Are you sure you want to Terminate  this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
           
              // $termintate_str = '<a href="'. base_url() .'contracts/contract_termination/i/'.encryptValue($row['id']) .'" title="Click to terminate contract"><i class="fa fa-times-circle"></i></a>';
                $edit_str = '<a title="Edit contract details" href="'. base_url() .'contracts/contract_award_form/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
         
            }
          }         


            $status_str = '';
            $completion_str = '';


          if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
          {
          if(  $lotcounting <= 0 && $haslots  =='N')
            {
            $status_str = '<span class="label label-success label-mini">Completed</span>';
            $completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="fa fa-eye"></i></a>';
            }
          }
          else
          {
            if(  $lotcounting <= 0 && $haslots  =='N')
            {
            $status_str = '<span class="label label-warning label-mini">Awarded</span>';
            $completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="fa fa-check"></i></a>';
            }
         }
         $variations = '';

         if(  $lotcounting <= 0 && $haslots  =='N')
            {
        $variations = ' <a class="view_variations" id="view_'.$row['id'].'" data-ref="'. base_url() .'contracts/contract_variation_view/i/'.encryptValue($row['id']) .'" title="Click to view Variations "><i class="fa fa-bars"></i></a> &nbsp; &nbsp; ';

        if(empty($row['actual_completion_date']) )
         {
        $variations .= '<a href="'. base_url() .'contracts/contract_variation_add/i/'.encryptValue($row['id']) .'" title="Click to Add Variations "><i class="fa fa-plus-circle "></i></a> &nbsp; &nbsp;';

         }
       }



			 
		 $more_actions = '<div class="btn-group" style="font-size:10px">
                                     <a href="#" class="btn btn-primary">more</a><a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><span class="fa fa-caret-down"></span></a>
                                     <ul class="dropdown-menu">
                                         <li><a href="#"><i class="fa fa-times-circle"></i></a></li>
                                         <li class="divider"></li>
                                         <li>'. $completion_str .'</li>
                                     </ul>
                                  </div>';

			$table_HTML .= '<tr>'.
				  '<td>';

				  if(!empty($lotcounting) && ($lotcounting> 0))
                          {
            $table_HTML .=   $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str;

                          }else{
                
                          if($this->session->userdata('isadmin') == 'N')
            $table_HTML .=    $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str.'&nbsp;&nbsp;'.$variations;
                          }

			$table_HTML .= 	  '</td>';

			  if($this->session->userdata('isadmin') == 'Y')
        		{
            $table_HTML .=    '<th> '.$row['pdename'].' </th>';
        		}

			$table_HTML .= 	  '<td>'. custom_date_format('d M, Y',$row['date_signed']) .'</td>'.
							  '<td>'. format_to_length($row['procurement_ref_no'], 30);
			 if( $haslots  =='Y')
              {
            $table_HTML .= '<br/> <a href="#" class="view_lots"  id="view_'.$row['bidinvitation_id'].'" data-ref="'. base_url() .'receipts/get_contracts_lots/ "  >View Lots Awarded</a>';
              }
			$table_HTML .=	  '</td>'.
							  '<td>'. format_to_length($row['subject_of_procurement'], 30);
			  if($row['framework'] == 'Y' )
               {
            $table_HTML .=   '<br/><a href="#" id="'.$row['id'].'" class="togglecalloforders"  > Add Call off Order </a> | <a href="#" data-procurement="'.$row['procurement_ref_no'].'" id="'.$row['id'].'" class="viewlistcalloff" >View Call off Orders </a>  </br/>';
                }

			$table_HTML .=    '</td>'.
							  '<td>'. $status_str .'</td>'.
							   '<td>'. $row['contract_manager'] .'</td>'.
							  '<td style="text-align:right; font-family:Georgia; font-size:14px">'. addCommas($row['total_price'], 0) .'</td>'.
							  '<td>'. custom_date_format('d M, Y', $row['dateadded']) .' by '. format_to_length($row['authorname'], 10) .'</td>'.
							  '</tr>';
		}

		

	else:
		$table_HTML .= '<tr><td colspan="100%" >'. format_notice('WARNING: Your search criteria does not match any contracts').'</td></tr>';
	endif;


}



#===============================================================================================
#  Branches
#===============================================================================================

else if(!empty($area) && $area == 'branches')
{
	if(!empty($page_list)):
$xx = 0;
		foreach($page_list as $row)
		{
			$xx ++;

	$table_HTML .=  '<tr >'.
					'<td>'.
				    '<a href="'.base_url().'branches/add/i/'.base64_encode($row['id']).'"> <i class="fa fa-edit"></i></a>'.
					'<a href="#" id="savedelpde_'.$row['pdeid'].'" class="savedelpde"> <i class="fa fa-trash"></i></a>'.
					'</td>'.
					'<td  class="actived">'.
						$xx.
					'</td>'.
					'<td  class="actived">'.
					 $row['pdename'].
					 '</td>'.
					 '<td  class="actived">'.
					  $row['branchname'].
					 '</td>'.
					 '<td  class="actived">'.
					 $row['shortcode'].
					'</td>'.
					'<td  class="actived">'.
					$row['address'].
					'</td>'.
					'<td  class="actived">'.
					 $row['author_name'].
					 '</td>'.
					 '<td  class="actived">'.
					 date(" M d, Y", strtotime($row['dateadded'])).
					 '</td>'.						
					'</tr>';

		}

	else:
		$table_HTML .=  '<tr ><td colspan="100%">'. format_notice('WARNING: There is no help data regarding section').'</td></tr>';
	endif;
}

#===============================================================================================
#Manage All Help Information
#===============================================================================================


else if(!empty($area) && $area == 'faqs_search')
{

	 if(!empty($page_list)):	
 
                foreach($page_list as $row)
                {
                    $delete_str = '<a title="Delete help details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'faqs/delete_help/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
          
          			$edit_str = '<a title="Edit help details" href="'. base_url() .'faqs/edit_help_section/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
          
          			$table_HTML .= '<tr>'.
					      '<td>';
					$table_HTML .=  $delete_str .'&nbsp;&nbsp;'. $edit_str;
					$table_HTML .= '</td>';
					
					$table_HTML .= '<td>'.$row['faq_topic'].'</td>'.
						  '<td>'.$row['faq_header'].'</td>'.
						  '<td>'.custom_date_format('d M, Y', $row['datecreated']).'</td>';
					$table_HTML .= '</tr>';
                }
				
	else:
		$table_HTML .= '<tr><td colspan="100%">'.format_notice('WARNING: There is no help data regarding section').'</td></tr>';
	endif;   
				
}

#===============================================================================================
# Show help section  Menu Side
#===============================================================================================
else if(!empty($area) && $area == 'helpsectionmenu')
{
	
	//print_r($help_menu);
	 if(!empty($help_menu)):		 
	 
	 
			foreach($help_menu as $row => $topic)
			{
			//rint_r($topic['id']);
			$table_HTML .=  '<li data-details="'.$topic['id'].'" class="dynamic_menu"><a href="javascript:void(0)"><i class=" icon-signin"></i>'.$topic['faq_topic'].'</a></li>';
		
		//	//$table_HTML .=  '<li data-details="'.$topic['id'].'" class="dynamic_menu"><a href="javascript:void(0)"><i class=" icon-signin"></i>'.$topic['faq_topic'].'</a></li>';
			}

	else:
		print format_notice('WARNING: There is no help data regarding section');
	endif;    
}




#===============================================================================================
# Show help section
#===============================================================================================
else if(!empty($area) && $area == 'help_section')
{
	if(!empty($page_list)):

		foreach($page_list as $row)
		{
			$table_HTML .= '<div class="accordion-group">'.
			  '<div class="accordion-heading">'.
			  '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">'.
			  	$row['faq_header'].
			  '</a>'.
			  '</div>'.
			  '<div id="collapseOne" class="accordion-body collapse in">'.
			  '<div class="accordion-inner">'.
			  	html_entity_decode($row['faq_description']).'<br />'.'
			  	<img src="'.base_url().'uploads/backgrounds/'.$row['faq_image'].'" width="600" height="400">'.
			  '</div>'.
			  '</div>'.
			  '</div>';

		}

	else:
		print format_notice('WARNING: There is no help data regarding section');
	endif;
}




#===============================================================================================
# Search procurement entries
#===============================================================================================
else if(!empty($area) && $area == 'procurement_entries')
{
	if(!empty($page_list)):
	 

		$delete_rights = check_user_access($this, 'delete_procurement_entry');
		$edit_rights = check_user_access($this, 'edit_procurement_entry');
		$delete_str = '';
		$edit_str = '';

		foreach($page_list as $row)
		{
			if($delete_rights)
				$delete_str = '<a title="Delete entry" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'procurement/delete_entry/i/'.encryptValue($row['entryid']).'\', \'Are you sure you want to delete this entry?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="icon-trash"></i></a>';

			 
  			if($delete_rights)
				$delete_str = '<a title="Delete entry" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'procurement/delete_entry/i/'.encryptValue($row['entryid']).'\', \'Are you sure you want to delete this entry?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';

		    if($edit_rights)
				$edit_str = '<a title="Edit entry details" href="'. base_url() .'procurement/load_procurement_entry_form/i/'.encryptValue($row['entryid']).'"><i class="fa fa-edit"></i></a>';

				    $table_HTML .='<tr>'.
								  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
								  '<td>'. format_to_length($row['subject_of_procurement'], 50) .'</td>'.
								  '<td>'. $row['funding_source'] .'</td>'.
								  '<td>'. (is_numeric($row['estimated_amount'])? number_format($row['estimated_amount'], 0, '.', ',') . ' ' . $row['currency_abbr'] : $row['estimated_amount']) .'</td>'.
                                  '<td>'. $row['procurement_method'] .'</td>'.
                  '<td>'. (is_numeric($row['estimated_amount'])? number_format($row['quantity'], 0, '.', ',')  : $row['quantity']) .'</td>'.
								  '<td>'. (empty($row['authorname'])? 'N/A' : $row['authorname']).'</td>'.
								  '<td>'. custom_date_format('d M, Y',$row['dateadded']) .'</td>'.
								  '</tr>';
		}

	 

	else:
	$table_HTML .= "<tr><td colspan='100%'>". format_notice('ERROR: Your search criteria does not match any records')."</td></tr>";
	endif;
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
		$table_HTML .= '<table width="100%" border=0 cellpadding=5>
						  <tr>
							<td colspan="2" style="text-align: center; text-decoration: underline; font-size:14px;" nowrap>
								<strong>'. (!empty($report_heading)? $report_heading : '') .'</strong>
							</td>
						  </tr>'.
						  (!empty($sub_heading)?
						  '<tr>'.
						  '<td colspan="2" style="text-align:center; font-size:12px;"><i>'. $sub_heading .'</i></div>'.
						  '</tr>'
						  : '' ).
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Financial year:</td>'.
						  '<td style="text-align:left">'. (!empty($financial_year)? $financial_year : '') .'</td>'.
						  '</tr>'.
						  (!empty($report_period)?
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Reporting period:</td>'.
						  '<td style="text-align:left">'. (!empty($report_period)? $report_period : '') .'</td>'.
						  '</tr>' : '').
						'</table>';

		$table_HTML .= '<table style="margin-top:10px; border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Date approved</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.

			  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'volume')))?
			  '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement entries</th>'
			  : '').

			  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'value')))?
			  '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Estimated value</th>'
			  : '').
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';

		$total_procurements_value = 0;
		$total_activities = 0;

		foreach($page_list as $row)
		{
			$table_HTML .=  '<tr>'.
					'<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['plan_id'])? custom_date_format('d M, Y',$row['plan_dateadded']) : '<i>N/A</i>')  .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.

				  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'volume')))?
			 '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['numOfEntries'])? number_format($row['numOfEntries'], 0, '.', ',') : $row['numOfEntries']) .'</td>'
			  : '').

				  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'value')))?
			  '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['estimatedValue'])? number_format($row['estimatedValue'], 0, '.', ',') : $row['estimatedValue']) .'</td>'
			  : '').
				  '</tr>';

				  $total_procurements_value += (is_numeric($row['estimatedValue'])? $row['estimatedValue']  : 0);
				  $total_activities += (is_numeric($row['numOfEntries'])? $row['numOfEntries'] : 0);
		}

		$table_HTML .=  '<tr>'.
				  '<td>&nbsp;</td>'.
				  '<td">&nbsp;</td>'.
				  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'volume')))?
			 		'<td style="text-align:right; font-weight:bold; font-size: 16px; font-family: Georgia">'. addCommas($total_activities, 0) .'</td>'
			  : '').
				  ((!empty($formdata['aggregate_by']) && in_array($formdata['aggregate_by'], array('both', 'value')))?
			  	'<td style="text-align:right; font-weight:bold; font-size: 16px; font-family: Georgia">'. addCommas($total_procurements_value, 0) .'</td>'
			  : '').

				  '</tr>';

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
		$table_HTML .= '<table width="100%" border=0 cellpadding=5>
						  <tr>
							<td colspan="2" style="text-align: center; text-decoration: underline; font-size:14px;" nowrap>
								<strong>REPORT ON LATE PROCUREMENTS</strong>
							</td>
						  </tr>'.
						  (!empty($sub_heading)?
						  '<tr>'.
						  '<td colspan="2" style="text-align:center; font-size:12px;"><i>'. $sub_heading .'</i></div>'.
						  '</tr>'
						  : '' ).
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Financial year:</td>'.
						  '<td style="text-align:left">'. (!empty($financial_year)? $financial_year : '') .'</td>'.
						  '</tr>'.
						  (!empty($report_period)?
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Reporting period:</td>'.
						  '<td style="text-align:left">'. (!empty($report_period)? $report_period : '') .'</td>'.
						  '</tr>' : '').
						'</table>';

		$table_HTML .= '<table style="margin-top:10px; border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">#</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement Ref. No</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Planned IFB date</th>'.
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
		$table_HTML .= '<table width="100%" border=0 cellpadding=5>
						  <tr>
							<td colspan="2" style="text-align: center; text-decoration: underline; font-size:14px;" nowrap>
								<strong>'. (!empty($report_heading)? $report_heading : '') .'</strong>
							</td>
						  </tr>'.
						  (!empty($sub_heading)?
						  '<tr>'.
						  '<td colspan="2" style="text-align:center; font-size:12px;"><i>'. $sub_heading .'</i></div>'.
						  '</tr>'
						  : '' ).
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Financial year:</td>'.
						  '<td style="text-align:left">'. (!empty($financial_year)? $financial_year : '') .'</td>'.
						  '</tr>'.
						  (!empty($report_period)?
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Reporting period:</td>'.
						  '<td style="text-align:left">'. (!empty($report_period)? $report_period : '') .'</td>'.
						  '</tr>' : '').
						'</table>';

		$table_HTML .= '<table style="margin-top:10px; border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="5">'.
			  '<thead>'.
			  '<tr>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE Name</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement ref. no.</th>'.
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Subject of procurement</th>'.
			  ($formdata['ifb_report_type'] == 'BER'? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement method <br />(Threshhold)</th>' : '').
			  ($formdata['ifb_report_type'] == 'PIFB'? '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Estimated cost</th>' : '').
			  ($formdata['ifb_report_type'] == 'PIFB'? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">IFB Date</th>' : '').
			  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Bid submission dead line</th>'.
			  ($formdata['ifb_report_type'] == 'BER'? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Bid submission duration</th>' : '').
			  ($formdata['ifb_report_type'] == 'PIFB'? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">No. of bids received</th>' : '').
			  '</tr>'.
			  '</thead>'.
			  '</tbody>';

		foreach($page_list as $row)
		{
			$table_HTML .= '<tr>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_ref_no'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['subject_of_procurement'] .'</td>'.
				  ($formdata['ifb_report_type'] == 'BER'? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_method_title'] . ' (' . $row['biddingperiod'] .')</td>' : '').
				  ($formdata['ifb_report_type'] == 'PIFB'? '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['estimated_amount'])? number_format(($row['estimated_amount'] * $row['exchange_rate']), 0, '.', ',') : $row['estimated_amount']) . '</td>' : '').
				  ($formdata['ifb_report_type'] == 'PIFB'? '<td style="text-align: left; white-space: nowrap; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y',$row['invitation_to_bid_date']) . '</td>' : '').
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y',$row['bid_submission_deadline']) . ' at ' . custom_date_format('h:i A',$row['bid_submission_deadline']). '</td>'.
				  ($formdata['ifb_report_type'] == 'BER'? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['bid_submission_duration'] .'</td>' : '').
				  ($formdata['ifb_report_type'] == 'PIFB'? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['numOfBids'] .'</td>' : '').
				  '</tr>';
		}

		$table_HTML .=  '</tbody></table>';

	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif;

}


#===============================================================================================
# Best evaluated bidder reports
#===============================================================================================
else if(!empty($area) && $area == 'best_evaluated_bidder_reports')
{
	if(!empty($page_list)):
		$table_HTML .= '<table width="100%" border=0 cellpadding=5>
						  <tr>
							<td colspan="2" style="text-align: center; text-decoration: underline; font-size:14px;" nowrap>
								<strong>'. (!empty($report_heading)? $report_heading : '') .'</strong>
							</td>
						  </tr>'.
						  (!empty($sub_heading)?
						  '<tr>'.
						  '<td colspan="2" style="text-align:center; font-size:12px;"><i>'. $sub_heading .'</i></div>'.
						  '</tr>'
						  : '' ).
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Financial year:</td>'.
						  '<td style="text-align:left">'. (!empty($financial_year)? $financial_year : '') .'</td>'.
						  '</tr>'.
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Reporting period:</td>'.
						  '<td style="text-align:left">'. (!empty($report_period)? $report_period : '') .'</td>'.
						  '</tr>'.
						'</table>';

		$table_HTML .= '<table width="100%" cellspacing="0" cellpadding="5" style="margin-top:13px; border-collapse: collapse;">'.
					  '<thead>'.
					  '<tr>'.
					  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;"">Date published</th>'.
					  ($formdata['beb_report_type'] == 'EBN'? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;"">BEB Expiry date</th>' : '').
					  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE name</th>'.
					  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement ref. no.</th>'.
					  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Subject of procurement</th>'.
					  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Provider</th>'.
					  ($formdata['beb_report_type'] == 'PBEB'? '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Estimated cost (UGX)</th>' : '').
					  '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Proposed contract amount (UGX)</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';

		$grand_estimated_cost = 0;
		$grand_contract_amount = 0;

		foreach($page_list as $row)
		{
			#if multiple providers..
			$providername = $row['providernames'];
			if(!empty($row['joint_venture'])):
				$providername = '';
				$jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $row['joint_venture'] .'"')->result_array();

				if(!empty($jv_info[0]['providers'])):
					$providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
					foreach($providers as $provider):
						$providername .= (!empty($providername)? ', ' : ''). $provider['providernames'];
					endforeach;

				endif;

			endif;

			$grand_estimated_cost += (is_numeric($row['estimated_amount'])? $row['estimated_amount'] : 0);
			$grand_contract_amount += (is_numeric($row['contractprice'])? $row['contractprice'] : 0);

			$table_HTML .= '<tr>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif; white-space:nowrap">'. custom_date_format('d M, Y', $row['beb_dateadded']) .'</td>'.
				  ($formdata['beb_report_type'] == 'EBN'? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif; white-space:nowrap">'. custom_date_format('d M, Y', $row['beb_expiry_date']) .'</td>' : '').
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['pdename'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_ref_no'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['subject_of_procurement'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. ucwords(strtolower($providername)) .'</td>'.
				  ($formdata['beb_report_type'] == 'PBEB'? '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['estimated_amount'])? number_format($row['estimated_amount'], 0, '.', ',') : '') .'</td>' : '').
				  '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. (is_numeric($row['contractprice'])? number_format($row['contractprice'], 0, '.', ',') : '')  . '</td>'.
				  '</tr>';
		}


		$table_HTML .= '<tr>'.
			  '<td>&nbsp;</td>'.
			  ($formdata['beb_report_type'] == 'EBN'? '<td>&nbsp;</td>' : '').
			  '<td>&nbsp;</td>'.
			  '<td>&nbsp;</td>'.
			  '<td>&nbsp;</td>'.
			  '<td>&nbsp;</td>'.
			  ($formdata['beb_report_type'] == 'PBEB'?
				'<td style="text-align:right; font-weight:bold; font-size: 16px; font-family: Georgia">'. addCommas($grand_estimated_cost, 0) .'</td>' : '') .
			  '<td style="text-align:right; font-weight:bold; font-size: 16px; font-family: Georgia">'. addCommas($grand_contract_amount, 0)  . '</td>'.
			  '</tr>';


		$table_HTML .= '</tbody></table>';

	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif;

}

#===============================================================================================
#procurement_plans
#===============================================================================================

else if(!empty($area) && $area == 'procurement_plans')
{
	#print_r($page_list);
	#exit();
	if(!empty($page_list)):

		   $status = "";
            $status2 = "";
            
            if($this->session->userdata('isadmin') == 'N')
            {
              $status ='hidden'; 
            }

              if($this->session->userdata('isadmin') == 'Y')
            {
              $status2 ='hidden'; 
            }
            
            $delete_rights = check_user_access($this, 'delete_procurement_plan');
            $edit_rights = check_user_access($this, 'edit_procurement_plan');
            $create_entry_rights = check_user_access($this, 'add_procurement_entry');
                        $delete_str = '';
            $edit_str = '';
            $create_entry_str = ''; 


		foreach($page_list as $row)
		{
		  if($delete_rights)        
                              $delete_str = '<a title="Delete plan" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'procurement/delete_plan/i/'.encryptValue($row['plan_id']).'\', \'Are you sure you want to delete this plan?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
                            
              if($edit_rights)  
                              $edit_str = '<a title="Edit plan details" href="'. base_url() .'procurement/procurement_plan_form/i/'.encryptValue($row['plan_id']).'"><i class="fa fa-edit"></i></a>';  
                
              if($create_entry_rights)
                $create_entry_str = '&nbsp;|&nbsp;'.
                    '<a href="'.base_url().'procurement/load_procurement_entry_form/v/'.encryptValue($row['plan_id']).'">'.
                                      'Create entry'. 
                                      '</a>';       
                            
                            $status_str = '';
                            $addenda_str = '[NONE]';
                            
            $table_HTML .= '<tr>'.
                                  '<td class="'.$status2.'">'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
                                  '<td class="'.$status.'" >'. $row['pdename'] .'</td>'.
                                  '<td>'. format_to_length($row['financial_year'], 50) .'</td>'.
                                  '<td>'.
                                  '<a href="'. base_url().'procurement/procurement_plan_entries/v/'.encryptValue($row['plan_id']). '">'.
                                  '<span class="badge badge-info">'.
                    $row['numOfEntries'].
                                  '</span>'.
                    			  '&nbsp;Entries'.
                                  '</a>'.
                                      $create_entry_str.
                  				  '</td>'.
                                  '<td>'. $row['firstname'].' ' . $row['lastname'] .'</td>'.
                                  '<td>'. custom_date_format('d M, Y',$row['dateadded']) .'</td>'.
                                  '</tr>';			 
		}
        // $table_HTML .= '</tbody>'; 
		//$table_HTML .=  '</tbody></table>';

	else:
		$table_HTML .= '<tr><td colspan="100%">'.format_notice('WARNING: Search did not yield any results ').'</td></tr>';
	endif;
}


#===============================================================================================
# Contracts signed reports
#===============================================================================================
else if(!empty($area) && $area == 'signed_contracts_reports')
{
	if(!empty($page_list)):
		$table_HTML .= '<table width="100%" border=0 cellpadding=5>
						  <tr>
							<td colspan="2" style="text-align: center; text-decoration: underline; font-size:14px;" nowrap>
								<strong>'. $report_heading .'</strong>
							</td>
						  </tr>'.
						  (!empty($sub_heading)?
						  '<tr>'.
						  '<td colspan="2" style="text-align:center; font-size:12px;"><i>'. $sub_heading .'</i></div>'.
						  '</tr>'
						  : '' ).
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Financial year:</td>'.
						  '<td style="text-align:left">'. $financial_year .'</td>'.
						  '</tr>'.
						  '<tr>'.
						  '<td style="text-align:right; font-weight:bold; width:130px">Reporting period:</td>'.
						  '<td style="text-align:left">'. $report_period .'</td>'.
						  '</tr>'.
						'</table>';


		$table_HTML .= '<table style="margin-top:10px; border-collapse: collapse;" cellpadding=5 cellspacing=0>'.
						  '<thead>'.
						  '<tr>'.
						  (($formdata['contracts_report_type'] == 'AC')? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Date signed</th>' : '').
						  (in_array($formdata['contracts_report_type'], array('CDC', 'LC'))? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Planned date of completion</th>' : '').

						  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">PDE name</th>'.
						  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Procurement ref. no.</th>'.
						  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Subject of procurement</th>'.
						  #'<th class="hidden-480">Status</th>'.
						  '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Service provider</th>'.
						  (in_array($formdata['contracts_report_type'], array('LC'))? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Days delayed</th>' : '').
						  (in_array($formdata['contracts_report_type'], array('CC'))? '<th style="font-size: 12px; vertical-align:middle; text-align: left; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Date of completion</th>' : '').
						  (in_array($formdata['contracts_report_type'], array('CC'))? '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Total amount paid (UGX)</th>' : '').
						  '<th style="font-size: 12px; vertical-align:middle; text-align: right; border-bottom: 2px solid #000; padding-bottom:5px;font-family: Calibri, arial, sans-serif;">Contract value (UGX)</th>'.
						  '</tr>'.
						  '</thead>'.
						  '</tbody>';

		$grand_contracts_value = 0;
		$grand_total_amount_paid = 0;

		foreach($page_list as $row)
		{
			if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
			{
				$status_str = 'COMPLETE';
			}
			else
			{
				$status_str = 'IN PROGRESS';
			}

			#if multiple providers..
			$providername = $row['providernames'];
			if(!empty($row['joint_venture'])):
				$providername = '';
				$jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $row['joint_venture'] .'"')->result_array();

				if(!empty($jv_info[0]['providers'])):
					$providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
					foreach($providers as $provider):
						$providername .= (!empty($providername)? ', ' : ''). $provider['providernames'];
					endforeach;

				endif;

			endif;

			$table_HTML .= '<tr>'.

				  (($formdata['contracts_report_type'] == 'AC')? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y', $row['date_signed']) .'</td>' : '').
				  (in_array($formdata['contracts_report_type'], array('CDC', 'LC'))? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y', $row['completion_date']) .'</td>' : '').

				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. format_to_length($row['pdename'], 30) .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['procurement_ref_no'] .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $row['subject_of_procurement'] .'</td>'.
				  #'<td>'. $status_str .'</td>'.
				  '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. $providername .'</td>'.
				  (in_array($formdata['contracts_report_type'], array('LC'))? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. get_date_diff((empty($row['actual_completion_date'])? date('Y-m-d') : $row['actual_completion_date']), $row['completion_date'], 'days') .'</td>' : '').

				  (in_array($formdata['contracts_report_type'], array('CC'))? '<td style="text-align: left; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. custom_date_format('d M, Y', $row['actual_completion_date']) .'</td>' : '').
				  (in_array($formdata['contracts_report_type'], array('CC'))? '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. addCommas($row['total_amount_paid'], 0) .'</td>' : '').

				  '<td style="text-align: right; border-bottom: solid #000 1px; font-size:12px; font-family: Calibri, arial, sans-serif;">'. addCommas($row['total_price'], 0) .'</td>'.
				  '</tr>';

			$grand_contracts_value += $row['total_price'];
			$grand_total_amount_paid += $row['total_amount_paid'];
		}

		$table_HTML .= '<tr>'.
			  (($formdata['contracts_report_type'] == 'AC')? '<td>&nbsp;</td>' : '').
			  (in_array($formdata['contracts_report_type'], array('CDC', 'LC'))? '<td>&nbsp;</td>' : '').

			  '<td>&nbsp;</td>'.
			  '<td>&nbsp;</td>'.
			  '<td>&nbsp;</td>'.
			  #'<td>'. $status_str .'</td>'.
			  '<td>&nbsp;</td>'.
			  (in_array($formdata['contracts_report_type'], array('LC'))? '<td>&nbsp;</td>' : '').
			  (in_array($formdata['contracts_report_type'], array('CC'))? '<td>&nbsp;</td>' : '').
			  (in_array($formdata['contracts_report_type'], array('CC'))? '<td style="text-align:right; font-weight:bold; font-size: 14px; font-family: Georgia">'. addCommas($grand_total_amount_paid, 0) .'</td>' : '').
			  '<td style="text-align:right; font-weight:bold; font-size: 16px; font-family: Georgia">'. addCommas($grand_contracts_value, 0) .'</td>'.
			  '</tr>';

		$table_HTML .=  '</tbody></table>';

	elseif(!empty($formdata)):
		$table_HTML .= format_notice('Your search criteria does not match any results');
	endif;

}


#===============================================================================================
#MANAE BEBS
#===============================================================================================
else if(!empty($area) && $area == 'manage_bes')
{
	if(!empty($manage_bes))
	{
		   $stack = array( );
		   foreach($manage_bes['page_list'] as $row)
           {
           #	print_r($row);

           	$bidd = $row['bid_id'];
           	$lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid  INNER JOIN bidinvitations ON  bidinvitations.id = lots.bid_id  WHERE lots.bid_id = ".$row['bid_id']."   AND    bidinvitations.haslots ='Y' ")->result_array();
           	if (in_array(  $bidd, $stack))
                        continue;
            $table_HTML .= '<tr><td width="5%">';
            $table_HTML .= '<div class="btn-group" style="font-size:10px">';
                    if(!empty($level) && ($level == 'active') ){
              if($this->session->userdata('isadmin') == 'N')
                  {
                    switch($row['ispublished'])
                    {

                     case 'Y':
                       #print '    <a href="javascript:void(0);"  id="'.$row['id'].'"   dataurl="'.base_url().'receipts/ajax_beb_action'.'"  class="dropdown-toggle element unpublish_beb" data-placement="bottom"  data-toggle="tooltip"  data-original-title="Publish"    dataid="'.$row['id'].'"  title="Unpublish BEB" widget-title="Unpublish BEB"  style="color:yellow"  > <i class="fa fa-eye"> </i> </a> ';

                       break;

                       case 'N':
                         if(!empty($lot_count) && ($lot_count[0]['nums'] > 0)) {
                     $table_HTML .= '    <a href="javascript:void(0);"  id="'.$row['id'].'"   dataurl="'.base_url().'receipts/ajax_beblots_action'.'"  class="dropdown-toggle element publish_beb" data-placement="bottom"  data-toggle="tooltip"  data-original-title="Publish"    dataid="'.$row['bid_id'].'"  title="Publish BEB" widget-title="Publish All Lots "  style="color:green"  > <i class="fa fa-eye"> </i> </a> ';
                            }
                          else
                            {
                     $table_HTML .= '    <a href="javascript:void(0);"  id="'.$row['id'].'"   dataurl="'.base_url().'receipts/ajax_beb_action'.'"  class="dropdown-toggle element publish_beb" data-placement="bottom"  data-toggle="tooltip"  data-original-title="Publish"    dataid="'.$row['id'].'"  title="Publish BEB" widget-title="Publish BEB"  style="color:green"  > <i class="fa fa-eye"> </i> </a> ';
                             }
                          break;

                          default:
                          break;

                        }


                  if(!empty($lot_count) && ($lot_count[0]['nums'] > 0)) {

                  $table_HTML .=   '<a href="'.base_url().'bids/publish_bidder/active_procurements/editbeb/'.base64_encode($row['id']).'" ><i class=`fa fa-edit`> </i></a>';
                  $table_HTML .=   '<a href="javascript:void(0);" id="'.$row[`id`].'"  dataurl="'.base_url().'receipts/ajax_beblots_action" databidid="'.$row[`bid_id`].'" dataid="'.$row[`bid_id`].'"   title="Cancel All BEB Lots "    class="cancel_beb"><i class="fa fa-trash"></i> </a>';
                   }
                   else
                   {
                  $table_HTML .=  '<a href="'.base_url().'bids/publish_bidder/active_procurements/editbeb/'.base64_encode($row['id']).'" ><i class="fa fa-edit"> </i></a>';
                  $table_HTML .=  '<a href="javascript:void(0);" id="'.$row['id'].'"  dataurl="'.base_url().'receipts/ajax_beb_action" databidid="'.$row['bid_id'].'" dataid="'.$row['id'].'"      class="cancel_beb"><i class="fa fa-trash"></i> </a>';

                   }
               }
           }
            	   $table_HTML .= '</div>';

            	   $table_HTML .='</td>'.
                               '<td>'.$row['procurement_ref_no'].'</td>';



                               $provider = rtrim($row['providers'],',');

                               $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();
                    $table_HTML .= '<td class="hidden-480">';

                                  # print_r($lot_count[0]['nums']);
                                 if(!empty($lot_count) && ($lot_count[0]['nums'] > 0))
                                 {
                    $table_HTML .= '<a href="#" class="view_lots"  id="view_'.$row['bid_id'].'" data-ref="'. base_url() .'receipts/get_beb_lots/ "  >View Lots Awarded</a>';
                                 }
                                 else
                                 {
                               $providerlist = '';
                               $x = 0;

                               foreach($result as $key => $record){
                                $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
                                $x ++ ;
                               }

                              //print_r($providerlist);
                              $providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;

                    $table_HTML .= $providerlists;
                            }
                    $table_HTML .= '</td>'.
                              '<td class="hidden-480">'.$row['subject_of_procurement'].'</td>'.
                              '<td class="hidden-480">';
                            if(!empty($lot_count) && ($lot_count[0]['nums'] > 0)) {
                    $table_HTML .="-";
                            }
                            else   {
                              $readout_prices = $this -> db->query("SELECT * FROM `readoutprices` WHERE receiptid  IN ( SELECT receiptid FROM receipts where bid_id = '".$row['bid_id']."' AND beb ='Y') ") ->result_array();
                            
                    $table_HTML .='<table width="100%">'.
                          '<tr><th>READOUT PRICE</th><th>EXHANGE RATE </th> <th>CURRENCY</th></tr>';
                            foreach ($readout_prices as $key => $record) {
                              # code...
                              if($record['readoutprice'] <=  0) continue;
                    $table_HTML .=    '<tr><td>'.$record['readoutprice'].'</td><td> '.$record['exchangerate'].'</td> <td>'.$record['currence'].'</td></tr>';
                            }
                    $table_HTML .= '</table>';
                    }

                    $table_HTML .= '</td>'.
                              		'<td>';
                    if(!empty($lot_count) && ($lot_count[0]['nums'] > 0)) {
                    $table_HTML .= "-";
                            }
                    else{
                    $table_HTML .='<label style="font-size:10px; text-decoration:none;">'.
                          		  '<input type="checkbox" name="adminreview" value="status"  id="'.$row['id'].'"  dataid="'.$row['id'].'  " dataurl="'. base_url().'receipts/ajax_beb_action"';
                          		   if($row['isreviewed'] == 'Y') 
 					$table_HTML .='checked="checked"';
                    $table_HTML .='class="admin_review">'.
                          		  '<a href="javascript:void(0); ">Under Admin Review</a></label>';


                    switch($row['isreviewed'])
                    {
                                case 'Y':
                    $table_HTML .= " <span class='label label-info minst'> Under Administrative Review </span>";
                    $table_HTML .= "<br/>";
                    $table_HTML .= '<select class="chosen-select" style="width:135px;" onChange="javascript:reviewlevel('.$row['id'].',this.value);" >'.
                              	   '<option selected disabled="true"> Select </option>'.
                              	   '<option';
                    if($row['review_level'] == 'Account Officer')
                    $table_HTML .= 'selected'  ;
                    $table_HTML .= '>Account Officer </option>';
                     if($this->session->userdata('isadmin') == 'Y'){
                    $table_HTML .= '<option';
                     	 if($row['review_level'] == 'PPDA') {
                    $table_HTML .= 'selected'; 
                	} 
                	$table_HTML .= ' >PPDA</option>';
                    $table_HTML .= '<option';
                    if($row['review_level'] == 'Tribunal') 
                     	{ 
                    $table_HTML .= 'selected'; 
                     	} 
                    $table_HTML .='>Tribunal </option>';
                     }
                    $table_HTML .='</select>';


                    if(!empty($row['review_level']))
                    {
                    $table_HTML .='<br/>';
                    $table_HTML .='<a href="#" class="lightbox_cal"  id="review_'.$row['bid_id'].'" data-ref="'.base_url().'receipts/add_review_details" > Add Review Details </a>';                                
                    }


                    break;

                    case 'N':
                    $table_HTML .='-';
                    break;

                    default:
                    $table_HTML .='-';
                    break; 
                       }

                        $reviews = $this->db->query("SELECT COUNT(*) as NUM FROM beb_review_details WHERE beb_review_details.bidid = ".mysql_real_escape_string($row['bid_id'])." and isactive ='Y' ") ->result_array();
 
 						 if($reviews[0]['NUM'] > 0)
                         {
                    $table_HTML .= '<br/>';
                    $table_HTML .=  '<a href="#" class="lightbox_cal"  id="viewreview_'.$row['bid_id'].'" data-ref="'.base_url().'receipts/fetch_admin_review" > View Reviews <span class="badge badge">'.$reviews[0]['NUM'].'</span></a>';
                         
                          }
                    }
                    $table_HTML .= '</td>'.
                                   '<td>'.date('Y-M-d',strtotime($row['dateadded'])).'</td>'.
                                   '</tr>';
                                    array_push( $stack, $bidd);                 


       }
 

	}
	else
	{
		 $table_HTML .= '<tr><td width="100%">'.format_notice('WARNING: No bid invitations have been added to the system').' </td></tr>';
	}
}


#===============================================================================================
# Procurement record details
#===============================================================================================
else if(!empty($area) && $area == 'procurement_record_details')
{
	if(!empty($procurement_details))
	{
	#	print_r($procurement_details);
		$table_HTML .= '<div class="control-group subject_of_procurement">'.
                       '<label class="control-label">Subject of procurement:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['subject_of_procurement'])? $procurement_details['subject_of_procurement'] : '<i>undefined</i>').
					   '<input type="hidden" name="procurement_details[subject_of_procurement]" value="'.$procurement_details['subject_of_procurement'] .'" />'.
					   '</div>'.
                       '</div>'.

					   '<div class="control-group">'.
                       '<label class="control-label">Financial year:</label>'.
                       '<div class="controls">'.
					   (!empty($procurement_details['financial_year'])? $procurement_details['financial_year'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[financial_year]" value="'.$procurement_details['financial_year'] .'" />'.
                       '</div>'.
                       '</div>'.

					   '<div class="control-group">'.
                       '<label class="control-label">Source of funding:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['funding_source'])? $procurement_details['funding_source'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[funding_source]" value="'.$procurement_details['funding_source'].'"/>'.
					   '</div>'.
                       '</div>';

									#		print_r($procurement_details['bidinvitationid']);
					 	if(!empty($procurement_details['bidinvitationid'])){
						$table_HTML .='<input type="hidden" name="bidinvitationid" value="'.$procurement_details['bidinvitationid'].'" />';
					}

				#print_r($ifb_quantity_data[0]['ifb_quantity_sum']);

					if(!empty($procurement_details['quantity'])){

						$total_ifb_q = 0;
					    $remaining_quantity = $procurement_details['quantity'];
						if(empty($ifb_quantity_data[0]['ifb_quantity_sum']))
							 $procurement_details['total_ifb_quantity'] = 0;
						if(!empty($ifb_quantity_data[0]['ifb_quantity_sum']))
						{
						  $total_ifb_q = 	 $procurement_details['total_ifb_quantity'] = $ifb_quantity_data[0]['ifb_quantity_sum'];
						  $remaining_quantity = $procurement_details['quantity'] - $total_ifb_q;

						}
						
						 	//$total_ifb_q = $procurement_details['quantity'] - $ifb_quantity_data[0]['ifb_quantity_sum'];
						
						if($total_ifb_q >= 0)
						{

						}
						else
						{
							$total_ifb_q = 0;
						}
						if(!empty($ifbquantity))
						{
							//$total_ifb_q = $total_ifb_q +  $ifbquantity;
		     $table_HTML .= '<input type="hidden" id="ifb_quantity" name="ifb_quantity" value="'.$ifbquantity.'"/>';
						}
						#fetch IFB quantity ::
		   $table_HTML .= '<div class="control-group">'.
                       '<label class="control-label">Quantity:</label>'.
                       '<div class="controls">'.
                       (!empty($remaining_quantity)? $remaining_quantity : '0').
                       '<input type="hidden" id="procurement_details_quantity" name="procurement_details_quantity" value="'.$remaining_quantity.'"/>'.

					   '</div>'.
                       '</div>';
                     }

                     if(empty($procurement_details['quantity']) && (!empty($procurement_details['procurement_method'])))
                     {

							$total_ifb_q = 0;

		   $table_HTML .= '<div class="control-group">'.
                       '<label class="control-label">Quantity:</label>'.
                       '<div class="controls">'.
                       (!empty($total_ifb_q)? $total_ifb_q : '0').
                       '<input type="hidden" id="procurement_details_quantity" name="procurement_details_quantity" value="'.$total_ifb_q.'"/>'.
					   '</div>'.
                       '</div>';
                     }


		   $table_HTML .='<div class="control-group">'.
                       '<label class="control-label">Method of procurement:</label>'.
                       '<div class="controls">'.
                       (!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>').
                       '<input type="hidden" name="procurement_details[procurement_method]" value="'. $procurement_details['procurement_method'].'" />'.
					   '</div>'.
                       '</div>';
							#Check out to see if  it has lots
							//bidinvitationid
							#receiptid
								$num_records = 0;
							if(!empty($procurement_details['bidinvitationid']))
							{
							$records = $this->db->query("SELECT COUNT(*) as cnt FROM bestevaluatedbidder INNER JOIN receipts ON bestevaluatedbidder.pid =   receipts.receiptid  WHERE  bestevaluatedbidder.lotid > 0 AND  receipts.bid_id = '".$procurement_details['bidinvitationid']."'")->result_array();

								$num_records = $records[0]['cnt'];
							}
						if($num_records > 0)
						{}
							else {

					   if(!empty($procurement_details['providers'])):
					   #$st = 'SELECT * FROM providers WHERE providerid in('.$procurement_details['providers'].')';
					   # print_r($st);

					  $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN ('.$procurement_details['providers'].') ' ) -> result_array();
					  #print_r($procurementdetails);
					  $providers = '<ul>';
					  $xc = '';
					 # $suspended = '';
					  $status = 0;
					  foreach ($procurementdetails as $key => $value) {
					 	# code...
					 	//check provider
					 	 $xc = '';
					 	 // searchprovidervalidity($value['providernames']);


							if(!empty($xc))
							{
								$status =1;
								 $providers .= "<li><div class='label label-warning' title='Suspended Provider' >".$value['providernames']."</div>".'&nbsp; &nbsp; <div class="alert alert-important " style="width:150px; margin-left:5px;">   <button data-dismiss="alert" class="close">×</button> This is a suspended provider    </div> </li>';
								# $suspended .= $value['providernames'].',';
							}
							else
							{
							 $providers .= "<li>".$value['providernames']."</li>";
							}

					 }

					  $providers .= '<ul>';
					 # print_r($procurement_details);
					  $str = '';
					  $vailiditystatus = '0';
					if($procurement_details['bidvalidity'] == 'y')
					{
					 	$enddatebidvalidity =  strtotime($procurement_details['bidvalidityperiod']);
					 	#echo "<BR/>:::::::<BR/>";
					 	#print_r($enddatebidvalidity);
					 	$vailiditystatus = '0';
					 	if(strtotime($enddatebidvalidity) < strtotime(date('Y-m-d')))
					 	{
					 		$vailiditystatus = '1';
					 			 		$str ='<div class="alert alert-info " style="width:250px; margin-left:5px;">   <button data-dismiss="alert" class="close">×</button> Validity Period Expired  on '.date('d M, Y',strtotime($procurement_details['bidvalidityperiod'])).'  </div>' ;
					 	}
					 #	echo "<BR/>:::::::<BR/>";
					 }


					#notify in case of suspended provider
					   $table_HTML .= '<input type="hidden" value="'.$vailiditystatus.'" id="bidvaliditystatus" > <input type="hidden" value="'.$status.'" id="providerstatus" >';

					   $table_HTML .= '<div class="control-group">'.
                       		'<label class="control-label">Selected provider:</label>'.
                       		'<div class="controls">'.
                       		 rtrim($providers,',').
                       		'<input type="hidden" name="provider" value="'.$procurement_details['providers'].'"/>'.
							'<input type="hidden" name="provider_info" value="'.(empty($procurement_details['id'])? 0 :$procurement_details['id']).'"/>'.
					   		'</div>'.
                       		'</div>';
                       $table_HTML .= $str;
					   endif;
					 }

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



				$xx = 0;
				//print_r($active['page_list']); exit();
foreach($page_list as $row)
{
	$xx ++;
	
	$table_HTML .= '<tr  id="active_'.$row['pdeid'].'">'.

			   '<td>
		           <a href="'.base_url().'pdes/load_edit_pde_form/'.base64_encode($row['pdeid']).'"> <i class="fa fa-edit"></i></a>
		           <a href="#" id="savedelpde_'.$row['pdeid'].'" class="savedelpde"> <i class="fa fa-trash"></i></a>
				</td>'.

				'<td  class="actived">'.$xx.'</td>'.
				'<td  class="actived">'.$row['pdename'].'</td>'.
				'<td  class="actived">'.$row['abbreviation'].'</td>'.
				'<td  class="actived">'.$row['category'].'</td>'.
				'<td  class="actived">'.$row['code'].'</td>'.
				'</tr>';
	 
 }
				 
	} else {
		$table_HTML .='<tr><td colspan="100%" >'. format_notice("ERROR: Could not find the PDE record details.").'</td></tr>';
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

?> 
				<?php
				$xx = 0;
foreach($page_list as $row)
{
	$xx ++;
	?>
	<tr  id="active_<?=$row['pdetypeid']; ?>">

		<td>
						<?php
		if($level=='active') 
			{?>

						 <a href="<?=base_url().'pdetypes/load_edit_pde_form/'.base64_encode($row['pdetypeid']); ?>"> <i class="fa fa-edit"></i></a>
						 <a href="#" id="savedelpdetype_<?=$row['pdetypeid'];?>" class="savedelpdetype"> <i class="fa fa-trash"></i></a>
		<?php } ?>
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
						 


					</tr>

	<?php
}
				?>


			 

<?php

	} else {
		$table_HTML .= '<tr> <td colspan="100%"> '.format_notice("ERROR: Could not find the PDE Type record details.").'</td></tr>';
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




                       $(".view_lots").click(function(){
                       $("#lightbox_wrapper").fadeIn('slow');
                       $("#lightbox_heading").html(" ");
                       $("#lightbox_body").html("Proccessing....");
                       formdata = {};
                       //url
                       var idd = this.id;
                       var url = $("#"+idd).attr('data-ref');
                       contract = idd.split("_");
                       contractid = contract[1];
                        console.log(url);
                        formdata['bidid'] = contractid;
                       console.log(formdata);


                          $.ajax({

                              type: "POST",
                              url:  url,
                              data: formdata,
                              success: function(data, textStatus, jqXHR){
                              $("#lightbox_body").html(data);
                              
                              },
                              error:function(data , textStatus, jqXHR)
                              {
                                  console.log('Data Error'+data+textStatus+jqXHR);
                              }

                            });
                          });

                  //lightbox fetch
                    $(".lightbox_cal").click(function(){
                       $("#lightbox_wrapper").fadeIn('slow');
                       $("#lightbox_heading").html(" ");
                       $("#lightbox_body").html("Proccessing....");
                       formdata = {};
                       //url
                       var idd = this.id;
                       var urld = $("#"+idd).attr('data-ref');
                      // alert(urld);
                       contract = idd.split("_");
                       contractid = contract[1];
                       console.log(urld);
                       formdata[''+contract[0]+''] = contractid;
                       console.log(formdata);

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
                   
                          });





//variations proccessing adons 


     $(".view_variations").click(function(){
     $("#lightbox_wrapper").fadeIn('slow');
     $("#lightbox_heading").html(" ");
     $("#lightbox_body").html("Proccessing....");
     formdata = {};
     //url
     var idd = this.id;
     var url = $("#"+idd).attr('data-ref');
     contract = idd.split("_");
     contractid = contract[1];
     console.log(url);
     formdata['contractid'] = contractid;

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,
            success: function(data, textStatus, jqXHR){
            $("#lightbox_body").html(data);
            },
            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

          });


 //end url fetch
 //alert(url);

});





xx = 0;
     $(".togglecalloforders").click(function(){

//start

 var strng = '<div style="width:80%;margin:auto; text-aligh:center;" >'+
            ' <div class="control-group"><h2>ADD CALLOFF ORDER </h2></div>'+

            '<div class="container-fluid">'+
            '<div class="row">'+
            '<div class="col-md-12">'+
            '<form role="form">'+
            '<div class="form-group">'+
           
            '<label for="call_off_order_no">'+
            ' Call off order No.'+
            '</label>'+
            '<input type="text"  class="form-control call_off_order_no" id="call_off_order_no" style="width:100%" />'+
            '</div>'+
        
        '<div class="form-group">'+           
          '<label for="exampleInputPassword1">'+
            ' Subject of Procurement.'+
          '</label>'+
          '<textarea type="email" class="form-control subject_of_procurement" style="width:100%" id="subject_of_procurement" >'+
          '</textarea>'+
         '</div>'+
        
       ' <div class="form-group">'+
          '<label for="exampleInputPassword1">'+
           ' Date of Call off Order'+
          '</label>'+
         ' <input type="date" class="form-control date_of_calloff_orders" style="width:100%" id="date_of_calloff_orders" />'+         
        '</div>'+
        
        '<div class="form-group">'+
          '<label for="user_department">'+
            'Name of User Department'+
          '</label>'+
          '<input type="text" class="form-control user_department"  style="width:100%" id="user_department" /> '+        
       ' </div>'+
        
        
        '<div class="form-group">'+
          '<label for="contract_value">'+
            ' Contract Value (UGX)'+
          '</label>'+
          '<input type="amount"  onChange="javascript:$(this).val(addCommas($(this).val()))" class="form-control   contract_value" style="width:100%" id="contract_value" /> '+        
        '</div>'+
        
        '<div class="form-group">'+
          '<label for="planned_completion_date">'+
            ' Planned Contract Completion Date'+
         ' </label>'+
         ' <input type="date" class="form-control planned_completion_date" style="width:100%"  id="planned_completion_date" /> '+        
        '</div>'+
        
        '<div class="form-group">'+
          '<label for="actual_completion_date">'+
             'Date of Actual Contract Completion'+
          '</label>'+
          '<input type="date" class="form-control actual_completion_date" style="width:100%"  id="actual_completion_date" />'+         
        '</div>'+
        
       ' <div class="form-group">'+
          '<label for="total_actual_payments">'+
            ' Total Actual Payments Up to End of Reporting Period'+
         ' </label>'+
          '<input type="text"   onChange="javascript:$(this).val(addCommas($(this).val()))" class="form-control total_actual_payments" style="width:100%"  id="total_actual_payments" />'+         
        '</div>'+
        
        
        
        '<button type="button" onClick="javascript:$(`#lightbox_wrapper`).fadeOut(`slow`);" class="btn btn-default" data-dismiss="modal">Close</button>'+
       ' &nbsp;'+
        '<button onClick="javascript:add_callof_order();" type="button" class="btn  add_callof_order">Submit</button> '
        
         
      '</form>'+
    '</div>'+
  '</div>'+
'</div>'+ '</div>';



//end
     contractid = this.id;
     $("#lightbox_wrapper").fadeIn('slow');
     $("#lightbox_heading").html(" ");
     $("#lightbox_body").html("Proccessing....");

     $("#lightbox_body").html(strng);


       // $("#myModal").modal('toggle');
        if(xx == 1)
        {

         // $("#tblform").fadeOut('slow');
           xx = 0;
        }
        else
        {
         // $("#tblform").fadeIn('slow');
           xx = 1;
        }

     });



  

      $(".viewlistcalloff").click(function(){

        contractid = this.id;
      //  $("#viewlist").modal('toggle');
        //var vst = this.data('procurement');
        //alert(vst);

     $("#lightbox_wrapper").fadeIn('slow');
     $("#lightbox_heading").html(" ");
     $("#lightbox_body").html("Proccessing....");




        var url = baseurl()+"contracts/viewcalloutcontracts";
        var formdata = {};
        if(contractid <= 0)
        return;

        formdata['contractid'] = contractid;
        console.log(formdata);
        
        $.ajax({

        type: "POST",
        url:  url,
        data: formdata,

        success: function(data, textStatus, jqXHR){
        // $(".viewcalloff_body").html(data);
        $("#lightbox_heading").html(" ");
        $("#lightbox_body").html(data);          
        console.log(data);
        //alert(data); exit();
        },

        error:function(data , textStatus, jqXHR)
        {
            console.log('Data Error'+data+textStatus+jqXHR);
            //alert(data);
        }

      });
 

     });
	 
	 
	 $('li.dynamic_menu').click(function() {
        	var id = $(this).data('details');
			$('#info').html('GENERATING CONTENT FOR SECTION');
			
			var form_data =
            {
                section_id: id
            };
			
			$.ajax({
                url: "<?=base_url().'faqs/load_section' ?>",
                type: 'POST',
                data: form_data,
                success: function(msg) {
                    $('#info').html(msg);

                }
            });
            return false;
		
    	}); 	
		






</script>
