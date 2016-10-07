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

            if  ($(this).text() == " Minimize this search area "){

                $(this).text("  What are you searching for?  ")
            }
            else{

                $(this).text(" Minimize this search area ")
            }

        });

        // search engine


        searchdata = {};
        $(".searchengine").on('change','.procurement_entity,.procurement_type,.subjectof_procurement,.subjectof_procurement,.procurement_method,.procurement_entityadv,.sourceof_funding,.financial_year,.contracts_status,.service_providers',function(){
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

                case 'service_providers':
                    searchdata['service_providers'] = values;
                    break;

                case 'contracts_status':
                    if(values.length > 0)
                        searchdata['contracts_status'] = values;
                    else
                        delete searchdata['contracts_status'];
                    break;


                default:
                    break;
            }

            //send information to server


            console.log("Proccessing ... ");
            $(".searchstatus").html("Proccessing ...")

            url = $(".searchengine").attr('dataurl');

            $.ajax({
                type: "POST",
                url:  url,
                data:searchdata,
                success: function(data, textStatus, jqXHR){
                    console.log(data);
                    $(".searchstatus").html("");
                    $(".search_results").html(data);
                },
                error:function(data , textStatus, jqXHR)
                {
                    console.log(data);
                }
            });


        });


        //end search engine

    })
</script>


<!-- start -->
<div class="col-md-13 column content-area">
    <div class="page-header col-lg-offset-2" style="margin:0px 0px">
        <div class=" page-header col-lg-offset-2 searchengine" style="margin:0px 0px" dataurl="<?=base_url()."page/search_awarded_contracts"; ?>">
            <div class="seearchingine-header row clearfix" style="margin:0px 0px" >
                <div class="col-md-12 column">
                    <div class="row clearfix">
                        <div class="col-md-8 column " style="padding-left:0px;">

                            <h3>Signed Contracts</h3>
                        </div>

                        <div class="col-md-4 column" style="padding-top:20px; font-size:20px; ">
                            <a href="javascript:void(0);" style="text-decoration:none; color:#000; font-size:15px;" class="header_toggle"> What are you searching for?  </a>
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

                            <div class="col-md-12 column">
                                <input type="text" class="col-md-12 form-control service_providers" dataattr="service_providers" id="service_providers" placeholder="Search Provider" />
                            </div>

                            <div class="row ">

                                <div class="col-md-5 column">
                                    <select   dataattr="procurement_entity" class="col-md-12 form-control procurement_entity" placeholder="Subject of procurement " id="procurement_entityadv">
                                        <option value="0" >Procurement Entity </option>
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
                                    <select class="col-md-12 form-control contracts_status" dataattr="contracts_status" id="contracts_status">
                                        <option value="0">Status</option>
                                        <option value="A">Awarded</option>
                                        <option value="C">Completed</option>
                                    </select>
                                </div>



                            </div>
                            <div class="row ">

                                <div class="col-md-5 column">
                                    <button type="button" class="btn btn-default  " onclick="javascript:location.href='<?=base_url().'page/awarded_contracts'?>';" style="font-size:12px; font-weight:bold;"><i class="fa fa-list" > </i> List Items</button>



                                    <!-- <button type="button" class="btn btn-default  ">Search</button>
                                     -->
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
<style>
    .fixed2 {
        position: fixed;
        width: 87%;
        z-index: 999;
        top: 40px;
        background: #fff;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        padding-top: 10px;
    }
</style>

