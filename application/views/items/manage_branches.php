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



</script>
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage  Branches</h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body">


    <!-- start -->

<div class="row-fluid">
		<div class="span12">
		<?php #$this->load->view('includes/modal'); ?>
			<div class="tabbable" id="tabs-358950">
<div id="results"> 
				 
		<?php
if($results['page_list'])
{
		?>	 

	<table class="table  table-striped">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							 <i class="fa  fa-institution"></i>
						</th>
						<th>
							SHOP NAME
						</th>
						<th>
						BRANCH NAME
						</th>
						<th>
							BRANCH SHORTCODE
						</th>

						<th>
							BRANCH ADDRESS
						</th>

						<th>
							AUTHOR
						</th>

						<th>
							DATEADDED
						</th>						
						
						 
					</tr>
				</thead>
				<tbody>
				<?php
				$xx = 0;
				//print_r($active['page_list']); exit();
foreach($results['page_list'] as $row)
{
	$xx ++;
	?>
	<tr  >

		<td>
						 <a href="<?=base_url().'branches/add/i/'.base64_encode($row['id']); ?>"> <i class="fa fa-edit"></i></a>
						 <a href="#" id="savedelpde_<?=$row['shopid'];?>" class="savedelpde"> <i class="fa fa-trash"></i></a>

		</td>

						<td  class="actived">
							<?=$xx; ?>
						</td>
						<td  class="actived">
							<?=$row['shopname']; ?>
						</td>
						<td  class="actived">
							<?=$row['branchname']; ?>
						</td>
						<td  class="actived">
							<?=$row['shortcode']; ?>
						</td>
						<td  class="actived">
							<?=$row['address']; ?>
						</td>
						<td  class="actived">
							<?=$row['author_name']; ?>
						</td>
						<td  class="actived">
							<?=date(" M d, Y", strtotime($row['dateadded'])); ?>
						</td>
						
					</tr>
				 
	<?php
}
				?>
					 
					 
				</tbody>
			</table>
	<?php
}
else
{
	print format_notice('WARNING: No Branches have been added to the system');
}

?>
	<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $results['rows_per_page'], $results['current_list_page'], base_url().	
						"branches/lists/p/%d")
						.'</div>'; 

						?> 
					 <!-- End -->
					</div>
					 
	</div>

 
		</div>
	 
</div>