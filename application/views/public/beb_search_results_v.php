<?=$this->load->view('public/includes/header')?>

<?php
#print_r($page_list);
?>
<style>
.pagination ul{}
.pagination ul li{ list-style: none;   float: left;}
.pagination ul li a{text-decoration: none; padding:6px;display: block;border:2px solid #eee;}
.bet{background: none repeat scroll 0 0 black;border: medium none;border-radius: 2px;
color: white;display: inline-block;font-size: 14px;padding: 6px 10px;transition: all 0.2s ease-out 0s;
cursor: pointer;line-height: normal;}
</style>
<style type="text/css">
    .searchengine {margin:0; background:#efefef;border:1px solid #e1e1e1; }
    .searchengine > .searchengine-header {}
    .searchengine  > .content {display: none;}
</style>


    <div class="clearfix content-wrapper" style="padding-top:28px;">
        <div class="col-md-12" style="margin:0 auto">
            <div class="clearfix">
                <div class="col-md-13 column content-area">
                    <div class="row">
                        <div class="">
                            <div class="col-md-4 pull-left">
                                <h2 class="">Best Evaluated Bidders </h2>
                            </div>

                            <div class="col-md-4 pull-right">
                                <form method="GET" action="<?=base_url().$this->uri->segment(1).'/search_bebs'?>" accept-charset="UTF-8" class="form-horizontal ng-pristine ng-valid">
                                    <div class="box-tools">
                                        <div class="input-group input-group-sm " >
                                            <input placeholder="Search for best evaluated bidders" id="term" class="form-control" name="term" type="text" value="">
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-info btn-flat">Go!</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>


                        </div>
                    </div>

<?=$this->load->view('public/parts/model_v')?>
<div class="searchstatus">
</div>
<div class="row clearfix current_tenders search_results">

<?php
if(!empty($level) && ($level == 'search'))
{
 $this->load->view('public/searchbeb_v');
}
else
{
?>
    <div class="column ">
  <a class="pull-right  btn btn-sm btn-danger"
       href="<?= base_url() . 'export/best_evaluated_bidder' ?>">Export This Page</a>
        <?php
            if(!empty($page_list))
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
 $stack = array( );

foreach($page_list as $row)
{
    # only active
    if(strtotime($row['beb_expiry_date'])>NOW()){
        $haslots = $row['haslots'];
        $bidd = $row['bid_id'];
        $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON receipts.receiptid = received_lots.receiptid  INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid INNER JOIN bidinvitations ON  bidinvitations.id = lots.bid_id  WHERE lots.bid_id = ".$row['bid_id']."   AND    bidinvitations.haslots ='Y' AND receipts.beb='Y'  AND bestevaluatedbidder.ispublished='Y'   ")->result_array();

        if (in_array(  $bidd, $stack))
            continue;
        if($haslots == 'Y' && ( $lot_count[0]['nums'] > 0 ) )
            array_push( $stack, $bidd);
        #print_r($row);


        print '<div class="row col-md-13">'.
            '<div class="col-md-1">'. custom_date_format('d M, Y', $row['display_of_beb_notice']) .'</div>'.
            '<div class="col-md-2 procurement_pde"> ';

        print $row['pdename'];

        print ' </div>'.
            '<div class="col-md-2">'. $row['procurement_ref_no']. '</div>'.
            '<div class="col-md-2 procurement_pde">';

        if($haslots == 'N')
        {
            # is not a joint venture
            if($row['joint_venture']==''){
                echo get_provider_info_by_id($row['providers'],'title');


            }else{
                ?>
                <h6>Joint Venture</h6>


                <?php
                foreach(csv_to_array(($row['providers'])) as $provider){

                    echo '<p>'.get_provider_info_by_id($provider,'title').'</p>';


                }
                ?>

                <?php

            }
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
                    #  print "<span class='label label-info'".$row['review_level']."</span>";
                    //class="label label-info"
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
            //  echo "<li> Exchange Rate :  ".number_format($row['exchange_rate'])."</li>";
            echo "<li> Currency :   ".$row['currency']."</li></ul>";



            /*
                                              // this picks readout price at level 2
                                          $readout = mysql_query("SELECT * FROM readoutprices WHERE receiptid=".$row['receiptid']."");

                                        if(mysql_num_rows($readout) > 0 )
                                        {
                                          echo "<ul>";
                                          while ( $valsue = mysql_fetch_array($readout)) {
                                            if($valsue['readoutprice']<=0)
                                              continue;
                                            # code...
                                             echo "<li>".number_format($valsue['readoutprice']).$valsue['currence']."</li>";
                                          }
                                          echo "</ul>";
                                        }    */
        }


        echo '</div>'.

            '</div>'.
            '<hr>';

    }

    }

                    print '<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().
                        "page/best_evaluated_bidder/p/%d")
                        .'</div>';

             //   <a id="modal-703202" href="#modal-container-703202" role="button" class="btn" data-toggle="modal">Launch demo modal</a>
            }
            else
            {
                print format_notice("ERROR: There are no verified bids");
            }

            //working upon export


        ?>
    </div>

    <?php
    } ?>

</div>
                </div>

        </div>
        <?=$this->load->view('public/includes/footer')?>
    </div>
    </div>

    </body>
</html>
