 
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage  Item Categories </h4>
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
<div id="list"> 
				 
		<?php
if($list['page_list'])
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
							CATEGORY
						</th>
						<th>
							ABBREVIATION
						</th>
						<th>
						   DETAILS
						</th>
						<th>
							AUTHOR
						</th>

						<th>
							STATUS
						</th>

						<th>
							DATE ADDED
						</th>

						 						
						
						 
					</tr>
				</thead>
				<tbody>
				<?php
				$xx = 0;
				//print_r($active['page_list']); exit();
foreach($list['page_list'] as $row)
{

	#print_r($row);
	#exit();

	$xx ++;
	?>
	<tr  >

		<td>
						 <a href="<?=base_url().'items/addcategory/i/'.base64_encode($row['id']); ?>"> <i class="fa fa-edit"></i></a>
						 <a href="#" data-url="<?=base_url().'items/verify/action/deletecategory';?>" delete_category" id="deleteitem_<?=$row['id'];?>" class="savedelpde"> 
						 <i class="fa fa-trash"></i></a>

		</td>

						<td  class="actived">
							<?=$xx; ?>
						</td>
						<td  class="actived">
							<?=$row['category']; ?>
						</td>
						<td  class="actived">
							<?=$row['abbreviation']; ?>
						</td>
						<td  class="actived">
							<?=$row['details']; ?>
						</td>
						<td  class="actived">
					 
							<?=get_user_info_by_id($row['added_by'],'fullname'); ?>
						</td>
						
						<td  class="actived">
						<?php
							switch ($row['status']) {
								case 'Y':
									 echo "Active";
									break;
								case 'N':
									 echo "Archive";
								break;
								
								default:
									# code...
									break;
							}
						?>
							 
						</td>
							<td  class="actived">
							<?=custom_date_format('Y -M-d',$row['date_aded']); ?>
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
	print format_notice('WARNING: No Item Categories  have been added to the system');
}

?>
	<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_list'), $list['rows_per_page'], $list['current_list_page'], base_url().	
						"branches/lists/p/%d")
						.'</div>'; 

						?> 
					 <!-- End -->
					</div>
					 
	</div>

 
		</div>
	 
</div>