<div class="searchstatus">
</div>
<!-- end -->
<div class="row clearfix current_tenders search_results">


    <?php
    if(!empty($level) && ($level == 'search'))
    {
        $this->load->view('public/includes/search_awarded_contracts');
    }
    else
    {
        ?>
        <div class="column">
      
            <?php
            if(!empty($page_list))
            {
                # print_r($page_list);
                print '<div class="row titles_h">
                              <div class="col-md-2">
                                  <b>Procuring/Disposing Entity</b>
                              </div>
                              
                              <div class="col-md-2">
                                  <b>Subject Of Procurement</b>
                                  [Procurement Reference Number]
                              </div>
                              <div class="col-md-2">
                                  <b>Service Provider</b>
                              </div>
                                <div class="col-md-1">
                                  <b>Date Signed</b>
                              </div>
                               <div class="col-md-2">
                                 Date of Commencement <br/> <b>[ Date of Completion]</b>
                              </div>
                              <div class="col-md-2"><b>Contract Value  </b></div>
                               <div class="col-md-1">
                                  <b>Status</b>
                              </div>
                          </div><hr>';
                # print_r($page_list);

                foreach($page_list as $row)
                {
                    #if multiple providers..
                    $providername = $row['providernames'];
                    if(!empty($row['joint_venture'])):
                        $providername = '';
                        $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $row['joint_venture'] .'"')->result_array();

                        if(!empty($jv_info[0]['providers'])):
                            $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
                            foreach($providers as $provider):
                                $providername .= (!empty($providername)? ', ' : ''). $provider['providernames'];
                            endforeach;

                        endif;

                    endif;

                    print '<div class="row">'.
                        '<div class="col-md-2 procurement_pde">'. $row['pdename'] .'</div>'.

                        '<div class="col-md-2 procurement_subject">'. $row['subject_of_procurement'] .'<br/><label class="procurement_pde">['.$row['procurement_ref_no'].']</label> <br/>
 
          
                              <a href="https://twitter.com/share" class="twitter-share-button  " 
                              data-url="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" data-size="small" data-hashtags="tenderportal_ug"
                              data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" 
                              data-action="share" data-size="medium" data-annotation="none" 
                              data-height="24" data-href="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" 
                              ></div>&nbsp;<div class="fb-share-button" 
                              data-href="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" 
                              data-layout="button" data-size="medium"></div>
                             
                             </div>'.
                        '<div class="col-md-2 procurement_pde">'. $providername .'</div>'.


                        '<div class="col-md-1"><strong>'. custom_date_format('d M, Y', $row['date_signed']) .'</strong></div>'.
                        '<div class="col-md-2"> '. custom_date_format('d M, Y', $row['commencement_date']).' <br/> <b>['. custom_date_format('d M, Y', $row['completion_date']).']</b>';
                    if(!empty($row['actual_completion_date']))
                    {
                        echo "<br/><a  class='btn  btn-xs btn-primary' role='button' href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".custom_date_format('d M, Y',$row['actual_completion_date'])."</a>";
                    }
                    else
                    {}
                    // (!empty($row['actual_completion_date'])) ? $row['actual_completion_date'] : ''.
                    echo  '</div>'.

                        '<div class="col-md-2" style="font-family:Georgia; font-size:13px;">';
                    #number_format($row['id']).

                    $query = $this->db->query("select a.amount,b.title from  contract_prices a  inner join  currencies b on a.currency_id = b.id where a.contract_id =".$row['id']." ORDER BY a.dateadded DESC ")-> result_array();
                    #currency_id
                    if(!empty($query))
                    {
                        echo "<ul>";
                        foreach ($query as $key => $rowvalue) {
                            #code...
                            echo "<li>".number_format($rowvalue['amount'])."&nbsp; ".$rowvalue['title']."</li>";
                        }
                        echo "</ul>";
                    }
                    if(!empty($row['final_contract_value']))
                    {
                        echo "<br/><a  class='btn  btn-xs btn-primary' role='button' href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".number_format($row['final_contract_value'])."&nbsp; </a>";
                    }
                    else
                    {}

                    print '</div>'.
                        '<div class="col-md-1">';
                    if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
                    {
                        $status_str = '<span class="label label-success label-mini">Completed</span>';
                        $completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="icon-ok"></i> View completion details</a>';
                    }
                    else
                    {
                        $status_str = '<span class="label label-warning label-mini">Awarded</span>';
                        $completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="icon-ok"></i> Mark as complete</a>';
                    }

                    echo $status_str.'</div>'.
                        '</div>'.
                        '<hr>';
                }
                #pagination::
                print '<div class="pagination pagination-mini pagination-centered">'.
                    pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().
                        "page/awarded_contracts/p/%d")
                    .'</div>';
            }
            else
            {
                print format_notice("ERROR: No contracts have been signed");
            }
            ?>
        </div>
    <?php } ?>
</div>