<div class="widget-body print_area" >

    <div class="space20"></div>
  <div class="row-fluid invoice-list " >
        <div class="span4">
            <h5><?=strtoupper($details['subject_of_procurement'])?></h5>
            <p>
                <b><a href=""><?php //$details['procurement_plan']?></a> </b>
            </p>
            <p>
                <strong>Reference Number:</strong> <?=$details['procurement_ref_no']?><br>
                <strong>Subject of Procurement:</strong> <?=$details['subject_of_procurement']?><br>
                <strong>Procuring Entity:</strong> <?=$details['pdename']?><br>
            </p>

        </div>


    </div>
    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Contract Details</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                        <tr><th width="150px"></th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Service Provider<b/></td>
                            <td style="text-align:left">
                                <span class=""><?php
                                    if(!empty($details['joint_venture'])):
                        $providername = '';
                        $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $details['joint_venture'] .'"')->result_array();
                        
                        if(!empty($jv_info[0]['providers'])):
                            $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();                          
                            foreach($providers as $provider):
                                $providername .= (!empty($providername)? ', ' : ''). $provider['providernames'];
                            endforeach;
                                                
                        endif;
                        
                        print $providername;
                        
                    else:
                        print $details['providernames'];
                    endif;
                                ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Date Signed<b/></td>
                            <td style="text-align:left">
                                <span class=""><?=custom_date_format('d M, Y', $details['date_signed'])?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Commencement Date</b></td>
                            <td style="text-align:left">
                                <span class=""><?=custom_date_format('d M, Y', $details['commencement_date'])?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Planned date of completion</b></td>
                            <td style="text-align:left">
                                <span class=""><?=custom_date_format('d M, Y', $details['completion_date'])?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Contract Amount</b></td>
                            <td style="text-align:left">
                                <span class=""><?=addCommas($details['total_price'], 0) . ' UGX'?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
      </div>

  </div>

  <?php
         $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";

            print  ''.
                    '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.$url.'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.$url.'"></div>&nbsp;<div class="fb-share-button" data-href="'.$url.'" data-layout="button" data-size="medium"></div>'
         
?>

</div>