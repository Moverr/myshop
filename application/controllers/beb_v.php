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

<script>
    $(function(){

        $(".view_lots").click(function(){

            formdata = {};
            //url
            var idd = this.id;
            var url = $(this).data("ref");
            var datavalue = $(this).data("value");
            var datalevel = $(this).data("levels");

            formdata['bidid'] = datavalue;
            formdata['side'] = datalevel;

            console.log(formdata);
            $(".modal-body").html("proccessing ...");

            $.ajax({

                type: "POST",
                url:  url,
                data: formdata,
                success: function(data, textStatus, jqXHR){
                    $(".modal-body").html(data);

                },
                error:function(data , textStatus, jqXHR)
                {
                    console.log('Data Error'+data+textStatus+jqXHR);
                }

            });
        });

        $('.header_toggle').click(function(){
            $(".content").slideToggle();

            if  ($(this).text() == " Minimize this search area "){

                $(this).text("  What are you searching for?  ")
            }
            else{

                $(this).text(" Minimize this search area ")
            }


        });


        searchdata = {};
        $(".searchengine").on('change','.procurement_entity,.procurement_type,.subjectof_procurement,.subjectof_procurement,.procurement_method,.procurement_entityadv,.sourceof_funding,.financial_year,.best_beb,.admin_review',function(){
            var atribute = $(this).attr('dataattr');
            console.log(atribute);

            //console.log(atribute);
            var values = $(this).val();
            switch(atribute)
            {

                case 'procurement_entity':
                    if(values > 0)
                        searchdata['procurement_entity'] = values;
                    else
                        delete searchdata['procurement_entity'];
                    break;


                case 'procurement_type':
                    if(values > 0)
                        searchdata['procurement_type'] = values;
                    else
                        delete searchdata['procurement_type'];
                    break;

                case 'subjectof_procurement':

                    searchdata['subjectof_procurement'] = values;

                    break;


                case 'procurement_entity':
                    if(values > 0)
                        searchdata['procurement_entity'] = values;
                    else
                        delete searchdata['procurement_entity'];
                    break;

                case 'procurement_method':
                    if(values > 0)
                        searchdata['procurement_method'] = values;
                    else
                        delete searchdata['procurement_method'];
                    break;


                case 'sourceof_funding':
                    if(values > 0)
                        searchdata['sourceof_funding'] = values;
                    else
                        delete searchdata['sourceof_funding'];
                    break;

                case 'financial_year':
                    if(values != '0')
                        searchdata['financial_year'] = values;
                    else
                        delete searchdata['financial_year'];
                    break;

                case 'best_beb':
                    searchdata['best_beb'] = values;
                    break;

                case 'admin_review':
                    if(values.length > 0)
                        searchdata['admin_review'] = values;
                    else
                        delete searchdata['admin_review'];
                    break;



                default:
                    break;
            }

           

        });

$(".searchme").click(function(){

            console.log("Proccessing ... ");

            console.log(searchdata);
            
            $(".proccess").html("Proccessing ...");

            url = $(".searchengine").attr('dataurl');
            // ajax posting
            $.ajax({
                type: "POST",
                url:  url,
                data:searchdata,
                success: function(data, textStatus, jqXHR){
                    console.log(data);
                     $(".proccess").html("");
                    $(".searchstatus").html("");
                    $(".search_results").html(data);
                },
                error:function(data , textStatus, jqXHR)
                {
                    console.log(data);
                }
            });

        });



    })
</script>

