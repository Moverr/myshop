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
        $('.header_toggle').click(function(){
            $(".content").slideToggle();

            if($(this).text() == " Hide Advanced Search "){

                $(this).text(" Show Advanced Search  ");
            }
            else{

                $(this).text(" Hide Advanced Search ")
            }

        });
        searchdata = {};
        $(".searchengine").on('change','.procurement_entity,.procurement_type,.subjectof_procurement,.procurement_method,.procurement_entityadv,.sourceof_funding,.financial_year,.simplesearch',function(){
            var atribute = $(this).attr('dataattr');
            console.log(atribute);

            //console.log(atribute);
            var values = $(this).val();
            //alert(values);
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

                case 'simple_search':

                    searchdata['simple_search'] = values;

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



                default:
                    break;
            }

            //send information to server






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
<!-- start -->
<div class="clearfix content-wrapper" style="padding-top:0px;">
    <div class="col-md-13" style="margin:0 auto">
        <div class="clearfix">
            <div class="col-md-13 column content-area">
                <div class="page-header col-lg-offset-2" style="margin:0px 0px">



                    <div class=" page-header col-lg-offset-2 searchengine" style="margin:0px 0px" dataurl="<?=base_url()."page/search_currentbids"; ?>">
                      

                        <div class="seearchingine-header row clearfix" style="margin:0px 0px" >
                            <div class="col-md-13 column">
                                <div class="row clearfix">
                                    <div class="col-md-4 column " style="padding-left:20px;">

                                        <h3>Current Tenders </h3>
                                    </div>


                        <!-- Simple Search --> 
                                     <div class="col-md-6 column " style="padding-top:18px;">

                                            <!-- Search -->
                                             <div class="input-group">
        <input type="text" id="simplesearch"  dataattr="simple_search" class="form-control simplesearch"
                                               placeholder=" "/>
                                              <span class="input-group-btn">
                                                <a  href="javascript:void(0);" class="btn btn-default searchme" type="button">Simple Search </a>
                                              </span>
                                            </div> 

                                    </div>


                                    <!-- Show Advanced Search --> 
                                    <div class="col-md-2 column" style="padding-top:20px; font-size:20px; ">
                                        <a href="javascript:void(0);" style="text-decoration:none; color:#000; font-size:15px;" class="header_toggle"> Show Advanced Search  
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                <!-- Advanced Search --> 
                        <div class="row content">
                            <div class="col-md-13 column form-group searchcontent">

                                <div class="row clearfix">
                                   
                                    <div class="col-md-13 column" style="border-left:1px solid #ddd;">
                                       <!-- Advanced Search --> 
                                        <div class="row " style="padding-top:0px; padding-bottom:0px; padding-left:10px;">
                                            <b>  Advanced  Search  </b>
                                        </div>

                                        <div class="row ">
                                            <div class="col-md-10 column">

                                                <input type="text" class=" form-control subjectof_procurement"  dataattr="subjectof_procurement" id="subjectof_procurement" placeholder="Subject of Procurement">
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
                                                  <?php           
                                                        $procurementtype = get_pdetype_list();
                                                     ?>
                                                     
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

            </div>
            <div class="proccess">
            </div>
            <div class="searchstatus">
            </div>

            <div class="row clearfix current_tenders search_results">
                <?php
                if(!empty($level) && ($level == 'search'))
                {
                    $this->load->view('public/includes/search_latest_bids');
                }
                else
                {
                    ?>
                    <a class="pull-right  btn btn-sm btn-danger"
                       href="<?= base_url() . 'page/home/level/export' ?>">Export This Page</a>

                    <div class="column">
                        <?php
                        if(!empty($page_list))
                        {
                            print '<div class="row  titles_h ">
                            <div class="col-md-3">
                                <b>Procuring/Disposing Entity</b>
                            </div>
                            <div class="col-md-3">
                                <b>Subject of Procurement</b>
                            </div>
                            <div class="col-md-2">
                                <b>Procurement Type</b>
                                  <b> [Procurement Method] </b>
                            </div>

                            <div class="col-md-2">
                                <b>Deadline</b>
                            </div>
                            <div class="col-md-2">
                                <b>&nbsp;</b>
                            </div>
                               <div class="col-md-2">

                            </div>
                        </div><hr>';

                            $current_date = '';

                            $dated = '';
                            foreach($page_list as $row)
                            {

                                if($current_date != custom_date_format('d M, Y', $row['invitation_to_bid_date']))
                                {
                                    if($dated == custom_date_format('d M, Y', $row['invitation_to_bid_date']))
                                    {
                                        print '<div class="row"><div class=" tender_date">'.
                                            '</div>';
                                    }
                                    else{
                                        print '<div class="row"><div class=" tender_date">' .
                                            '<b style="font-size:30px; col-md-6">Posted on '. custom_date_format('d M, Y', $row['invitation_to_bid_date']).
                                            '</b>   </div>';
                                    }
                                    $dated =   custom_date_format('d M, Y', $row['invitation_to_bid_date']);
                                }

                                print '<div class="row">'.
                                    '<div class="col-md-3 procurement_pde">' . $row['pdename'] . '</div>' .
                                    '<div class="col-md-3 procurement_subject">' . $row['subject_of_procurement'] . '&nbsp;' .
                                    ($row['numOfAddenda'] > 0?'<span><a class="btn btn-xs btn-success" href="'. base_url() .
                                        'page/addenda_list/a/'. encryptValue($row['bidinvitation_id']) .'">View addenda</a></span>' : '').
                                    '<BR/><div class=""><a class="btn btn-xs btn-primary" href="'. base_url() .
                                    'page/bid_details/i/'. encryptValue($row['bidinvitation_id']) .'">View details</a></div>'.
                                    '</div>' .

                                    '<div class="col-md-2 procurement_pde"><strong>'. $row['procurement_type'] . '</strong> ['. $row['procurement_method'].'] </div>';
                                $time = !empty($formdata['bid_submission_deadline']) ? ' at <strong> ' . display_time('h:i A', trim(substr($formdata['bid_submission_deadline'], 10, 10))).'</strong>' : ' ';


                                print '<div class="col-md-2"><strong>'. custom_date_format('d M, Y', $row['bid_submission_deadline']) .'</strong>' .$time.'</div>'.
                                    '<div class="col-md-2">'.
                                    '<ul style="list-style:none;"><li>'.
                                    '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.base_url() .
                                    'page/bid_details/i/'. encryptValue($row['bidinvitation_id']).'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a></li><li> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.base_url().'page/bid_details/i/'. encryptValue($row['bidinvitation_id']).'
"></div></li><li><div class="fb-share-button" data-href="'. base_url() .
                                    'page/bid_details/i/'. encryptValue($row['bidinvitation_id']) .'" data-layout="button" data-size="medium"></div></li></ul>
 '.
                                    '</div>'.
                                    '</div>  '.


                                    '<hr>';

                                $current_date = custom_date_format('d M, Y', $row['bid_date_approved']);
                            }

                            print '<div class="pagination pagination-mini pagination-centered">'.
                                pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().
                                    "page/home/p/%d")
                                .'</div>';


                        }
                        else
                        {
                            if(!empty($formdata)):
                                print format_notice("ERROR: Your search criteria did not match any records");
                            else:
                                print format_notice("ERROR: There are no active tenders");
                            endif;

                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>