<script type="text/javascript">
     $(document).on('click','.printer',function(){
 
 
    $(".table").printArea();
    
  })


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

    $("div.dataTables_filter input").unbind();

    $('#filter').click(function(e){
        oTable.fnFilter($("div.dataTables_filter input").val());
    });

} );



</script><div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage users</h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body" id="results">
    	<?php 
			if(!empty($page_list)): 
				
				print '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Name</th>'.
					  '<th class="hidden-480">SHOP</th>'.
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
					
					$delete_str = '<a title="Delete user details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'admin/delete_user/i/'.encryptValue($row['userid']).'\', \'Are you sure you want to delete this user?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
					
					$edit_str = '<a title="Edit user details" href="'. base_url() .'user/load_user_form/i/'.encryptValue($row['userid']).'"><i class="fa fa-edit"></i></a>';
					
					print '<tr>'.
						  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'</td>'.
						  '<td>'. (!empty($row['prefix'])? $row['prefix'] . ' ' : '') . $row['firstname'] . ' ' . $row['lastname'] .'</td>'.
						  '<td>'. $row['shopname'] .'</td>'.
						  '<td>'. format_to_length($user_roles_text, 50) .'</td>'.
						  '<td>'. $row['emailaddress'] .'</td>'.
						  '<td>'. $row['telephone'] .'</td>'.
						  '<td>'. custom_date_format('d M, Y',$row['dateadded']) .'</td>'.
						  '</tr>';
				}
				
				
				print '</tbody></table>';
				
				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
						"admin/manage_users/p/%d")
					.'</div>';
		
			else:
        		print format_notice('WARNING: No users have been added to the system');
        	endif; 
        ?>
    </div>
</div>