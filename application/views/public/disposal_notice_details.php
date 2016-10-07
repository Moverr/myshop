<?php  
  #print_array($bid_inviation); exit;
//print_array($page_list['page_list']);
  if((!empty($page_list['page_list'])) && (count($page_list['page_list']) > 0) )
    {
       $ref_no =  '';
       foreach ($page_list['page_list'] as $key => $row)
     {
       # code...
			$disposalserialno = $row['disposal_serial_no'];
			$biddocumentissuedate = custom_date_format('Y-m-d',$row['bid_document_issue_date']);
			$display_of_beb_notice = custom_date_format('Y-m-d',$row['display_of_beb_notice']);
			$bid_opening_date =     custom_date_format('Y-m-d',$row['bid_opening_date']);
			$subject_of_disposal = $row['subject_of_disposal'];
			$date_of_approval_form28 = custom_date_format('Y-m-d',$row['date_of_approval_form28']);
			$date_of_initiation_form28 = custom_date_format('Y-m-d',$row['date_of_initiation_form28']);
			$cc_approval_date =   custom_date_format('Y-m-d',$row['cc_approval_date']);
			$disposal_ref_no = $row['disposal_ref_no'];
			$asset_location = $row['asset_location'];
			$bid_opening_time = $row['bid_openning_date_time'];
			$evaluation_from = $row['bid_evaluation_from'];
			$evaluation_to = $row['bid_evaluation_to'];
            $inspect_date = custom_date_format('Y-m-d',$row['inspect_openning_date']);
			$inspect_time = $row['inspect_openning_date_time'];
			$inspect_cdate = custom_date_format('Y-m-d',$row['inspect_close_date']);
			$inspect_ctime = $row['inspect_close_date_time'];
			$bidenddate =  custom_date_format('Y-m-d',(strtotime(custom_date_format('Y-m-d',$row['bid_opening_date']).' + '.$row['bid_duration'].' days' )));
            $dateadded=custom_date_format('Y-m-d',$row['dateadded']);
            $bid_opening_address=$row['bid_opening_address'];
            $doc_inspection_address=$row['documents_inspection_address'];
            
            $documents_address_issue = $row['documents_address_issue'];
            $documents_address_delivered_to = $row['documents_address_delivered_to'];
            $disposal_method   = $row['disposal_method'];
            
                        
            $deadline_for_submition = custom_date_format('Y-m-d',$row['deadline_for_submition']);

			//get days
			$date1 = strtotime($evaluation_from);
			$date2 = strtotime($evaluation_to);
			
			$datediff = $date2 - $date1;
			$newdays = floor($datediff/(60*60*24));
			$bidclose_date = date('Y-m-d', strtotime($bid_opening_date.' + '.$newdays.' days'));
	
			$contract_award_date = $row['contract_award_date'];
	
			$disposal_id = $row['id'] ;
           $pdename=$row['pdename'];
           $fee=$row['non_refundable_fee'];
      }
  }
?>


