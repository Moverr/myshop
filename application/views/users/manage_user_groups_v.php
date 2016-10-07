<script type="text/javascript">
     $(document).on('click','.printer',function(){
 
 
    $(".table").printArea();
    
  })


$(document).ready(function() {
  $('.table').dataTable({
     "paging":   false,
        "ordering": true,
        "info":     false });
        var oTable = $('.table').dataTable();

    $("div.dataTables_filter input").unbind();

    $('#filter').click(function(e){
        oTable.fnFilter($("div.dataTables_filter input").val());
    });
  
  /*$('.table tbody').on('click', 'tr', function () {
    var name = $('td', this).eq(0).text();
    alert( 'You clicked on '+name+'\'s row' );
  } );  */
} );



</script><div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage user groups</h4>
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
					  '<th width="50px"></th>'.
					  '<th>User group</th>'.
					  '<th class="hidden-480">No. of Members</th>'.
					  '<th class="hidden-480">Author</th>'.
					  '<th class="hidden-480">Date added</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';
				
				foreach($page_list as $row)
				{
					$delete_str = $edit_str = '';
					if($row['usergroupid'] != 14)
					{
						$delete_str = '<a title="Delete user group" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'admin/delete_user_group/i/'.encryptValue($row['usergroupid']).'\', \'Are you sure you want to delete this user group?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
					
						$edit_str = '<a title="Edit user group details" href="'. base_url() .'admin/user_group_form/i/'.encryptValue($row['usergroupid']).'"><i class="fa fa-edit"></i></a>';
					}
					
					$permissions_str = '<a title="View user group permissions" href="'. base_url() .'admin/user_group_permissions/i/'.encryptValue($row['usergroupid']).'"><i class="fa fa-lock"></i></a>';
					
					print '<tr>'.
						  '<td>'. $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $permissions_str .'</td>'.
						  '<td>'. $row['groupname'] .'</td>'.
						  '<td>'. $row['numOfUsers'] .'</td>'.
						  '<td>'. $row['authorname'] .'</td>'.
						  '<td>'. custom_date_format('d M, Y', $row['dateadded']) .'</td>'.
						  '</tr>';
				}
				
				print '</tbody></table>';
		
			else:
        		print format_notice('WARNING: No user groups have been added to the system');
        	endif; 
        ?>
    </div>
</div>