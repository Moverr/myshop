<div class="widget ">
    <div class="widget-title">
        <!-- <h4><i class="icon-reorder"></i>&nbsp;Manage Bid Invitations</h4> -->
            <span class="tools">
              <!--   <a href="javascript:;" class="fa fa-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>   -->
            </span>
    </div>
    <div class="widget-body tab-content results " id="results">
    	<?php


			if(!empty($list)):

				print '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Title</th>'.
					  '<th class="hidden-480">Date</th>'.
					   '<th class="hidden-480">Last updated</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';


				#$xx = 0;
				foreach($list as $row)
				{


					$status_str = '';
					$addenda_str = '[NONE]';
					$delete_str ='';
					$edit_str  = '';
				    
				    if($this->session->userdata('isadmin') == 'Y')
					{
						$delete_str = '<a title="Delete public holiday" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'public_holiday/delete/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this public holiday?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';

						$edit_str = '<a title="Edit public holiday" href="'. base_url() .'public_holiday/load_form/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';

					}


					print '<tr>'.
						  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
						  '<td>'. htmlspecialchars_decode($row['title'], ENT_QUOTES) .'</td>'.
						  '<td>'. display_date( $row['holiday_date']) .'</td>'.
						  '<td>'. display_date( $row['last_updated']).'</td>'.
						  '</tr>';
				}

				print '</tbody></table>';

				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), NUM_OF_ROWS_PER_PAGE, $current_list_page, base_url().
						"public_holiday/lists/p/%d")
					.'</div>';

			else:
        		print format_notice('WARNING: No public holidays have been added to the system. Click <a href="'. base_url() .'public_holiday/load_form"><i>here</i></a> to add a public holiday');
        	endif;
        ?>
    </div>

</div>