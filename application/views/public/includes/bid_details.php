<?php  $procurement_details = $formdata['procurement_details']; ?>
 <script type="text/javascript">
   $(function(){
	   
    //print functionality in the header
    
        //alert('Ready to Print');
       $('.print').click(function(){
        //alert('Ready to Print');
       w = '800px';
       $(".printarea").css("width", w);
      
     

    //function printDiv(divName) {
     var printContents =  $(".printarea").html();
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
 
       
         });

})
    </script>
	
<div class="page-header col-lg-offset-2" style="margin:0">
    <h3>Tender Details</h3>
     <a href="<?=base_url().'page/home'; ?>" class="btn btn-default bet"> Return to Current Tenders</a>
    <a class=" btn btn-default print"   style='cursor:pointer;'><i class="fa fa-print  fa-1x">Print</i></a>
</div>
<div class="row clearfix current_tenders printarea" style="width:100%;">
    <div class="column">
        <div class="widget-body" style="   ">            
        
        <div id="doc-wrapper">
            <div class="row-fluid" style="text-align:center; font-weight:700; font-size:14px">
                <div class="col-md-13">
                  <?=strtoupper('BID NOTICE UNDER ' . ((!empty($formdata['procurement_method']) || ( $formdata['procurement_method'] != null ) ) ? $formdata['procurement_method'] : $procurement_details['procurement_method']))?>
          
                   
                </div>
            </div>
            
            <div id="doc-content" style="font-size:14px">
                <div class="row-fluid">
                    <div class="col-md-13"><?=(!empty($procurement_details['pdename'])) ? $procurement_details['pdename']  : ''; ?></div>
                </div>
                
                <div class="row-fluid">
                    <div class="col-md-13"><?=custom_date_format('d M, Y', $formdata['invitation_to_bid_date'])?></div>
                </div>
                
                <div class="row-fluid">
                    <div class="col-md-13">
                    <?php
                    if(!empty($procurement_details['subject_of_procurement'])){
                    echo  $procurement_details['subject_of_procurement'];
                    }
                    
                    if(!empty($procurement_details['procurement_ref_no'])){
                    echo  ' - ' .$procurement_details['procurement_ref_no'];
                    }
                    
                    
                    ?>
                        
                    </div>
                </div>
                
                <div class="row-fluid">
                    <div class="col-md-12">
                        <ul class="ifb-doc-list">
                            <li><?=(!empty($procurement_details['pdename'])) ? $procurement_details['pdename']  : ''; ?> 
                            has <?php
                            if(!empty($procurement_details['funding_source'])){
                                if( $procurement_details['funding_source'] == 1){
                                    print 'allocated funds';
                                }
                                else{
                                    print 'received funds ';
                                }
                            }

                      print  (empty($procurement_details['funder_name']) ) ? '' : ' from ' .$procurement_details['funder_name']; ?>
                       to be used for the acquisition of <?=(!empty($procurement_details['subject_of_procurement'])) ? $procurement_details['subject_of_procurement']  : ''; ?>

                            <!-- Add Lots if Applicable -->
                            <?php
                            #fetch lots if avalilable
                          # print_r($procurement_details);
                            if(!empty($procurement_details['haslots']) && $procurement_details['haslots'] == 'Y')
                            {
                                $lots = $this->db->query("SELECT * FROM lots WHERE lots.bid_id = ".$procurement_details['bidinvitationid']) ->result_array();
                              
                              #print_r($lots);
                              if(!empty($lots))
                              {
                                ?>
                                <h4>LOTS DETAILS </h4>
                                 <table cellpadding="4" class="table table-stripped" border="1">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>LOT NUMBER</th>
                                            <th>LOT TITLE</th>
                                            <th>LOT DETAILS</th>
											<th>BID SECURITY</th>
                                        </tr>
                                    </thead>
                                   
                                    <?php
                                    {
                                       foreach ($lots as $key => $record) {
                                       
                                        ?>
                                    <tr>
                                        <td></td>
                                        <td><?=$record['lot_number'] ?></td>
                                        <td><?=$record['lot_title'] ?></td>
                                         <td><?=$record['lot_details'] ?></td>
										 <td><?=number_format($record['bid_security_amount'])."".get_currency_info_by_id($record['bid_security_currency'],'title'); ?></td>
                             
                                    </tr>
                                        <?php
                                             # code...
                                        }
                                    }
                                    ?>
                                      
                                  
                                    
                                </table>
                                <?php
                              }
                                
                            }
                            ?>

                            <!-- end of lots -->
                            </li>
                            
                            <li>The Entity invites sealed bids from eligible bidders for the provision of the above supplies</li>
                            
                            <li>Bidding will be conducted in accordance with the open <?= ((!empty($formdata['procurement_method']) || ( $formdata['procurement_method'] != null ) ) ? $formdata['procurement_method'] : $procurement_details['procurement_method']) ?> method contained in the Public Procurement and Disposal of Public Assets Act, 2003, and is open to all bidders.</li>
                            
                            <li>Interested eligible bidders may obtain further information and inspect the bidding documents at the address given below at 8(a) from  
							<?php if(!empty($formdata['start_hours']) && !empty($formdata['end_hours'])){ 
							      echo date('H:m',strtotime($formdata['start_hours']))." - ".date('H:m',strtotime($formdata['start_hours']));
							}
							else
								echo "8AM - 6PM";
							?>.</li>
                            
                            <li>The Bidding documents in English may be purchased by interested bidders on the submission of a written application to the address below at 8(b) and upon payment of a non-refundable fee of <?=add_commas($formdata['bid_documents_price'], 0) . ' ' .strtoupper(get_currency_info_by_id($formdata['bid_documents_currency'],'title')); ?>. The method of payment will be <?=!empty($formdata['payment_method']) ? $formdata['payment_method']  :'N/A' ; ?> </li>
                            
                            <li>Bids must be delivered to the address below at 8(c) at or before <?=custom_date_format('l, d M, Y ', $formdata['bid_submission_deadline'])?>. 
                            
                            <?php if(!empty($formdata['bid_security_amount'])): ?>
                            All bids must be accompanied by a bid security of <?=((is_numeric($formdata['bid_security_amount']))? add_commas($formdata['bid_security_amount'], 0) : $formdata['bid_security_amount']) . ' ' .strtoupper(get_currency_info_by_id($formdata['bid_security_currency'],'title')); ?> or a bid securing declaration. Bid securities or bid securing declarations must be valid until (insert day, month and year). 
                            <?php endif; ?>
                            Late bids shall be rejected. Bids will be opened in the presence of the bidders' representatives who choose to attend at the address below at 8(d) on <?=custom_date_format('l, d M, Y', $formdata['bid_openning_date']) . '   '.display_time('h:i A', trim(substr($formdata['bid_openning_date'], 10, 10)))  ?></li>
                            
                            <li>
                            <?php if(!empty($formdata['pre_bid_meeting_date'])): ?>
							<?php
							$data_y = custom_date_format('l, d M, Y', $formdata['pre_bid_meeting_date']);
							?>
                            There shall be a pre - bid meeting on <?= !empty($data_y) ? custom_date_format('l, d M, Y', $formdata['pre_bid_meeting_date']) .'   '.display_time('h:i A', trim(substr($formdata['pre_bid_meeting_date'], 10, 10)))  : ''; ?>
                             
                            <?php else: ?>
                            There shall not be a pre - bid meeting
                            <?php endif; ?>
                            </li>
                            
                            <li>
                                <table>
                                    <tr>
                                        <td>(a)</td>
                                        <td align="left">Documents may be inspected at:</td>
                                        <td><?=$formdata['documents_inspection_address']?></td>
                                    </tr>
                                    <tr>
                                        <td>(b)</td>
                                        <td align="left">Documents will be issued from:</td>
                                        <td><?=$formdata['documents_address_issue']?></td>
                                    </tr>
                                    <tr>
                                        <td>(c)</td>
                                        <td align="left">Bids must be delivered to:</td>
                                        <td><?=$formdata['bid_receipt_address']?></td>
                                    </tr>
                                    <tr>
                                        <td>(d)</td>
                                        <td align="left">Address of bid openning:</td>
                                        <td><?=$formdata['bid_openning_address']?></td>
                                    </tr>
                                </table>
                            </li>
                            
                            <li>
                                <div>The planned procurement schedule (subject to changes) is as follows:</div>
                                <table cellpadding="4"   class="table table-stripped" border="1">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Activity</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <td>a.</td>
                                        <td>Publish bid notice</td>
                                        <td>
                                            <?=custom_date_format('l, d M, Y', $formdata['invitation_to_bid_date'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>b.</td>
                                        <td>Pre-bid meeting where applicable</td>
                                        <td><?=custom_date_format('l, d M, Y ', $formdata['pre_bid_meeting_date'])?></td>
                                    </tr>
                                    <tr>
                                        <td>c.</td>
                                        <td>Bid closing date </td>
                                        <td><?=custom_date_format('l, d M, Y  ', $formdata['bid_submission_deadline'])?></td>
                                    </tr>
                                    <tr>
                                        <td>d.</td>
                                        <td>Evaluation process</td>
                                        <td><?='From ' . custom_date_format('l, d M, Y', $formdata['bid_evaluation_from']) . ' to '. custom_date_format('l, d M, Y', $formdata['bid_evaluation_to'])?></td>
                                    </tr>
                                    <tr>
                                        <td>e.</td>
                                        <td>Display and communication of best evaluated bidder notice</td>
                                        <td><?=custom_date_format('l, d M, Y', $formdata['display_of_beb_notice'])?></td>
                                    </tr>
                                    <tr>
                                        <td>f.</td>
                                        <td>Contract award and signature</td>
                                        <td><?=custom_date_format('l, d M, Y', $formdata['contract_award_date'])?></td>
                                    </tr>
                                </table>
                            </li>
                          
     <li>Additional Notes :
                                <br/>
                                <?=$formdata['additional_notes']; ?>

                                <?php
                                #print_r($formdata);
                                ?>
                                </li>
                        </ul>
                    </div>
                </div>
            </div>
         </div>        
    </div>
    <a href="<?=base_url().'page/home'; ?>" class="btn btn-default bet"> Return to Current Tenders</a>
    <?php
         $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";

            print  ''.
                    '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.$url.'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.$url.'"></div>&nbsp;<div class="fb-share-button" data-href="'.$url.'" data-layout="button" data-size="medium"></div>'
         
         ?>
    </div>
</div>