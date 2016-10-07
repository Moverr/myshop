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
        $(".searchengine").on('change','.disposing_entity,.disposing_method,.subjectof_disposal,.subjectof_disposal,.procurement_method,.disposing_entityadv,.sourceof_funding,.financial_year,.simplesearch',function(){
            var atribute = $(this).attr('dataattr');
            console.log(atribute);

            //console.log(atribute);
            var values = $(this).val();
            //alert(values);
            switch(atribute)
            {

                case 'disposing_entity':
                    if(values > 0)
                        searchdata['disposing_entity'] = values;
                    else
                        delete searchdata['disposing_entity'];
                    break;


                case 'disposing_method':
                    if(values > 0)
                        searchdata['disposing_method'] = values;
                    else
                        delete searchdata['disposing_method'];
                    break;


                case 'simple_search':
                    searchdata['simple_search'] = values;
                    break;


                case 'subjectof_disposal':

                    searchdata['subjectof_disposal'] = values;

                    break;


                case 'disposing_entity':
                    if(values > 0)
                        searchdata['disposing_entity'] = values;
                    else
                        delete searchdata['disposing_entity'];
                    break;

                case 'procurement_method':
                    if(values > 0)
                        searchdata['procurement_method'] = values;
                    else
                        delete searchdata['procurement_method'];
                    break;


                case 'sourceof_funding':disposal
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

<div class="clearfix content-wrapper tabbable" style="padding-top:28px;">
    <div class="col-md-12" style="margin:0 auto">
        <style type="text/css">
            .navs{ padding: 0px;}
            .nav-tabs > li {
                list-style: none; margin-left: 0px;
            }
            .nav-tabs > li > a, .nav-tabs > li > a:hover, .nav-tabs > li > a:focus, .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
                border: none;
                border-radius: 0; text-decoration: none;
            }
            .nav-tabs>li>a:hover {
                border-color: #eee #eee #ddd;
            }
            .navs>li>a:hover, .nav>li>a:focus {
                text-decoration: none;
                background-color: #eee;
            }
            .nav-tabs>li>a {
                padding: 15px;
            }
            .nav-tabs>li>a {
                margin-right: 2px;
                line-height: 1.428571429;
                border: 1px solid transparent;
                border-radius: 4px 4px 0 0;
            }
            .navs>li>a {
                position: relative;
                display: block;
                padding: 10px 15px;
            }

            .nav-tabs>li>a
            {
                padding: 15px;
            }
        </style>
        <ul class="navs nav-tabs">
            <li class="active">
                <a href="<?=base_url()."page/disposal_plans"; ?>" >DISPOSAL PLAN</a>
            </li>
            <li>
                <a href="<?=base_url()."page/disposal_notices"; ?>"  >DISPOSAL NOTICES</a>
            </li>

            <li>
                <a href="<?=base_url()."page/published_lbas"; ?>"  >PUBLISHED LBAS</a>
            </li>
        </ul>
    </div>


    <div class="col-md-12" style="margin:0 auto">
        <div class="clearfix">


            <div class="col-md-13 column content-area">


                <?php
                #   if (!isset($details)) {
                ?>
                <!-- start -->


                <div class="col-md-13 column content-area">
                    <div class="page-header col-lg-offset-2" style="margin:0px 0px">
                        <div class=" page-header col-lg-offset-2 searchengine" style="margin:0px 0px" dataurl="<?=base_url()."page/search_diposal_plans"; ?>">
                            <div class="seearchingine-header row clearfix" style="margin:0px 0px" >
                                <div class="col-md-13 column">
                                    <div class="row clearfix">
                                        <div class="col-md-4 column " style="padding-left:20px;">

                                            <h3>Disposal Plans</h3>
                                        </div>


                                                <!-- Simple Search --> 
                                     <div class="col-md-6 column " style="padding-top:18px;">

                                            <!-- Search -->
                                             <div class="input-group">
                                             <input type="text" id="simplesearch"  dataattr="simple_search" class="form-control simplesearch"
                                               placeholder=" "/>
                                              <span class="input-group-btn">
                                                <a  href="javascript:void(0);" class="btn btn-default searchme" type="button"> Simple Search </a>
                                              </span>
                                            </div> 

                                    </div>




                                        <div class="col-md-2 column" style="padding-top:20px; font-size:20px; ">
                                            <a href="javascript:void(0);" style="text-decoration:none; color:#000; font-size:15px;" class="header_toggle"> Show Advanced Search  </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="row content">
                                <div class="col-md-13 column form-group searchcontent">

                                    <div class="row clearfix">
                                        
                                        <div class="col-md-13 column" style="border-left:1px solid #ddd;">
                                            <div class="row " style="padding-top:0px; padding-bottom:0px; padding-left:10px;">
                                                <b> Advanced  Search  </b>
                                            </div>

                                            <div class="row ">
                                                <div class="col-md-10 column">

                                                    <input type="text" class="col-md-12 form-control subjectof_disposal"  dataattr="subjectof_disposal" id="subjectof_disposal" placeholder="Subject of Disposal">
                                                </div>
                                            </div>

                                            <div class="row ">

                                                <div class="col-md-5 column">
                                                    <select   dataattr="disposing_entity" class="col-md-12 form-control disposing_entity" placeholder="Subject of procurement " id="disposing_entityadv">
                                                        <option value="0" >Disposing  Entity </option>
                                                        <?php
                                                        $records = get_pde_list();
                                                        foreach ($records as $key => $row) {
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
                                                    $financial_years = fetch_disposal_years();?>
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
                                                 <?php
                                                $disposaltype = fetch_disposal_methods();
                                                ?>

                                                    <select   dataattr="disposing_method" class="col-md-12 form-control disposing_method" id="disposing_methodadv"  >
                                                        <option value="0" >Disposing Method </option>
                                                        <?php
                                                        foreach ($disposaltype as $key => $row) {
                                                            # code...
                                                            ?>
                                                            <option value="<?=$row['id']; ?>"> <?=$row['method']; ?> </option>
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

                <!-- end -->

                <?php # } ?>
                <?=$this->load->view('public/parts/model_v')?>



                      <div class="proccess">
                </div>
                
                <div class="searchstatus"> </div>

                <div class="row clearfix current_tenders  search_results">
                    <?php
                    if(!empty($level) && ($level == 'search'))
                    {
                        $this->load->view('public/search_disposal');
                    }
                    else
                    {
                        ?>
                        <div class="column col-md-13">


                            <?php
                            if (isset($details)) {
                                ?>


                             

                                <?php
                                echo '<div class="row invoice-list"> <div class="page-header col-lg-offset-2" style="margin:0">
                       <h3>' . $page_list['page_list'][0]['financial_year'] . ' Disposal Plan for ' . $page_list['page_list'][0]['pdename'];

                                echo '</h3></div>';
                                echo '<p>
                <b>Financial Year: </b>' . $page_list['page_list'][0]['financial_year'] . '<br>
                <b>Entity: </b> ' . $page_list['page_list'][0]['pdename'] . '<br>

            </p>';

                                print '
                            <div class="col-md-3">
                                <b>Subject of Disposal </b>
                            </div>

                            <div class="col-md-3">
                                <b>Quantity</b>
                            </div>

                            <div class="col-md-3">
                                <b>The Entity </b>
                            </div>

                            <div class="col-md-3">
                                <b>Disposal Method </b>
                            </div>
                            </div>


                             <hr>';

                                #print_r($page_list['page_list']['disp_plan']);
                                foreach ($page_list['page_list'] as $row) {
                                    print '<div class="row">' .
                                        '<div class="col-md-3">' . $row['subject_of_disposal'] . '</div>' .
                                        '<div class="col-md-3 procurement_pde"> ';
                                    if($row['quantity'] > 0)
                                        print number_format($row['quantity']);
                                    print '</div>' .
                                        '<div class="col-md-3 ">' . $row['pdename'] . '</div>' .
                                        '<div class="col-md-3">' . $row['method'] . '</div>' .
                                        '</div>' .
                                        '<hr>';
                                }


                            } else {
                                #   print_r($page_list['page_list']);
                                /// print_r($page_list); exit();
                                if(!empty($page_list['page_list']))
                                {
                                    print '<div class="row titles_h">

                           
                              <div class="col-md-8">
                                <b>Procuring/Disposing Entity</b>
                            </div>
                              
                            
                             <div class="col-md-4">
                              
                            </div>
                            

                            
                            
                        </div><hr>';

#print_r($page_list['page_list']);
                                    foreach($page_list['page_list'] as $row)
                                    {
                                        // custom_date_format('d M, Y', $row['dateadded'])
                                        print '<div class="row  column col-md-13">' .

                                            '<div class="col-md-6 procurement_pde"> ' . $row['pdename'] . ' </div>' .

                '<div class="col-md-2 "> <a href="'.base_url().'page/disposal_plans/level/export/plan/'.base64_encode($row['disposalpln_id']).'" class="btn btn-default">Download</a> </div>'.

                     '<div class="col-md-4 "> 
                                            <a class="btn btn-xs btn-primary center"  href="' . base_url() . 'page/disposal_plans/details/' . base64_encode($row['disposalpln_id']) . '">Details of the ' . $row['financial_year'] . ' Annual Disposal Plan </a>' .
                                            '</div>'.




                                            '</div>'.
                                            '<hr>';
                                    }
                                    print '<div class="pagination pagination-mini pagination-centered">'.
                                        pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().
                                            "page/disposal_plans/p/%d")
                                        .'</div>';

                                    //   <a id="modal-703202" href="#modal-container-703202" role="button" class="btn" data-toggle="modal">Launch demo modal</a>
                                }
                                else
                                {
                                    print format_notice("ERROR: There are no Disposal Plans");
                                }
                            }
                            ?>
                        </div>

                        <?php

                    }

                    ?>
                </div>
            </div>

        </div>
        <?=$this->load->view('public/includes/footer')?>
    </div>
</div>

</body>
</html>