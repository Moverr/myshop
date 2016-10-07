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

})




</script><div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;Manage  PDE Types</h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body">


    <!-- start -->

<div class="row-fluid">
		<div class="span12">
			<div id="results">
		<?php $this->load->view('includes/modal'); ?>
			<div class="tabbable" id="tabs-358950">
				<ul class="nav nav-tabs">
					 
					<li class=" <?php if($level=='active') { ?> active <?php } else { ?> disabled <?php }  ?>  " >
						<a  href="<?=base_url()."admin/manage_pdetypes/level/active/"; ?>"   > Active </a>
					</li>
				 	<li class="<?php if($level=='archive') { ?> active <?php } else { ?> disabled <?php }  ?>  " >
						<a href="<?=base_url()."admin/manage_pdetypes/level/archive/"; ?>"   > Archived </a>
					</li>

				</ul>
				<div class="tab-content">
					<div class="tab-pane active  dvq" id="panel-active">
					<!--start -->
<!-- <form class="form-search">
				 <button class="btn btn-medium" type="button">Add + </button> &nbsp; &nbsp; <label> Search </label> : &nbsp; <input type="text" class="input-medium search-query" /><button type="submit" class="btn">Go</button> <button type="submit" class="btn">Print</button>
			</form> -->
					<!-- end -->
					 <!-- Active Table COntent -->
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
							PDE Type
						</th>
						<th>
						Date Created
						</th>
						 
						 
						
						 
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
					 
					 
				</tbody>
			</table>
<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
						"admin/manage_pdetypes/p/%d")
						.'</div>'; 

						?> 
					 <!-- End -->
					</div>
					
				</div>
			</div>

		</div>
		</div>
	</div>

 
		</div>
	 
</div>