<div class="clearfix content-wrapper" style="padding-top:28px;">
    <div class="col-md-12" style="margin:0 auto">
        <div class="clearfix">
            <div class="col-md-13 column content-area">
                <div class="page-header col-lg-offset-2" style="margin:0">

                    <!--start -->

                    <div class=" page-header col-lg-offset-2 searchengine" style="margin:0px 0px" dataurl="<?=base_url()."page/search_best_evaluated_bidder"; ?>">
                        <div class="seearchingine-header row clearfix" style="margin:0px 0px" >
                            <div class="col-md-12 column">
                                <div class="row clearfix">
                                    <div class="col-md-8 column " style="padding-left:0px;">

                                        <h3>Best Evaluated Bidders </h3>
                                    </div>
                                    <div class="col-md-4 column" style="padding-top:20px; font-size:20px; ">
                                        <a href="javascript:void(0);" style="text-decoration:none; color:#000; font-size:15px;" class="header_toggle"> What are you searching for? </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row content">
                            <div class="col-md-13 column form-group searchcontent">

                                <div class="row clearfix">
                                    <div class="col-md-4 column">
                                        <div class="row " style="padding-top:0px; padding-bottom:0px; padding-left:5px;">
                                            <b> Simple Search </b>
                                        </div>
                                        <div class="row ">
                                            <?php #print_r(get_pde_list()); ?>

                                            <select  dataattr="procurement_entity" class="col-md-12 chosen chozen  selectpicker form-control procurement_entity" id="procurement_entitys">
                                                <option value="0" >Procurement Entity </option>
                                                <?php
                                                $records = get_pde_list();
                                                foreach ($records as $key => $row) {
                                                    # code...
                                                    ?>
                                                    <option value="<?=$row['pdeid']; ?>"> <?=$row['pdename']; ?> </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="row ">
                                            <?php
                                            $procurementtype = get_pdetype_list();
                                            ?>
                                            <select  dataattr="procurement_type" class="col-md-12 chosen chozen  form-control procurement_type" id="procurement_types">
                                                <option value="0" >Procurement Type </option>
                                                <?php
                                                foreach ($procurementtype as $key => $row) {
                                                    # code...
                                                    ?>
                                                    <option value="<?=$row['id']; ?>"> <?=$row['title']; ?> </option>
                                                    <?php
                                                }
                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8 column" style="border-left:1px solid #ddd;">
                                        <div class="row " style="padding-top:0px; padding-bottom:0px; padding-left:10px;">
                                            <b> Advanced  Search  </b>
                                        </div>



                                        <div class="row ">
                                            <div class="col-md-12 column">

                                                <input type="text" class="col-md-12 form-control subjectof_procurement"  dataattr="subjectof_procurement" id="subjectof_procurement" placeholder="Subject of Procurement">
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-md-12 column">

                                                <input type="text" class="col-md-12 form-control best_beb" dataattr="best_beb" id="best_beb" placeholder="Search for Best Evaluated Bidder">
                                            </div>
                                        </div>



                                        <div class="row ">
                                            <div class="col-md-5 column">
                                                <select   dataattr="procurement_entity" class="col-md-12 form-control procurement_entity" placeholder="Subject of procurement " id="procurement_entityadv">
                                                    <option value="0" >Procurement Entity </option>
                                                    <?php
                                                    $records = get_pde_list();
                                                    foreach ($records as $key => $row) {
                                                        # code...
                                                        ?>
                                                        <option value="<?=$row['pdeid']; ?>"> <?=$row['pdename']; ?> </option>
                                                        <?php
                                                    }
                                                    ?>

                                                </select>
                                            </div>

                                            <div class="col-md-5 column">
                                                <?php
                                                # print_r(fetch_financialyears_list());
                                                $financial_years = fetch_financialyears_list();?>
                                                <select   dataattr="financial_year"  class="col-md-12 form-control financial_year" id="financial_year" placeholder="Subject of procurement">
                                                    <option value="0" >Financial Year </option>
                                                    <?php

                                                    foreach ($financial_years as $key => $row) {
                                                        # code...
                                                        ?>
                                                        <option value="<?=$row['financial_year']; ?>"> <?=$row['financial_year']; ?> </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row ">
                                            <div class="col-md-5 column">
                                                <select   dataattr="procurement_type" class="col-md-12 form-control procurement_type" id="procurement_typeadv"  >
                                                    <option value="0" >Procurement Type </option>
                                                    <?php
                                                    foreach ($procurementtype as $key => $row) {
                                                        # code...
                                                        ?>
                                                        <option value="<?=$row['id']; ?>"> <?=$row['title']; ?> </option>
                                                        <?php
                                                    }
                                                    ?>

                                                </select>
                                            </div>

                                            <div class="col-md-5 column">
                                                <?php
                                                #print_r(get_procurement_method_list());
                                                $procurement_method = get_procurement_method_list();
                                                ?>
                                                <select   dataattr="procurement_method"  class="col-md-12 form-control procurement_method" id="procurement_methodadv" >
                                                    <option value="0" >Procurement Method </option>
                                                    <?php
                                                    foreach ($procurement_method as $key => $row) {
                                                        # code...
                                                        ?>
                                                        <option value="<?=$row['id']; ?>"> <?=$row['title']; ?> </option>
                                                        <?php
                                                    }
                                                    ?>


                                                </select>
                                            </div>
                                        </div>


                                        <div class="row ">
                                            <div class="col-md-5 column">

                                                <?php
                                                #print_r(get_funding_source_list());
                                                $fundingsource = get_funding_source_list();
                                                ?>
                                                <select   class="col-md-12 form-control sourceof_funding" dataattr="sourceof_funding"  id="sourceof_funding"  >
                                                    <option value="0" >Source of Funding  </option>
                                                    <?php
                                                    foreach ($fundingsource as $key => $row) {
                                                        # code...
                                                        ?>
                                                        <option value="<?=$row['id']; ?>"> <?=$row['title']; ?> </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>


                                            <div class="col-md-5 column">
                                                <select class="col-md-12 form-control admin_review" dataattr="admin_review" id="admin_review">
                                                    <option value="0">Status</option>
                                                    <option value="N">Active</option>
                                                    <option value="Y">Admin Review</option>
                                                </select>
                                            </div>

                                        </div>
                                          <div class="row ">

                                            <div class="col-md-5 column">
                                                <button type="button" class="btn btn-default  searchme " style="font-size:12px; font-weight:bold;" ><i class="fa fa-search"></i> Search</button>
                                            
                                                <button type="button" class="btn btn-default  " style="font-size:12px; font-weight:bold;" onClick="javascript:location.reload(0);"><i class="fa fa-refresh"></i> Refresh</button>
                                            </div>



                                        </div>



                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <?=$this->load->view('public/parts/model_v')?>

                <div class="proccess">
                </div>
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
                            #
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
                                $stack = array( );
#print_r($page_list['page_list']);
                                foreach($page_list['page_list'] as $row)
                                {
                                    $haslots = $row['haslots'];
                                    $bidd = $row['bidid'];
                                    $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON receipts.receiptid = received_lots.receiptid  INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid INNER JOIN bidinvitations ON  bidinvitations.id = lots.bid_id  WHERE lots.bid_id = ".$row['bidid']."   AND    bidinvitations.haslots ='Y' AND receipts.beb='Y'  AND bestevaluatedbidder.ispublished='Y'   ")->result_array();

                                    if (in_array(  $bidd, $stack))
                                        continue;
                                    if($haslots == 'Y' && ( $lot_count[0]['nums'] > 0 ) )
                                        array_push( $stack, $bidd);
                                    #print_r($row);


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
                                                    #print_r($provider_array);
                                                    if ($row['providerlead'] ==   $vaue['providerid']) {
                                                        $lead = '&nbsp; <span class="label" title="Project Lead " style="cursor:pointer;background:#fff;color:orange;padding:0px;margin:0px; margin-left:-15px; font-size:18px; " >&#42;</span>';
                                                        #break;
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
