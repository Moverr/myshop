    <?php  if(empty($requiredfields)) $requiredfields = array();?>


    <script type="text/javascript">
        $(document).on('click','.printer',function(){


            $(".table").printArea();

        })


        $(document).ready(function() {
            $('.table').dataTable({
                "paging":   false,
                "ordering": true,
                "info":     false });

            /*$('.table tbody').on('click', 'tr', function () {
             var name = $('td', this).eq(0).text();
             alert( 'You clicked on '+name+'\'s row' );
             } );  */
        } );


        /*
        Fetch Details and then Load  generate the Reference Number :

        */

    //Dealing with Changing state in Disposal Record at IFB :
    $(function(){
        // on change Disposal id change
        $("#disposal_id").change(function(){        
            var getUrl = baseurl() + 'disposal/ajax_fetch_disposal_entry'; 
            formdata = {};
            console.log('Proccessing ...');
            console.log(getUrl);
            var  formdata = {};
            //fet the formdata 
            disposal_entry_id = this.value;
            
            formdata['disposal_entry_id'] = disposal_entry_id;
            //ajax fetch  :: 
             $(".disposal_records").html("Proccessing ... ");
                   $.ajax({               
                        url: getUrl,
                        type: 'POST',
                        data: formdata,
                        success: function(data)
                        {                       
                            console.log("Response");
                            if(data != 0)
                            {
                                $(".disposal_records").html(data);
                            }

                          
                            console.log(data);                     
                        },
                        error:function(data)
                        {
                            console.log('ERROR');
                           console.log(data);
                        }

                        }) 
                //end of events
                    });
                })
            
            

            /*
     var getUrl = getBaseURL() + 'bids/procurement_record_details';

        if($(this).hasClass('get_beb'))
          getUrl += '/b/get';
          console.log(getUrl);
          formdata = {proc_id: $(this).val()};
          branch_shortcode = $("#pde_branch").val();
          formdata['branch_shortcode'] = branch_shortcode;
          procurement_entry_id = $(this).val();
        $("#sequencenumber").val("");
        $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
                $.ajax({
                    url: getUrl,
                    type: 'POST',
                    data: formdata,
                    success: function(msg)
                    {
                        $("#procurement_plan_details").html(msg);
                          //get the procurement ID ::

                            $.ajax({
                            url: getBaseURL() + 'bids/loadprocurementrefno',
                            type: 'POST',
                            data: formdata,
                            success: function(msg)
                            {
                              if(msg != 0)
                              {
                                $("#sequencenumber").removeClass("hidden");
                               $("#sequencenumber").val(msg);
                               console.log(msg);
                               //fetch procurement entry details 
                                 procurement_entry_details();
                              }
                              else
                              {
                                 $("#sequencenumber").val("");
                              }


                            },
                            error:function(data)
                            {
                            console.log(msg);
                            }
                            });
                        //end of events
                    }
                });


            */
            
      
     



    </script>

    <?php
    # created by  mover
    #included into the play : so it would be nice if its plugged in Ajaxly
    /*
    Check if Pde name Exists in the Db..   server side checks
    */


    #print_r($bid_inviation);

    $ref_no =  '';
    $disposalserialno ='';
    $biddocumentissuedate = '';
    $bid_opening_date =  '';
    //$subject_of_disposal =  '';
    $date_of_approval_form28 =  '';
    $date_of_initiation_form28 = '';
    $cc_approval_date = '';
    $disposal_ref_no = '';
    $bidenddate ='';
    $receiptid = 0;
    $disposal_id = 0;
    $disposal_reference_number = '';
    $deadline_for_submition = '';
    $deadline_submission= '';


    #exit();
    if((!empty($bid_inviation)) && (count($bid_inviation) > 0) )
    {
        #  print_r($bid_inviation['page_list']);
        $ref_no =  '';
        foreach ($bid_inviation['page_list'] as $key => $row) {
            # code...
			$formdata = $row;
            $disposalserialno           = $row['disposal_serial_no'];
            $biddocumentissuedate       = custom_date_format('Y-m-d',$row['bid_document_issue_date']);
            $bid_opening_date           = custom_date_format('Y-m-d',$row['bid_opening_date']);
            $bid_opening_time           = $row['bid_openning_date_time'];
            $date_of_approval_form28    = custom_date_format('Y-m-d',$row['date_of_approval_form28']);
            $cc_approval_date           = custom_date_format('Y-m-d',$row['cc_approval_date']);
            $submission_deadline        = $row['cc_approval_date'];
            $inspection_addr            = $row['documents_inspection_address'];
            $bidopening_addr            = $row['bid_opening_address'];
            $biddelivery_addr           = $row['documents_address_issue'];
            $inspectionstart_date       = custom_date_format('Y-m-d',$row['inspect_openning_date']);
            $inspectionstart_time       = $row['inspect_openning_date_time'];
            $inspectionclose_date       = custom_date_format('Y-m-d',$row['inspect_close_date']);
            $inspectionclose_time       = $row['inspect_close_date_time'];
            $bidevaluation_from         = custom_date_format('Y-m-d',$row['bid_evaluation_from']);
            $bidevaluation_to           = custom_date_format('Y-m-d',$row['bid_evaluation_to']);
            $beb_notice                 = $row['display_of_beb_notice'];
            $contractsgnature_date      = custom_date_format('Y-m-d',$row['contract_award_date']);
            $deadline_submission        = custom_date_format('Y-m-d',$row['deadline_for_submition']);

            $disposal_ref_no = $row['disposal_serial_no'];
            $subject_of_disposal = $row['subject_of_disposal'];
            $bidenddate = custom_date_format('Y-m-d',$row['bid_opening_date']);

            if(!empty($bidenddate))
            {
                $bidenddate =   custom_date_format('Y-m-d',(strtotime(custom_date_format('Y-m-d',$row['bid_opening_date']).' + '.$row['bid_duration'].' days' ))) ;
            }
            else
            {
                $bidenddate = '' ;
            }


            $disposal_id = $row['id'] ;
        }


    }
	
	#print_r($formdata);


    $i = 'insert';
    if(!empty($formtype))
    {

        switch($formtype)
        {
            case 'edit':
			if(!empty($disposal_records))
			$formdata['disposal_id'] = $disposal_records[0]['id'];
		
              $i  = 'edit/'.$edit;
                break;
        }

    }


    ?>


    <div class="widget">
        <div class="widget-title">
            <h4><i class="fa fa-reorder"></i>&nbsp;<?=$page_title; ?> </h4>
                  <span class="tools">
                      <a href="javascript:;" class="fa fa-chevron-down"></a>
                      <a href="javascript:;" class="fa fa-remove"></a>
                  </span>
        </div>
        <div class="widget-body">


            <div class="row-fluid">

                <!-- id="disposal_bid_invitation" -->
                <form  method="post" action="<?=base_url() . 'disposal/save_bid_invitation' . ((!empty($i))? '/'.$i : '' )?>"
                       class="form-horizontal"

                       name="disposal_bid_invitation"
                       data-type="newrecord"
                       data-cheks="pdename<>pdecode"
                       data-check-action=" "
                       data-action="<?=base_url();?>disposal/save_bid_invitation<?= '/'.$i; ?>"
                       data-elements="*disposal_serial_no<>disposal_reference_no<>bid_document_issue_date<>deadline_for_submition<>cc_date_of_approval<>date_of_approval_form28<>date_of_initiation_form28" >

                    <div class="row-fluid">
                        <script type="text/javascript">
                            $(function(){

                                var url  = '<?=base_url()?>disposal/load_bid_invitation_form/';


                                $(".ifb_financial_year").change(function(){
                                    //alert($(this).val());
                                    var financial_year = $(this).val().trim();

                                    if(financial_year.length > 0)
                                    {
                                        url += 'financial_year/'+financial_year;
                                        location.href =url;
                                    }

                                });

                            })
                        </script>

                        <?php
                        #print_r($requiredfields);
                        ?>
                        <div class="control-group   <?=(in_array('ifb_financial_year', $requiredfields)? 'error': '')?>">

                            <label class="control-label">Financial Year </label>
                            <div class="controls">
                                <select   id="ifb_financial_year" class="chosen ifb_financial_year    financial_year" name="ifb_financial_year">
                                    <?=get_select_options($financial_years, 'fy', 'label', (!empty($current_financial_year)? $current_financial_year : '' ))?>
                                </select>
                            </div>
                        </div>

                        <div class="row-fluid">
                            <?php
                            #print_r($active_procurements);
                            ?>
                            <div class="control-group  <?=(in_array('disposal_id', $requiredfields)? 'error': '')?> ">
                                <label class="  control-label">Search Disposal Item</label>
                                <div class="controls">

                                    <select  class="  chosen disposal_id"  id="disposal_id" name="disposal_id"  data-placeholder="Disposal Reference  Numbers " tabindex="1">
    <!--

    Fetch Disposal Records, but then allow on edit to get the select record to change state ::
    -->
                        <?=get_select_options($disposal_records, 'id', 'subject_of_disposal', (!empty($formdata['disposal_id'])? $formdata['disposal_id'] : '' ))?>


                                    

                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php
                        if(!empty($formdata['disposal_id'] ))
                        {
                                                ?>

                        <script>
                        $(function(){
                            $("#disposal_id").val(<?= $formdata['disposal_id'];?>).trigger("change");    
                        })
                        </script>
                        <?php
                        }
                        ?>
    <!-- Populated Details -->

                  <div class="row-fluid disposal_records">
                    



                  </div>


                        <!-- Disposal Reference Number-->

                        <div class="row-fluid">
                            <div class="control-group   <?=(in_array('disposal_reference_no', $requiredfields)? 'error': '')?>  ">
                                <label class="control-label">Disposal Reference Number  </label>
                                <div class="controls">
                <div class="input-append" >
                <input name="disposal_reference_no"  
                class="disposal_reference_no" 
                id="disposal_reference_no" 
                type="text" value="<?=!empty($formdata['disposal_reference_no']) ?$formdata['disposal_reference_no']: !empty($formdata['disposal_ref_no']) ? $formdata['disposal_ref_no'] : '' ?>">

                                    </div>
                                </div> </div>
                        </div>



                        <!-- Date of Approval of Form 28 -->
                        <div class="row-fluid">
                            <div class="control-group  <?=(in_array('date_of_approval_form28', $requiredfields)? 'error': '')?> ">
                                <label class="control-label">Date of Approval of Form 28 </label>
                                <div class="controls   ">
                                    <div class="input-append date date-picker" data-date="<?=date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                        <input name="date_of_approval_form28" data-date="<?=date('Y-m-d'); ?>" class=" date-picker date_of_approval_form28" id="date_of_approval_form28" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=!empty($formdata['date_of_approval_form28']) ?$formdata['date_of_approval_form28']:'' ?>">
                                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                                        <button type="button" onclick="javascript:$('#date_of_approval_form28').attr('value','');"><i class="fa fa-refresh"></i> </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end of staff -->


                        <!-- Contracts Committee Approval Date -->
                        <div class="row-fluid">
                            <div class="control-group   <?=(in_array('cc_date_of_approval', $requiredfields)? 'error': '')?> " >
                                <label class="control-label">Contracts Committee Approval Date</label>
                                <div class="controls   ">
                                    <div class="input-append date date-picker" data-date="<?=date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                        <input name="cc_date_of_approval" data-date="<?=date('Y-m-d'); ?>" class="cc_date_of_approval date-picker" id="cc_date_of_approval" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=!empty($formdata['cc_date_of_approval']) ?$formdata['cc_date_of_approval']: !empty($formdata['cc_approval_date']) ?$formdata['cc_approval_date']  : '' ?>">
                                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                                        <button type="button" onclick="javascript:$('#cc_date_of_approval').attr('value','');"><i class="fa fa-refresh"></i> </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end -->


                        <!-- date of form28 approval and initiation -->

                        <!--    <div class="row-fluid">
                <div class="control-group">
                <label class="control-label">Date of Initiation of Form 28  </label>
                <div class="controls">
                <div class="input-append date date-picker" data-date="<?=date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                <input name="date_of_initiation_form28" data-date="<?=date('Y-m-d'); ?>" class="date_of_initiation_form28" id="date_of_initiation_form28" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=$date_of_initiation_form28 ?>">
                <span class="add-on"><i class="fa fa-calendar"></i></span>
                </div>
                </div> </div>
                </div>  -->





                        <!-- Bid Document Issue Date -->
                        <div class="row-fluid">
                            <div class="control-group <?=(in_array('bid_document_issue_date', $requiredfields)? 'error': '')?>  ">
                                <label class="control-label">Bid Document Issue Date</label>
                                <div class="controls   ">
                                    <div class="input-append date date-picker" data-date="<?=date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                        <input name="bid_document_issue_date" data-date="<?=date('Y-m-d'); ?>" class="bid_document_issue_date date-picker " id="bid_document_issue_date" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=!empty($formdata['bid_document_issue_date']) ?$formdata['bid_document_issue_date']:'' ?>">
                                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                                        <button type="button" onclick="javascript:$('#bid_document_issue_date').attr('value','');"><i class="fa fa-refresh"></i> </button>

                                    </div>
                                </div></div>
                        </div>

                        <!-- Inspection Period -->


                        <!-- Deadline of Submission-->
                        <div class="row-fluid">
                            <div class="control-group <?=(in_array('deadline_for_submition', $requiredfields)? 'error': '')?>">
                                <label class="control-label">Deadline of Submition</label>
                                <div class="controls   ">

                     <div class="input-append date date-picker" data-date="<?=date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                        <input name="deadline_for_submition" data-date="<?=date('Y-m-d'); ?>" class="deadline_for_submition date-picker " id="deadline_for_submition" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=!empty($formdata['deadline_for_submition']) ?$formdata['deadline_for_submition']:'' ?>">
                                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                                        <button type="button" onclick="javascript:$('#deadline_for_submition').attr('value','');"><i class="fa fa-refresh"></i> </button>
                                    </div>

                                </div> </div>
                        </div>

                        <!-- Address Where Inspection will be Done -->
                        <div class="control-group <?=(in_array('documents_inspection_address', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Address Where Inspection <br/> Of Disposal Items Will be Done<span>*</span></label>
                            <div class="controls  <?=(in_array('documents_inspection_address', $requiredfields)? 'error': '')?> ">
                                <textarea rows="3" name="documents_inspection_address" id="documents_inspection_address" class="input-xxlarge"> 

