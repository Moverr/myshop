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

} );



</script>
<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;All Help Sections</h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body" id="results">
        <?php 
            if(!empty($page_list)): 
                
                print '<table class="table table-striped table-hover">'.
                      '<thead>'.
						  '<tr>'.
							  '<th width="94px"></th>'.
							  '<th>Help Topic</th>'.
							  '<th>Help Header</th>'.                      
							  '<th class="hidden-480">Date Added</th>'.
						  '</tr>'.
                      '</thead>'.
                      '</tbody>';
                
                foreach($page_list as $row)
                {
                    $delete_str = '<a title="Delete help details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'faqs/delete_help/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
          
          			$edit_str = '<a title="Edit help details" href="'. base_url() .'faqs/edit_help_section/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
          
          			print '<tr>'.
					      '<td>';
						  print  $delete_str .'&nbsp;&nbsp;'. $edit_str;
					print '</td>';
					
					print '<td>'.$row['faq_topic'].'</td>'.
						  '<td>'.$row['faq_header'].'</td>'.
						  '<td>'.custom_date_format('d M, Y', $row['datecreated']).'</td>';
					
					
					print '</tr>';
                }
                
                print '</tbody></table>';

                print '<div class="pagination pagination-mini pagination-centered">'.pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url()."faqs/list_all_help/p/%d").'</div>';

        
            else:
                print format_notice('WARNING: No help sections are available in the system');
            endif; 
        ?>
    </div>
</div>