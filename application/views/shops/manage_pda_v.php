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



</script>
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage  PDE's</h4>
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
				<ul class="nav nav-tabs">
					<li class=" <?php if($level=='active') { ?> active <?php } else { ?> disabled <?php }  ?>  " >
						<a  href="<?=base_url()."admin/manage_pdes/level/active/"; ?>"   > Active </a>
					</li>
				 	<li class="<?php if($level=='archive') { ?> active <?php } else { ?> disabled <?php }  ?>  " >
						<a href="<?=base_url()."admin/manage_pdes/level/archive/"; ?>"   > Archived </a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active dvq" id="panel-active">
				 
	<table class="table  table-striped">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							 <em class="glyphicon glyphfa fa-user"></em>
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
						 <a href="<?=base_url().'pdes/load_edit_pde_form/'.base64_encode($row['pdeid']); ?>"> <i class="fa fa-edit"></i></a>
						 <a href="#" id="savedelpde_<?=$row['pdeid'];?>" class="savedelpde"> <i class="fa fa-trash"></i></a>

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
	<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
						"admin/manage_pdes/level/".$level."/p/%d")
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