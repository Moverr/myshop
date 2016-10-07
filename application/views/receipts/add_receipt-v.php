<?php

function fetchnum_bidders($ref){
	$st = "SELECT COUNT(*) as sm ,r.bid_id as bid FROM receipts r INNER JOIN bidinvitations as b on r.bid_id = b.id  WHERE b.procurement_ref_no = '".$ref."'" ;

	$q1 = mysql_query($st);
	# print_r($st);
	 $q2 =  mysql_fetch_array($q1)or die("".mysql_error());
	
	 return $q2;
				
}
?>
<div class="widget">
<div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;<?=$page_title; ?></h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
  <div class="widget-body">
<div class="row-fluid">
	<?php
	if((isset($level)) && (!empty($level)))
	switch ($level) {
		case 'publish_bidder':
			# Evaluate Bidder...
		#procurementdetails
		#print_r($procurementdetails[0]['procurement_ref_no']); exit();
		?>
		<div class="span12">
			<div class="accordion" id="accordion-562508">
				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#procurement_record">Procurment Record</a>
					</div>
					<div id="procurement_record" class="accordion-body   in">
						<div class="accordion-inner">
						 

							 <div class="row-fluid">
									 <label class="span4">Approved Procurement : </label>
									  <label class="span6"> <?=$procurementdetails[0]['proctype'] ?> : [<?=$procurementdetails[0]['procurement_ref_no'] ?>] </label>
									 	<label class="span4"> </label><a href="#" class="link span6 "> More details </a>
							 </div>
						</div>
					</div>
				</div>

				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#bid_activity">Bid Activity Schedule</a>
					</div>
					<div id="bid_activity" class="accordion-body  in ">
						<div class="accordion-inner">
							  <?php 
							  #print_r($bidinformation);
							  ?>
							   <div class="row-fluid">
									 <label class="span4">Deadline of bid submission : </label>
									  <label class="span3"><?=$bidinformation[0]['bid_submission_deadline']; ?></label>
									  <label class="span2">Time * </label>
									 	<label class="span3"> 12:00PM </label> 
							 </div>
							  



						</div>
					</div>
				</div>

				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#bids_received">Bids Received</a>
					</div>
					<div id="bids_received" class="accordion-body  in ">
						<div class="accordion-inner">
							  <?php
							  #print_r($receiptinfo);
							  ?>
							   <div class="row-fluid">
									 <label class="span4">Number of bids submission </label>
									  <a class="span2" href="#"> <?=$receiptinfo[0]['sm']; ?> </a>
									 	<label class="span4"> Local bids</label><a href="#" class="link span2 "><?=$localbids[0]['sm']; ?></a>
							 </div>
							  



						</div>
					</div>
				</div>



				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#bid_evaluation">Bid Evaluation</a>
					</div>
					<div id="bid_evaluation" class="accordion-body  in ">
						<div class="accordion-inner">
							 <div class="row-fluid">
							 	<?php
							 	#print_r($evaluation_methods);
							 	?> 
									<label class="span4"> Type of evaluation <br/>methodology applied : </label>
									<select  class="span8 chosen" data-placeholder="Type of Evaluation" tabindex="1">
<?php
										foreach ($evaluation_methods as $key => $value) {
											# code...
											?>
											<option value="<?=$value['evaluation_method_id']; ?>"><?=$value['evaluation_method_name']; ?></option>
											<?php
										}
										?>
									 </select>
							 </div>

							  <div class="row-fluid">
									<label class="span4"> Number of technically <br/>responsive bids evaluated </label>
									<input type="text" class="datepicker span8" class="span8"> 
							 </div>

							  <div class="row-fluid">
									<label class="span4"> Date of commencement of the <br/>evaluation : </label>
									 <div class="controls span8">
	         							<div class="controls span12">
	                                        <div class="input-append date date-picker span8" data-date="12-02-2012" data-date-format="dd-mm-yyyy" data-date-viewmode="years">
	                                            <input class=" m-ctrl-medium date-picker span10"   type="text" value="12-02-2012" /><span class="add-on"><i class="icon-calendar"></i></span>
	                                       	 </div>
	                                    </div>
    								 </div> 	
							 </div>

							 <div class="row-fluid">
									<label class="span4"> Date of combined  evaluation  <br/>report : </label>
									 <div class="controls span8">
	         							<div class="controls span12">
	                                        <div class="input-append date date-picker span8" data-date="12-02-2012" data-date-format="dd-mm-yyyy" data-date-viewmode="years">
	                                            <input class=" m-ctrl-medium date-picker span10"   type="text" value="12-02-2012" /><span class="add-on"><i class="icon-calendar"></i></span>
	                                       	 </div>
	                                    </div>
    								</div> 	
								</div>

							  <div class="row-fluid">
									<label class="span4"> Date of approval of the final   <br/> evaluation report by the contracts committee : </label>
									 <div class="controls span8">
	         							<div class="controls span12">
	                                        <div class="input-append date date-picker span8" data-date="12-02-2012" data-date-format="dd-mm-yyyy" data-date-viewmode="years">
	                                            <input class=" m-ctrl-medium date-picker span10"   type="text" value="12-02-2012" /><span class="add-on"><i class="icon-calendar"></i></span>
	                                       	 </div>
	                                    </div>
    								</div> 	
								</div>


						</div>
					</div>
				</div>


				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#bidder_details_contactprice">Bidder Details and Contract Price</a>
					</div>
					<div id="bidder_details_contactprice" class="accordion-body  in ">
						<div class="accordion-inner">
							  <div class="row-fluid">
							  	<?php 
							  	#print_r($providerslist);
							  	?>
									<label class="span4">  BEB Name :  </label>
									<select class="span8 chosen" data-placeholder="BEB Name" tabindex="1">
									<?php
									foreach ($providerslist as $key => $value) {
										# code...
										?>
										<option value="<?=$value['providerid']; ?>"><?=$value['providernames']; ?></option>
										<?php

									}
									?>
									</select>
							 </div>

							   <div class="row-fluid">
									<label class="span4">  Nationality  </label>
									<select class="span8 chosen" data-placeholder="Nationality" tabindex="1">
										<option> Uganda </option>
									</select>
							 </div>

							   <div class="row-fluid">
									<label class="span4">  Contract Price :  </label>
									<input type="text" class="datepicker span8" class="span8"> 
							 </div>

						</div>
					</div>
				</div>

				<div class="accordion-group">
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508" href="#unsuccessful_bidders">Unsuccessful Bidders</a>
					</div>
					<div id="unsuccessful_bidders" class="accordion-body  in ">
						<div class="accordion-inner">
							   <div class="row-fluid">
									<label class="span4"> Bidders Name :  </label>

									<input type="text" class="datepicker span8" class="span8"> 
							 </div>

						</div>
					</div>
				</div>



			</div>
		</div>
		<?php
			break;
		
		case 'active_procurements':
			# code...
		?>
		<div class="row-fluid">
		<div class="span12">

<table class="table table-bordered">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							Procurement Ref # 
						</th>
						<th>
							Subject of iFB
						</th>
						<th>
							No of Bids
						</th>
						 


					</tr>
				</thead>
				<tbody>
			<?php

			 #print_r($active_procurements); 
			foreach ($active_procurements as $key => $value) {
				# code...
				?>
					<?php  $q = fetchnum_bidders( $value['procurement_ref_no']); 


					?>
					
				<tr  >
						<td>
						 <?php
						 $pqs = $value['procurement_ref_no'];
						 ?>
						<a href="<?= base_url() . 'bids/publish_bidder/publish_bidder/'.$q[1].'/'.base64_encode($pqs); ?>"> Evaluate </a>
						
						</td>
						<td>
							<?=$value['procurement_ref_no']; ?>
						</td>
						<td>
							<?php 
							//$value['subject_of_procurement']; ?>							
							 
						</td>
						<td>
							<?php
							$zx = $q[1];
							if($zx > 0)
							{
								$ss = $q[1];
							}else
							{
								$ss = 0;
							}
							?>
							<a href="<?= base_url() . 'bids/publish_bidder/view_bidders_list/'.$ss.'/'.base64_encode($value['procurement_ref_no']); ?>" publish_bidder>
							 <?php
							
							
							echo($q[0]);

							 ?>
							 </a>
						</td>
						 
						 
						 
					</tr>
				<?php
			}
			?>
			
				

				</tbody>
			</table>
		</div>
	</div>
		<?php
			break;
			case 'view_bidders_list':
			?>
	<div class="span12">

<table class="table table-bordered">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							Procurement Ref # 
						</th>
						<th>
							Service Provider
						</th>
						<th>
							Details
						</th>
						<th>
							Date Submitted
						</th>
						 
						<th>
							Recieved By
						</th>
						<th> Author </th>
						<th> Date Added </th>


					</tr>
				</thead>
				<tbody>
			<?php
			 
			foreach ($bidderslist as $key => $value) {
				# code...
				?>
				<tr  >
						<td>
							<?php  $q = fetchnum_bidders($page_title); ?>
							<a href="<?= base_url() . 'bids/publish_bidder/publish_bidder/'.$q[1].'/'.base64_encode($page_title); ?>"> Evaluate </a>
						
						</td>
						<td>
							<?=$value['procurement_ref_no']; ?>
						</td>
						<td>
							&nbsp;&nbsp; &nbsp;&nbsp;-
						</td>
						<td>
							 &nbsp;&nbsp; &nbsp;&nbsp;-
						</td>
						<td>
							<?=$value['datereceived']; ?>
						</td>
						 
						<td>
							 <?=$value['received_by']; ?>
						</td>
						<td>
							-
						</td>
						<td>
							
							 <?=$value['dateadded']; ?>
							 
						</td>
					</tr>
				<?php
			}
			?>
			
				

				</tbody>
			</table>
		</div>
			<?php
			break;
			default:
			break;
	}
	?>
		
</div>
<br/>
<br/>

</div> 
</div>


 