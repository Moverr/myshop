
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Manage  Stock </h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>

    <div class="tabbable" style="padding-left:30px; " id="tabs-45158">
			
			    <!-- Navigation Tabs Active Archived and Cancelled and Financial Year -->
				<ul class="nav nav-tabs">
				
				   <!-- Active -->
					<li class=" <?php if(!empty($level) && ($level == 'overview'))  {  echo "active"; } ?> " onClick="javascript:location.href='<?=base_url().'stock/manage_stock/level/overview/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'">
						<a href="<?=base_url().'stock/manage_stock/level/overview/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder-open"> </i>  STOCK OVERVIEW <span class="badge badge-info"> 0  </span></a>
					</li>
					
					<!-- inventoryd -->
					<li onClick="javascript:location.href='<?=base_url().'stock/manage_stock/level/inventory/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'"  class="<?php if(!empty($level) && ($level == 'inventory'))  {  echo "active"; } ?>">
						<a href="<?=base_url().'stock/manage_stock/level/inventory/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder"> </i>  STOCK INVENTORY  <span class="badge badge-info">0</span></a>
					
					</li>
					
					<!-- Canceled IFBS -->
						<li onClick="javascript:location.href='<?=base_url().'stock/manage_stock/level/cacnceled/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>'"  class="<?php if(!empty($level) && ($level == 'cacnceled'))  {  echo "active"; } ?>">
						<a href="<?=base_url().'stock/manage_stock/level/cacnceled/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder"> </i>  CANCELED  <span class="badge badge-info">0</span></a>
					
					</li>
					
					<!-- Financial years -->
					<li style="float: right;">


				 
					<select class="chosen financial_year_selection">
						   <?=get_select_options($financial_years, 'financial_year', 'financial_year', (!empty($current_financial_year)? $current_financial_year : '' ))?> 

					</select>
					</li>
				</ul>
			 
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
		
if($level =='overview' )  
{
		?>
		<table class="table datatable table-striped">
	 <thead> <tr>  <th> <i class="fa  fa-institution"></i> </th>
		  <th> ITEM </th> <th> UNIT MEASURE </th>
         <th>  ITEMS ADDED  </th>   <th>  ITEMS AVAILABLE  </th>  <th> UNIT SELLING PRICE </th> <th> RESERVE PRICE </th>
		   </tr> </thead>
	
	<tbody>
	<?php

}
else if($level =='inventory' )  
{
	?>
		<table class="table datatable table-striped">
	 <thead> <tr> <th> # </th> <th> <i class="fa  fa-institution"></i> </th>
		 <th> STOCK # </th> <th> PURCHASE # </th> <th> ITEM </th> <th> UNIT MEASURE </th>
         <th> NUMBER OF UNITS  </th> <th> UNIT SELLING PRICE </th> <th> RESERVE PRICE </th>
		 <th>  DATE ADDED </th> <th> AUTHOR </th> </tr> </thead>
	
	<tbody>
	<?php
}
?>

	
				<?php
				$xx = 0;
				//print_r($active['page_list']); exit();
		 
foreach($list['page_list'] as $key => $row)
{
 

 #print_r($row);

	switch ($level) {
		case 'overview':
			# code...
		?>
		

		<?php

		 
 
	$xx ++;
	?>
	<tr  >

		 

						<td  class="actived">
							<?=$xx; ?>
						</td>
						 
						<td  class="actived">
							<?=$row['name']; ?>
						</td>
						<td  class="actived">
							<?=$row['unit_measure']; ?>
						</td>
						<td  class="actived">
							<?=number_format($row['stock_added']); ?>
						</td>

						<td  class="actived">
							 - 
						</td>

						<td  class="actived">
							<?=number_format($row['unit_selling_price'] * $row['unit_selling_price_exhange_rate']); ?>
						</td>

						<td  class="actived">
							<?=number_format($row['reserve_price'] * $row['reserve_price_exchange_rate']); ?>
						</td>

						  
						 
						
						
					</tr>
				 
	<?php

		 

			break;

		case 'inventory':
		
	?>
	 
	<?php
 
	$xx ++;
	?>
	<tr  >

		<td> 
				<a href="<?=base_url().'stock/add_stock/i/'.base64_encode($row['id']); ?>"> <i class="fa fa-edit"></i></a> 

				 <a href="#" data-url="<?=base_url().'stock/verify/action/deletestock';?>" 

				  id="deleteitem_<?=$row['id'];?>" class="savedelpde"> 
						 <i class="fa fa-trash"></i></a>

						</td>

						<td  class="actived">
							<?=$xx; ?>
						</td>
						<td  class="actived">
							<?=$row['stock_no']; ?>
						</td>
						<td  class="actived">
							<?=$row['purchase_no']; ?>
						</td>
						<td  class="actived">
							<?=$row['name']; ?>
						</td>
						<td  class="actived">
							<?=$row['unit_measure']; ?>
						</td>
						<td  class="actived">
							<?=number_format($row['no_of_units']); ?>
						</td>
						<td  class="actived">
							<?=number_format(($row['unit_selling_price']*$row['unit_selling_price_exhange_rate'])).'UGx'; ?>
						</td>

						<td  class="actived">
							<?=number_format($row['reserve_price']*$row['reserve_price_exchange_rate']).'UGx'; ?>
						</td>

						 
						<td  class="actived">
							<?=$row['date_added']; ?>
						</td>
							<td  class="actived">

							
							<?=get_user_info_by_id($row['added_by'],'fullname'); ?>
						</td>
						
						
					</tr>
				 
	<?php

		# code...
			break;
		

		default:
			# code...
			break;
	}

}
				?>
					 
					 
				</tbody>
			</table>
	<?php

	 print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_list'), $list['rows_per_page'], $list['current_list_page'], base_url().	
						"stock/manage_stock/p/%d")
						.'</div>'; 



}
else
{
	print format_notice('WARNING: No Item Categories  have been added to the system');
}

?>
	 
					 <!-- End -->
					</div>
					 
	</div>

 
		</div>
	 
</div>