<?=!empty($formdata['documents_inspection_address']) ?$formdata['documents_inspection_address']:'' ?>


                                </textarea>
                            </div>
                        </div>


                        <!-- Address Where bids will be delivered -->
                        <div class="control-group <?=(in_array('documents_address_issue', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Address  Bids Will Be Delivered TO :<span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="documents_address_issue" id="documents_address_issue" class="input-xxlarge"><?=(!empty($biddelivery_addr)? $biddelivery_addr : '' )?>
<?=!empty($formdata['documents_address_issue']) ?$formdata['documents_address_issue']:'' ?>


                                </textarea>
                                    <span>
                                    <input type="checkbox" name="same_as_above" id="same_as_inspection" value="same" />
                                    <label for="same_as_inspection" style="display:inline">
                                        Tick if same as above or state
                                    </label>
                                    </span>
                            </div>
                        </div>

                        <!-- Bid Opening Address-->

                        <div class="control-group <?=(in_array('documents_address_issue', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid Opening Address <span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="bid_opening_address" id="bid_opening_address" class="input-xxlarge"><?=(!empty($bidopening_addr)? $bidopening_addr : '' )?>
                            <?=!empty($formdata['bid_opening_address']) ?$formdata['documents_address_issue']:'' ?>


</textarea>
                                    <span>
                                    <input type="checkbox" name="same_as_above" id="same_as_delivered_to" value="same" />
                                    <label for="same_as_inspection" style="display:inline">
                                        Tick if same as above or state
                                    </label>
                                    </span>
                            </div>
                        </div>


                        <!-- Bid Opening Date -->


                        <div class="control-group <?=(in_array('bid_openning_date', $requiredfields) || in_array('bid_openning_date_time', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid opening date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_openning_date'])? display_date( $formdata['bid_openning_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_openning_date" name="bid_openning_date" data-date="<?=(!empty($bid_opening_date )? $bid_opening_date : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker bid_openning_date" type="text" value="<?=!empty($formdata['bid_openning_date']) ?$formdata['bid_openning_date']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_openning_date').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                                <div class="input-append bootstrap-timepicker-component">

                                    <input id="bid_openning_date_time" name="bid_openning_date_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['bid_openning_date']) ?$formdata['bid_openning_date']:'' ?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_openning_date_time').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                            </div>
                        </div>


                        <!-- inspect Opening Date -->


                        <div class="control-group <?=(in_array('inspect_openning_date', $requiredfields) || in_array('inspect_openning_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Inspection Start date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['inspect_openning_date'])? display_date( $formdata['inspect_openning_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="inspect_openning_date" name="inspect_openning_date" data-date="<?=(!empty($inspectionstart_date)? $inspectionstart_date : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker inspect_openning_date" type="text" value="<?=!empty($formdata['inspect_openning_date']) ?$formdata['inspect_openning_date']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_openning_date').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                                <div class="input-append bootstrap-timepicker-component">

                                    <input id="inspect_openning_date_time" name="inspect_openning_date_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['bid_openning_date']) ?$formdata['bid_openning_date']:'' ?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                    <button type="button" onClick="javascript:$('#inspect_openning_date_time').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                            </div>
                        </div>


                        <!-- inspect Close Date -->


                        <div class="control-group <?=(in_array('inspect_close_date', $requiredfields) || in_array('inspect_close_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Inspection Close date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['inspect_close_date'])? display_date( $formdata['inspect_close_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="inspect_close_date" name="inspect_close_date" data-date="<?=(!empty($inspectionclose_date)? $inspectionclose_date : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker inspect_close_date" type="text" value="<?=!empty($formdata['inspect_close_date']) ?$formdata['inspect_close_date']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#inspect_close_date').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                                <div class="input-append bootstrap-timepicker-component">

                                    <input id="inspect_close_date_time" name="inspect_close_date_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['inspect_close_date']) ?$formdata['inspect_close_date']:'' ?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                    <button type="button" onClick="javascript:$('#inspect_close_date_time').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                            </div>
                        </div>

                        <!-- Bid Evaluation Period -->

                        <div class="control-group <?=(in_array('bid_evaluation_from', $requiredfields) || in_array('bid_evaluation_to', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid evaluation period:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_evaluation_from'])? display_date( $formdata['bid_evaluation_from']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_evaluation_from" name="bid_evaluation_from" data-date="<?=(!empty($bidevaluation_from)? $bidevaluation_from   : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" placeholder="From" class="m-ctrl-medium date-picker bid_evaluation_from" type="text" value="<?=!empty($formdata['bid_evaluation_from']) ?$formdata['bid_evaluation_from']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_evaluation_from').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                                <div class="input-append date date-picker" data-date="<?=(!empty($bidevaluation_to)? $bidevaluation_to : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_evaluation_to" name="bid_evaluation_to"  placeholder="To" data-date="<?=(!empty($bidevaluation_to)? $bidevaluation_to : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker bid_evaluation_to" type="text" value="<?=!empty($formdata['bid_evaluation_to']) ?$formdata['bid_evaluation_to']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_evaluation_to').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                            </div>
                        </div>



                        <!-- Display of BEB Notice -->

                        <div class="control-group <?=(in_array('display_of_beb_notice', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Display of BEB notice:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($beb_notice)? $beb_notice : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="display_of_beb_notice" name="display_of_beb_notice" data-date="<?=(!empty($beb_notice)? $beb_notice : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker display_of_beb_notice" type="text" value="<?=!empty($formdata['display_of_beb_notice']) ?$formdata['display_of_beb_notice']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#display_of_beb_notice').attr('value','');"><i class='fa fa-refresh'></i> </button>

                                </div>
                            </div>
                        </div>



                        <!-- Date of Contract Signature -->

                        <div class="control-group <?=(in_array('contract_award_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contract Signature date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['contract_award_date'])? display_date( $formdata['contract_award_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="contract_award_date" name="contract_award_date" data-date="<?=(!empty($contractsgnature_date)? $contractsgnature_date : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker contract_award" type="text" value="<?=!empty($formdata['contract_award_date']) ? $formdata['contract_award_date']:'' ?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#contract_award_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                                </div>
                            </div>
                        </div>
						
						
						&nbsp; &nbsp;
						
                        <!-- Non Refundable Fee  -->

                        <div class="control-group <?=(in_array('non_refundable_fee', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Non Refundable Fee :<span>*</span></label>
                            <div class="controls">
                                <div class="input-append ">
                                    <input id="non_refundable_fee" name="non_refundable_fee"   class="m-ctrl-medium   non_refundable_fee" type="text" value="<?=!empty($formdata['non_refundable_fee']) ?$formdata['non_refundable_fee']:'' ?>" />
                                       <select class="input-small   currency" data-placeholder="Currency" id="currency" name="currency" tabindex="1">
											   <?php
												foreach($cur as $record{
												  ?>
												  <option><?php print_r($record['title']); ?> </option>
												  <?php
												  }
												  ?>
									    </select>   

							  </div>
                            </div>
                        </div>
						









                        <div class="row-fluid">
                            <button type="submit" name="save" value="save" class="btn blue"> Save</button>
                            &nbsp; &nbsp;&nbsp;
                            <button type="reset" name="cancel" value="cancel" class="btn"> Clear </button>
                        </div>

                    </div>

            </div>
            </form>
        </div>
    </div>