<div class="container">
    <div class="row">
		
         <div class="col-md-8 col-md-offset-2">
            <div class="card">
				
                <div class="row-fluid">

                    <div id="print_this"  class="current_tenders printarea" >
                        <div class="column">
                            <div class="widget-body">
  <div id="doc-wrapper">
                                    <div id="doc-content" style="font-size:14px">
                                        <h3 style="text-align: center" class="text-align-center">BID NOTICE UNDER <?=strtoupper($disposal_method); ?></h3>
                                        <div class="row-fluid">
                                            <span >
                                                <?=$dateadded?>
                                            </span>
                                            <h4>Invitation to bid for disposal of assets - [<?=$disposal_ref_no?>]</h4>
                                        </div>

                                        <div class="row-fluid">
                                            <div class="span12">
                                                <ol>
                                                    <li>
                                                      The <?=$pdename?> Intends to dispose of the following assets <?=ucfirst($subject_of_disposal)?>. The asset(s) are  sold on an <b>"as is, where is"</b> basis and the Entity  will have no further liability after sale.</p>
                                                    </li>

                                                    <li>
                                                        The Entity invites sealed bids for the purchase of the above assets
                             	                       </li>

                                                    <li>
                                                       Bidding will be conducted in accordance with the Public Bidding Disposal method contained in the Public Procurement and Disposal of Public Assets Act, 2003 and is open to all bidders.
                                                    </li>

                                                    <li>
                                                       Interested bidders may inspect the  asset(s) at the location indicated in 7(a) on <?= custom_date_format('d M, Y',$inspect_date); ?>  from <?=custom_date_format('H:i',$inspect_time); ?>  
                                                  </li>

                                                    <li>
                                                       The bidding documents  may be inspected and purchased by interested bidders on the submission of a written application to the address indicated in 7(b)  and upon payment of a non-refundable fee of  UGX <?=number_format($fee);?>.
                                                    </li>

                                                    <li>
                                                       Bids must be delivered to  the address indicated in 7(c)  at or before <?=custom_date_format('d M,Y',$deadline_for_submition); ?>. Late bids shall be rejected. Bids will be opened in the presence of the bidders or the representatives of the bidders who choose to attend at the address indicated in 7(d) at <?=custom_date_format('d M,Y',$bid_opening_date) ; ?>.
                                                    </li>

                                                    <li>
                                                        <ol  type="A" style="font-weight: bold;">
                                                            <li>The assets may be inspected at:  <span ><?php echo $doc_inspection_address;?> </span></li>
                                                            <li>Documents will be issued from:   <b> <?=$documents_address_issue; ?> </b></li>
                                                            <li>Bids must be delivered to: <?=$documents_address_delivered_to; ?> </li>
                                                            <li>Address of bid opening: <?php echo $bid_opening_address;?></li>
                                                        </ol>
                                                    </li>
                                                </ol>
                                                <h4>
                                                    The planned disposal schedule (subject to changes) is as follows:

                                                </h4>
                                                <table cellpadding="4" class="table table-stripped" border="1">
                                                    <thead>
                                                    <tr>
                                                        <th width="12"></th>
                                                        <th width="329">Activity</th>
                                                        <th width="167">Date</th>
                                                    </tr>
                                                    </thead>
                                                    <tr>
                                                        <td>a.</td>
                                                        <td>Publish bid notice</td>
                                                        <td><?=custom_date_format('d M,Y',$biddocumentissuedate);?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>b.</td>
                                                        <td>Inspection of Assets </td>
                                                        <td>
															<p>
																<b> Date : </b>  &nbsp; <?=custom_date_format('d M,Y',$inspect_date);?><br/>
																<b>Address : </b> <?=$doc_inspection_address ?>
															</p>
															
															</td>
                                                    </tr>
                                                    <tr>
                                                        <td>c.</td>
                                                        <td>Bid Closing Date </td>
                                                        <td><?=custom_date_format('d M,Y',$deadline_for_submition); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>d.</td>
                                                        <td>Evaluation process</td>
                                                        <td><?='From ' .$evaluation_from.' to '.$evaluation_to?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>e.</td>
                                                        <td>Display and communication of best evaluated bidder notice</td>
                                                        <td><span style="font-weight:bold"><?=custom_date_format('d M,Y',$display_of_beb_notice); ?></span></td>
                                                    </tr>
                                                    
                                                      <tr>
                                                        <td>f.</td>
                                                        <td>Contract Award and Signature </td>
                                                        <td><span style="font-weight:bold"><?=custom_date_format('d M,Y',$contract_award_date); ?></span></td>
                                                    </tr>
                                                    
                                                </table>
                                            </div>
                                        </div>
                                        <br/>
                                        <!-- Authorization-->
                                         <div class="row-fluid">
											    <div class="span12">
													<br/>
													Signature :___________________________________________________________________________________________<br/>
													<br/>Name : ______________________________________________________________________________________________ <br/>
													<br/>Position of Authorized Official : ____________________________________________________________________
												 </div>
										</div>
										
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <a href="<?=base_url().'disposal/view_bid_invitations'; ?>" class="btn btn-default bet"> Return to Disposal Notices</a>
                    <a class="btn" href="#" onclick="printContent('print_this')"> PRINT </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


    function printContent(el){
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

</script>
