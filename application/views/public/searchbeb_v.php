
     <a class="pull-right  btn btn-sm btn-danger"
                       href="<?= base_url() . 'page/best_evaluated_bidder/level/export' ?>">Export This Page</a>
        <?php
      
 
            if(!empty($page_list['page_list']))
            {
                   print '<div class="row titles_h">

                            <div class="col-md-1">
                                <b>Date Posted</b>
                            </div>
                              <div class="col-md-2">
                                <b>Procuring/Disposing Entity</b>
                            </div>
                               <div class="col-md-2">
                                <b>Procurement Reference Number </b>
                            </div>
                             <div class="col-md-2">
                                <b>Selected Provider</b>
                            </div>
                              <div class="col-md-1">
                                <b>Subject </b>
                            </div> 


                             <div class="col-md-1">
                                <b>Date  BEB Expires</b>
                            </div>
                            <div class="col-md-1">
                                <b>Status</b>
                            </div>



                             <div class="col-md-2">
                                <b>BEB Price </b>
                            </div>

                        </div><hr>';
                        
                        
                                foreach($page_list['page_list'] as $row)
                                {
                                    $haslots = $row['haslots'];
                                    $bidd = $row['bidid'];
                                    $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON receipts.receiptid = received_lots.receiptid  INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid INNER JOIN bidinvitations ON  bidinvitations.id = lots.bid_id  WHERE lots.bid_id = ".$row['bidid']."   AND    bidinvitations.haslots ='Y' AND receipts.beb='Y'  AND bestevaluatedbidder.ispublished='Y'   ")->result_array();

                                    if (in_array(  $bidd, $stack))
                                        continue;
                                    if($haslots == 'Y' && ( $lot_count[0]['nums'] > 0 ) )
                                        array_push( $stack, $bidd);
                                   

                                    print '<div class="row col-md-13">'.
                                        '<div class="col-md-1">'. custom_date_format('d M, Y', $row['date_of_display']) .'</div>'.
                                        '<div class="col-md-2 procurement_pde"> ';

                                    print $row['pdename'];

                                    print ' </div>'.
                                        '<div class="col-md-2">'. $row['procurement_ref_no']. '</div>'.
                                        '<div class="col-md-2 procurement_pde">';

                                    if($haslots == 'N')
                                    {


                                        if(((strpos($row['providernames'] ,",")!== false)) || (preg_match('/[0-9]+/', $row['providernames'] )))
                                        {

                                            $label = '';
                                            $providers  = rtrim($row['providernames'],",");
                                            $rows= mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("".mysql_error());
                                            $provider = "";
                                            $x = 0;
                                            $xl = 0;

                                            while($vaue = mysql_fetch_array($rows))
                                            {
                                                $x ++;
                                                if(mysql_num_rows($rows) > 1)
                                                {
                                                    $lead = '';
                                                    
                                                    if ($row['providerlead'] ==   $vaue['providerid']) {
                                                        $lead = '&nbsp; <span class="label" title="Project Lead " style="cursor:pointer;background:#fff;color:orange;padding:0px;margin:0px; margin-left:-15px; font-size:18px; " >&#42;</span>';
                                                      
                                                    }
                                                    else{
                                                        $lead = '';

                                                    }

                                                    $provider  .= "<li>";
                                                    $provider  .=   strpos($vaue['providernames'] ,"0") !== false ? '' :  $lead.$vaue['providernames'];
                                                    $provider  .= "</li>";

                                                }else{
                                                    $provider  .=strpos($vaue['providernames'] ,"0") !== false ? '' : $vaue['providernames'];
                                                }
                                            }

                                            if(mysql_num_rows($rows) > 1){
                                                $provider .= "</ul>";}
                                            else{
                                                $provider = rtrim($provider,' ,');
                                            }

                                            if($x > 1)
                                                $label = '<span class="label label-info">Joint Venture</span>';
                                            print_r($provider.'&nbsp; '.$label );
                                            $x  = 0 ;
                                            $label = '';
                                        }
                                        else{  echo $row['providernames'];}


                                    }
                                    else
                                    {

                                        print '<a id="modal-703202" href="#modal-container-703202" data-value="'.$row['bidid'].'" data-levels="public"  data-ref="'.base_url().'page/get_beb_lots"  role="button" class="view_lots" data-toggle="modal" >  Awarded Lots </a><span class="badge">'.$lot_count[0]['nums'].'</span>';
                                    }

                                    print '</div>'.
                                        '<div class="col-md-1 procurement_subject"> '.$row['subject_of_procurement'].' <br/> '.
                                        '<a id="modal-703202"  data-lot="'.$haslots.'" href="#modal-container-703202" data-bid="'.$row['bidid'].'" data-value="'.$row['receiptid'].'" data-framework="'.$row['framework'].'"  data-ref="'.base_url().'page/beb_notice"  role="button" class="btn  btn-xs btn-primary beb" data-toggle="modal">
                             View  details</a></div>'.
                                        '<div class="col-md-1"><strong>';

                                    if($haslots == 'Y' ){
                                        echo "-";
                                    }
                                    else{
                                        echo date("d M, Y",strtotime($row['beb_expiry_date']));
                                    }
                                    echo '</strong></div>'.
                                        '<div class="col-md-1" style="padding:5px;" >' ;
                                    if($haslots == 'Y' ){
                                        echo "-";
                                    }
                                    else{
                                        switch($row['isreviewed'])
                                        {

                                            case 'Y':
                                                print (" <span class='label label-info '> For Admin Review </span>  <br/> <span class='label label-success'>".$row['review_level']." </span> <br/>");
                                               
                                                break;


                                            case 'N':
                                                print (" <span class='btn btn-xs btn-success'> Active </span>");

                                                break;


                                            default:
                                                print("-");
                                                break;
                                        }
                                    }

                                    print '</div>'.



                                        '<div class="col-md-2" style="pading:5px; font-family:Georgia; font-size:13px;">';
                                    if($haslots == 'Y' ){
                                        echo "-";
                                    }
                                    else{

                                        echo "<ul>";
                                        echo "<li> Contract Price :  ".number_format($row['contractprice'])."</li>";                                        
                                        echo "<li> Currency :   ".$row['currency']."</li></ul>";



                                    }


                                    echo '</div>'.

                                        '</div>'.
                                        '<hr>';
                                }

                            
                    print '<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().
                        "page/best_evaluated_bidder/p/%d/level/search")
                        .'</div>';
                           }
            else
            {
                print format_notice("ERROR: There are no verified bids");
            }
           
        ?>
   
    
