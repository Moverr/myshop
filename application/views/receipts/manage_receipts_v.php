<br/>
<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>Received Bids </h4>
          <span class="tools">
                <a href="javascript:void(0);" class="icon-chevron-down"></a>
                <a href="javascript:void(0);" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body">


    <!-- start -->
<div class="row-fluid">
		<div class="span12">
		<div id="results" >

		 <div class="tabbable" id="tabs-358950">
 <?php
 #print_r($receiptinfo);
 ?>

				<div class="tab-content">
				<div class="tab-pane active dvq" id="panel-active">
        <?php
				if((!empty($feed)) && $feed == 'receipt' )
				{
				?>
      <table width="100%" class="table  table-striped">
			<thead>
			<tr>

						<th width="17">
						#
						</th>
            <th width="17">
            <em class="glyphicon glyphicon-user"></em>
            </th>
            <th width="10">
            Lot
            </th>
			<th width="30">
						Service Provider
						</th>
            <th colspan="3" width="30">
						Readout Price
						</th>
            <th width="30">
            Country of Registration
            </th>
						<th width="82">
						Date Submitted
						</th>
				 </tr>

         <tr>
						<th>&nbsp;</th>
						<th>
						<em class="glyphicon glyphicon-user"></em>
						</th>
						<th>&nbsp;</th>
            <th>&nbsp;</th>
            <th width="35"   style="text-align:left;  ">Amount</th>
            <th width="35"  style="text-align:center; ">Exchange Rate</th>
            <th width="30"  style="text-align:center;  ">Currency</th>
						<th width="88">&nbsp;</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
                <?php
			#	print_r($receiptinfo_jv);

				?>




  <?php
  $numcount = 0;
  #print_r($receiptinfo); exit();
  foreach ($receiptinfo['page_list'] as $key => $value) {
	# code...
	#print_r($value);
	$provider_lead = $value['provider_lead'];
	 $numcount  ++;
	 ?>
	 <tr>
   <td>
	 <?=$numcount; ?>
	 </td>
	 <td>
   <a href="javascript:void(0);" id="savedelreceipt_<?=$value['receiptid'];?>" class="savedelreceipt">
   <i class="fa fa-trash"></i></a>
   </td>
	 <td>
     <?php
     $query = $this->db->query("select * from lots inner join received_lots on lots.id = received_lots.lotid where received_lots.receiptid=".$value['receiptid']." limit 1 ") ->result_array();
    if(!empty($query))
    {
     print_r($query[0]['lot_title']);
    }
    else
    {
      echo " - ";
    }
        ?>
   </td>
   <td>
   <?php
  	
		$providers  = rtrim($value['providers'],",");


		  	 
   #print_r($providers);
   #echo "<br/>";
	 	$row = mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("ERROR ONE ".mysql_error());
		//print_r($row);

	  $provider = "";
      $provider_array = explode(",",$providers);
      $lead = '';
      $joint_venture = '';
		while($record = mysql_fetch_array($row))
		{
			 
			 
			$x = 0;
			$leader = 0;

			  if(trim($record['providerid'])  == ''.trim($provider_lead))
			   {	 
			           $lead = '&nbsp; <span class="label" title="Project Lead " style="cursor:pointer;background:#fff;color:orange; padding-right:0px; font-size:35px;border-radius:50px; " >&#42;</span>';
				       $provider  .= $lead.$record['providernames'].', ';	
			    }			     
			    else
			    {
			    	 $provider  .=  $record['providernames'].', ';
			    }	 
			
			
			 foreach ($provider_array as $result) {
			 	$x ++;
				  
			 	 
					if($x > 1)
					 $joint_venture = '&nbsp; <span class="label label-info">Joint Venture</span>';
			 
			    }


			      
			
			
		    
		}
		print_r($provider.''.$joint_venture);
	
					
		?>

						</td>
						<td colspan="3">

                         <?php

						 $rest = mysql_query("select * from readoutprices where receiptid = ".$value['receiptid']);

						 if(mysql_num_rows($rest) > 0)
						 {
							 ?>
                               <table width="100%">
                             <?php
						 while($row = mysql_fetch_array($rest))
						 {
							 if($row['readoutprice'] == 0)
							 continue;
							 ?>
                         <tr>
                        <td style="text-align:center; width:30%; text-align:left;"><?=number_format($row['readoutprice']); ?></td>
                         <td style="text-align:center; width:30%; text-align:left;"><?=number_format($row['exchangerate']); ?></td>
                        <td style="text-align:center; width:30%;"><?=$row['currence']; ?></td>
                        </tr>
                             <?php
						 }
						 ?>

                        </table>
                         <?php
						 }
						 ?>




                        <?php

						?>
					   </td>
                      <td>
                        <?=$value['nationality']; ?>
                      </td>
						<td>
							 <?=date('Y-M-d',strtotime($value['datereceived'])); ?>
						</td>

					</tr>


	<?php
}


					?>




				</tbody>
			</table>
                    <?php
				}

				?>
			


<!-- add pagination -->
<?php print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $receiptinfo['rows_per_page'], $receiptinfo['current_list_page'], base_url()."receipts/manage_receipts/p/%d").'</div>';

						?>
<!-- end pagination -->
					 <!-- End -->
				  </div>

		    </div>
			</div>




		</div>
		</div>
	</div>


		</div>

</div>

     </div>