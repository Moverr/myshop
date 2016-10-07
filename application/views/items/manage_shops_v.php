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




<?php



 

?>


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
			if(!empty($list['page_list'])): 
				
				print '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Shop Name</th>'.
					  '<th class="hidden-480">Address</th>'.
					  '<th class="hidden-480">Author</th>'.					  
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';
				
				foreach($list['page_list'] as $row)
				{
					 

					$delete_str = '<a title="Delete user details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'shops/verify/action/delete/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this user?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-edit"></i></a>';
					
					$edit_str = '<a title="Edit user details" href="'. base_url() .'shops/add/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
					
					print  '<tr>'.
					  '<td width="5%">'.$delete_str.$edit_str.'</td>'.
					  '<td>'.$row['shopname'].'</td>'.
					  '<td >'.$row['location'].'</td>'.
					  '<td  >'.get_user_info_by_id($row['author'],"fullname").'</td>'.					  
					  '</tr>';
				}
				
				
				print '</tbody></table>';
				
				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $list['rows_per_page'], $list['current_list_page'], base_url().	
						"admin/manage_users/p/%d")
					.'</div>';
		
			else:
        		print format_notice('WARNING: No users have been added to the system');
        	endif; 
        ?>
    </div>
</div>