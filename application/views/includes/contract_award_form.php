<?php if(empty($requiredfields)) $requiredfields = array();

?>
<div class="widget">
<?php
if (empty($formdata['vi'])) {
    ?>
<script type="text/javascript">

//trigger contract amount and price
function trigger_amount_contracts(beb_contract_price,beb_contract_currency)
{

var prx = beb_contract_price;
var beb_curncy = beb_contract_currency;

alert(beb_contract_price);
  
  console.log("AMOUNT"+prx);
  console.log("CURRENCY"+beb_curncy);

    //Delete ELements 
    $('.contract_prices>table tr td>a').closest('tr').remove();
    if(!$('.contract_prices>table tbody tr').length)
    {
      $('.contract_prices>table').hide();
      $('.contract_prices .alert').show();
    }

    



   
  // Adding options to amount :: 
  $(".contract_currency option").filter(function() {     
      return $(this).text() == beb_contract_currency; 
  }).prop('selected', true);
  
   
  $(".amount").val(prx); 
  $(".addamount").trigger("click");

   
}

function trigger_clear_amounts()
{
  $('.contract_prices>table tr td>a').closest('tr').remove();
    if(!$('.contract_prices>table tbody tr').length)
    {
      $('.contract_prices>table').hide();
      $('.contract_prices .alert').show();
    }

}


$(function(){


   var url  = '<?=base_url()?>contracts/contract_award_form/';
         

    $(".financial_year_selection").change(function(){
      //alert($(this).val());
      var financial_year = $(this).val().trim();      

      if(financial_year.length > 0)
      {
        url += 'financial_year/'+financial_year;
        location.href =url;
      }
      


    })


var scrollers1 = function(){
    $('html, body').animate({scrollTop : 0},800);
     }

    $("#cntractawardform").submit(function (e) {
        status = $("#providerstatus").val();
$(".alert").fadeOut('slow');
if(status == 'undefined')
{
  var pp = $("#procurement-ref-no-contracts").val();
   
  if(pp == null)
  {  
   $(".alert").html("Select Reference Number  ").fadeIn("slow");
   scrollers1();
   e.preventDefault();
    return;  
  }
   
 pp = $("#optionlots").val();

 if(pp  == null)
  {  
   $(".alert").html("Select Lot ").fadeIn("slow");
   scrollers1();
    e.preventDefault();
    return;
  }

  $(".alert").html("Fill Blanks ").fadeIn("slow");
  scrollers1();
    e.preventDefault();
    return;
  

}


$(".alert").fadeOut('slow');

     if(status != 0)
         e.preventDefault();
         if(status == 1){
         alert('Attempt to award a contract to suspended provider');
           //push information
          var getUrl = getBaseURL() + 'bids/procurement_recorddetails_contracts/notification/y';

        getUrl += '/b/get';
        formdata = {proc_id: $("#procurement-ref-no-contracts").val()};


            $.ajax({

                url: getUrl,
                type: 'POST',
                data: formdata,
                     success: function(data)
                     {
                     console.log(data);
                     },
                     error:function(data)
                     {
                     console.log(msg);
                      }

                    });
                }

     })


  })


</script>
<?php } ?>
     <!-- BEGIN FORM-->
     <form action="<?=base_url() . 'contracts/award_contract' . ((!empty($i))? '/i/'.$i : '' ).((!empty($lotid))? '/lotid/'.$lotid : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal" id="cntractawardform">
    <div class="widget-body">
    <!-- Procurement Record view -->
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Procurement Record</h4>
    </div>

     <?php if(!empty($success))
     {
      print format_notice('SUCCESS: Contract Saved Successfully');    
     } 
     ?>
    
    <!-- Financial Year --> 

      <div class="control-group">
            <label class="control-label">Financial Year <span>*</span></label>
            <div class="controls">
            <select class="chosen financial_year_selection " name="financial_year">
               <?=get_select_options($financial_years, 'fy', 'label', (!empty($current_financial_year)? $current_financial_year : '' ))?> 
        
            </select>
            </div>
      </div>



    <?php if(empty($procurement_plan_entries)): ?>
    <?php print format_notice('ERROR: There are no procurement entries available for contract award'); ?>
    <?php else: ?>

      <!-- Financial Year -->
    

        <!-- Procurement Ref Number --> 
        <div class="control-group">
            <label class="control-label">Procurement Ref. No <span>*</span></label>
            <div class="controls">             

          <select id="procurement-ref-no-contracts" class="chosen get_beb" name="prefid" tabindex="1">
             <?php
                if(!empty($formdata))
                {
          

                  ?>
                  <option value="<?=$formdata['procurement_details']['procurement_id'].'__'.$formdata['bidinvitation_id'].'__'.$formdata['receiptid']; ?>">
                   <?=$formdata['procurement_details']['procurement_ref_no']; ?>
                   </option>
                  <?php

                }
              else
                {
     
                ?>

          <option selected="selected" disabled> Select Procurement Ref Number</option>
            <?php

                  foreach ($procurement_plan_entries as $key => $record) {
                    # code...
                    ?>
                      <option value="<?=$record['id'].'__'.$record['bidinvitation'].'__'.$record['receiptid']; ?>">
                      <?=$record['procurement_ref_no']; ?>
                      </option>
                    <?php
                  }
                }

            ?>
             </select>
            </div>
        </div>

        <!--
        Procurement Plan Details are Fetched Ajaxly 
        -->

        <div id="procurement_plan_details">
        <!-- Procurement Details --> 
        <?php if(!empty($formdata['procurement_details'])): ?>
              <?php $procurement_details = $formdata['procurement_details']; ?>
                  <div class="control-group">
                      <label class="control-label">Subject of procurement:</label>
                      <div class="controls">
                          <?=(!empty($procurement_details['subject_of_procurement'])? $procurement_details['subject_of_procurement'] : '<i>undefined</i>')?>
                          <input type="hidden" name="procurement_details[subject_of_procurement]" value="<?=$procurement_details['subject_of_procurement']?>" />
                      </div>
                  </div>
                  <!-- Method of Procurement --> 
                  <div class="control-group">
                      <label class="control-label">Method of Procurement:</label>
                      <div class="controls">
                          <?=(!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>')?>
                          <input type="hidden" name="procurement_details[procurement_method]" value="<?=$procurement_details['procurement_method']?>" />
                      </div>
                  </div>
                  <div class="control-group">
                      <label class="control-label">Source of Funding:</label>
                      <div class="controls">
                          <?=(!empty($procurement_details['funding_source'])? $procurement_details['funding_source'] : '<i>undefined</i>')?>
                          <input type="hidden" name="procurement_details[funding_source]" value="<?=$procurement_details['funding_source']?>" />
                      </div>
                  </div>
               <?php endif; ?>
      </div>

    <!-- End Procurement Record view -->


    <!-- Lot Information -->
    <div style="display:none;" class="widget-title lotinfo  ">
        <h4><i class="fa fa-reorder"></i>&nbsp;Lot Information</h4>
    </div><br />

      <div  style="display:none;"  class="control-group lotinfo   ">
        <label class="control-label">Select Lot: <span>*</span></label>
        <div class="controls">
            <select  class="  optionlots" id="optionlots" name="lotid"  >

            </select>

            <div class="bebawarded" id="bebawarded">
            </div>
        </div>
    </div>


    <!-- Contract award details -->
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Contract  details</h4>
    </div><br />


  <!-- Date Signed --> 
  <div class="control-group <?=(in_array('date_signed', $requiredfields)? 'error': '')?>">
            <label class="control-label">Date signed: <span>*</span></label>
        <div class="controls">
          
              <div class="input-append date date-picker" data-date="<?=(!empty($formdata['date_signed'])? custom_date_format('Y-m-d', $formdata['date_signed']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
               
                      <input name="date_signed" data-date="<?=(!empty($formdata['date_signed'])? custom_date_format('Y-m-d', $formdata['date_signed']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days"  id="date_signed" class="m-ctrl-medium date-picker"  type="text" value="<?=(!empty($formdata['date_signed'])? $formdata['date_signed'] : '' )?>">

                      <span class="add-on"><i class="fa fa-calendar"></i></span>
                      
                       <button type="button" onClick="javascript: $('#date_signed').attr('value','');"  ><i class='fa fa-refresh'></i>
                       </button>
           
            </div>

        </div>
  </div>

<!-- Planned Date of COmmencement of Contract --> 
  <div class="control-group  <?=(in_array('commencement_date', $requiredfields)? 'error': '')?> ">
            <label class="control-label">Planned date of commencement of
contract: <span>*</span></label>
            <div class="controls">
              <div class="input-append date date-picker" data-date="<?=(!empty($formdata['commencement_date'])? custom_date_format('Y-m-d', $formdata['commencement_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
        <input name="commencement_date" data-date="<?=(!empty($formdata['commencement_date'])? custom_date_format('Y-m-d', $formdata['commencement_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker" type="text" value="<?=(!empty($formdata['commencement_date'])? $formdata['commencement_date'] : '' )?>" id="start_date">
        <span class="add-on"><i class="fa fa-calendar"></i></span>
                  <button type="button" onClick="javascript: $('#start_date').attr('value','');"  ><i class='fa fa-refresh'></i> </button>
       
        </div>
            </div>
        </div>

        <?php
        #print_r($formdata);
        ?>
 <div class="control-group ">
            <label class="control-label">Duration of contract (no. of days): <span>*</span></label>
            <div class="controls">
                <?php

                if (!empty($formdata['duration'])) {
                    $formdata['days_duration'] = $formdata['duration'];
                }

                ?>
        <!-- Years -->
           <input type="number" id="years" name="years_duration" placeholder="Years"
                       value="<?= (!empty($formdata['years_duration']) ? $formdata['years_duration'] : '') ?>"
                       class="input-small numbersOnly"  /> &nbsp;<span id="errmsg"></span>
             
        <!-- Months -->
           <input type="number" id="months" name="months_duration" placeholder="Months"
                       value="<?= (!empty($formdata['months_duration']) ? $formdata['months_duration'] : '') ?>"
                       class="input-small numbersOnly"  /> &nbsp;<span id="errmsg"></span>
             
        <!-- Days -->
                 <input type="number" id="days" name="duration" placeholder="Days"
                       value="<?= (!empty($formdata['days_duration']) ? $formdata['days_duration'] : '') ?>"
                       class="input-small numbersOnly"  /> &nbsp;<span id="errmsg"></span>


               
             

            </div>
        </div>

    
    <?php
    // print_r($formdata);
    ?>
<div class="control-group <?=(in_array('completion_date', $requiredfields)? 'error': '')?> ">
            <label class="control-label">Planned date of contract
completion: <span>*</span></label>
            <div class="controls">
          <input name="completion_date"class="input-large" type="text" value="<?=(!empty($formdata['completion_date'])? $formdata['completion_date'] : '' )?>" id="end_date" placeholder="Completion Date" readonly>
        <button type="button" onClick="javascript: $('#end_date').attr('value','');" class=" "><i class='fa fa-refresh'></i> </button>
            
    </div>
            </div>
        </div>
    <!-- End contract award view -->

    <!-- Contract amount -->
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Contract amounts</h4>
    </div><br />
    <input type="hidden" name="amounts_str" value="" id="amounts-str" />

    <div class="control-group add_contract_price <?=(in_array('amount', $requiredfields)? 'error': '')?>">
            <label class="control-label">Amount: <span>*</span></label>
            <div class="controls">
              <select class="input-small m-wrap contract_currency " name="currency" required>
                <?=get_select_options($currencies, 'id', 'title', (!empty($formdata['currency'])? $formdata['currency'] : 1 ))?>
              </select>
                <input style="display:none" class=" input-small numbercommas" name="rate" placeholder="Exchange rate" type="text" />
                <div class="input-append">
                    <input class=" input-medium numbercommas amount" name="amount" placeholder="amount" type="text" />
                    <div class="btn-group">
                        <a class="btn addamount ">
                            Add amount
                        </a>
                    </div>
                </div>
                <span class="help-inline">&nbsp;</span>
           </div>
    </div>

    <div class="control-group">
            <label class="control-label">&nbsp;</label>
            <div class="controls contract_prices" style="width:50%">

              <div class="alert alert-info" <?=(!empty($formdata['contract_amount'] )? 'style="display:none"' : '')?>>
                  <button data-dismiss="alert" class="close">×</button>
                    No contract prices entered yet.
                    To add a contract price, select the appropriate currency, enter the amount and click 'Add amount' to add the contract price
                </div>


                <table class="table table-condensed table-striped table-bordered" <?=(!empty($formdata['contract_amount'] )? '' : 'style="display:none"')?>>
                    <thead>
                    <tr>
                      <th style="width:5%"></th>
                        <th style="width:15%">
                            Amount
                        </th>
                        <th style="width:10%" class="hidden-phone">
                            Currency
                        </th>
                        <th style="width:10%" class="right-align-text hidden-phone">
                            X Rate
                        </th>
                        <th style="width:20%; text-align:right" class="right-align-text hidden-phone">
                            Amount in UGX
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                      <?php
                          if(!empty($formdata['contract_amount'])):
                            foreach($formdata['contract_amount'] as $contract_amount)
                {
                  if(is_array($contract_amount)):
                    $contract_amount_arr[0] = $contract_amount['amount'];
                    $contract_amount_arr[1] = $contract_amount['currency_id'];
                    $contract_amount_arr[2] = $contract_amount['xrate'];
                    $contract_amount_arr[3] = $contract_amount['title'];

                  else:
                    $contract_amount_arr = explode('__', $contract_amount);

                  endif;

                  print '<tr>'.
                     '<td style="text-align:center">'.
                     '<a title="Click to remove" href="javascript:void(0);">'.
                     '<i class="fa fa-remove"></i></a>'.
                     '<input type="hidden" name="contract_amount[]" value="'.$contract_amount_arr[0].
                     '__' .$contract_amount_arr[1] .'__'. $contract_amount_arr[2] .'__'. $contract_amount_arr[3] .'" />'.
                     '</td>'.
                     '<td>'.addCommas($contract_amount_arr[0], 0).'</td>'.
                     '<td class="hidden-phone" style="font-size:11px"><strong>'. $contract_amount_arr[3] .'</strong></td>'.
                     '<td class="right-align-text hidden-phone">'.
                     '<input type="hidden" class="curId" value="'. $contract_amount_arr[1] .'" />'.
                     '<span class"number">'. addCommas($contract_amount_arr[2], 0) .'</span></td>'.
                     '<td style="text-align:right" class="right-align-text hidden-phone">'.
                     '<span class"number">'.
                     addCommas(removeCommas($contract_amount_arr[0]) * removeCommas($contract_amount_arr[2]), 0).
                     '</span>'.
                     '</td></tr>';

                }
              endif;
            ?>
                    </tbody>
                </table>
            </div>


             <!-- contract manager -->
                 <div class="control-group  <?=(in_array('contract_manager', $requiredfields)? 'error': '')?>">
                    <label class="control-label"> Contract Manager <span>*</span></label>
                        <div class="controls">
                        <input   name="contract_manager"   class="m-ctrl-medium "  type="text" value="<?=(!empty($formdata['contract_manager']) ? $formdata['contract_manager'] : '')?>">
                        </div>
                </div>
    </div>


<!-- Buttons -->
 <div class="control-group ">

            <div class="controls">
              
              <button type="submit" name="save" value="save" class="btn blue savebtn"> Save</button> 

                       <?php
                  if(!empty($i))
                  {}
                  else{
                   ?>
                <button type="submit" name="savefinish" value="save" class="btn blue savefinish"> Save & Exit</button>      
                      <?php  }     ?>

                      <button type="reset" name="cancel" value="CLEAR" class="btn"> Clear Form </button> 


            </div>

    </div>



    <?php endif; ?>

    </form>
    <!-- END FORM-->

    <!-- End contract amount -->
    </div>

    <script type="text/javascript">


    $(document).ready(function () {
        //called when key is pressed in textbox
        $(".numbersOnly").keypress(function (e) {
            //if the letter is not digit then display error and don't type anything
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                //display error message
                $("#errmsg").html("<span class='text-error'>Digits Only</span>").show().fadeOut("slow");
                return false;
            }
        });
    });
    /*
     $(function(){
     val xc = $("#days").val();
     if(xc >0)
     {
     var date = new Date($("#start_date").val()),
     days = parseInt($("#days").val(), 10);

     if(!isNaN(date.getTime()) && !isNaN(days)){
     date.setDate(date.getDate() + days);

     $("#end_date").val(date.toInputFormat());
     } else {
     $("#end_date").val('');
     }
     }

     }) */
    (function($, window, document, undefined){



var selected_date = '';
 var date = '';
        $("#days").on("change", function () {
          
          
          
          selected_date = $("#start_date").val();
          date = new Date($("#start_date").val()),
          days = parseInt($("#days").val(), 10);            
         

       

        if(!isNaN(date.getTime()) && !isNaN(days)){
          date.setDate(date.getDate() + days);

          $("#end_date").val(date.toInputFormat());
      
       //months
        if(months > 0 )
            date.setMonth(date.getMonth() + months);
      
      
       //years  
     if(!isNaN(years))
        date.setFullYear(date.getFullYear() + years);     
       
                 
       
  
          $("#end_date").val(date.toInputFormat());
      
        } else {           
            $("#end_date").val('');        
          
        }
      });

var months = 0;
      $("#months").on("change", function () {
        var date = '';
      
          selected_date = $("#start_date").val();
          date = new Date($("#start_date").val()),
           months = parseInt($(this).val());
       


        if(!isNaN(months)){
          date.setMonth(date.getMonth() + months);
          
     //years  
     if(!isNaN(years))
          date.setFullYear(date.getFullYear() + years); 
      
      //days
     if(!isNaN(date.getTime()) && !isNaN(days))
          date.setDate(date.getDate() + days);       
      
      
      
  
          $("#end_date").val(date.toInputFormat());
          selected_date =  $("#end_date").val();
        } 

        else {
          
          
            $("#end_date").val('');
                     
        }

      });



var years = 0;



    
      $("#years").on("change", function () {
        var date = '';
       
         
          selected_date = $("#start_date").val();
          date = new Date($("#start_date").val()),
          years = parseInt($(this).val());
       

        //Years
        if(!isNaN(years)){
        date.setFullYear(date.getFullYear() + years); 
      
        //days
         if(!isNaN(days))
          date.setDate(date.getDate() + days);         
      
          //months
           if(months > 0  )
            date.setMonth(date.getMonth() + months);
      
  
          $("#end_date").val(date.toInputFormat());
          selected_date =  $("#end_date").val();
        } 

        else {
          
          
            $("#end_date").val(selected_date);
                     
        }

      });

      Date.prototype.toInputFormat = function() {
         var yyyy = this.getFullYear().toString();
         var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
         var dd  = this.getDate().toString();
         return yyyy + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + (dd[1]?dd:"0"+dd[0]); // padding
      };

      function initDeleteLinks()
      {
        $('.contract_prices>table tr td>a').on('click', function(){
          $(this).closest('tr').remove();
          if(!$('.contract_prices>table tbody tr').length)
          {
            $('.contract_prices>table').hide();
            $('.contract_prices .alert').show();
          }
        });
      }

      function showHideXchangeRate()
      {
        if($('.add_contract_price select').val() == 1)
        {
          $('.add_contract_price input[name="rate"]').hide();
        }
        else
        {
          $('.add_contract_price input[name="rate"]').show();
        }
      }

      function findExistingCurrency(curId)
      {
        var result = false;

        $(".contract_prices>table tbody tr").each(function(){
          if($(this).find('input.curId').val() == curId)
            result = true;
        });

        return result;
      }

      $('.add_contract_price a.btn').click(function(){
          var curId = $('.add_contract_price select').val();
          var amount = $('.add_contract_price input[name="amount"]').val();
          var xchange = ((curId == 1)? 1 : $('.add_contract_price input[name="rate"]').val());
          var curText = $('.add_contract_price select :selected').text();
          var ugxAmount = addCommas((parseInt(xchange.toString().split(',').join("")) * parseInt(amount.split(',').join(""))));

          if(amount == '')
          {
            $('.add_contract_price').addClass('error');
            $('.add_contract_price .help-inline').html('Null amounts are not allowed');
          }
          else if(curId >1 && xchange == '')
          {
            $('.add_contract_price').addClass('error');
            $('.add_contract_price .help-inline').html('Please enter the exchange rate');
          }
          else if(findExistingCurrency(curId))
          {
            $('.add_contract_price').addClass('error');
            $('.add_contract_price .help-inline').html('An amount with the selected currency has already been added');
          }
          else
          {

            $('.add_contract_price').removeClass('error');
            $('.add_contract_price .help-inline').html('');
            $('.contract_prices>table').show();

            $('.contract_prices>table tbody').append('<tr><td style="text-align:center">'+
                                 '<a title="Click to remove" href="javascript:void(0);">'+
                                 '<i class="fa fa-remove"></i></a>'+
                                 '<input type="hidden" name="contract_amount[]" value="'+amount+'__'+curId+'__'+xchange+'__'+curText+'" />'+
                                 '</td>'+
                                 '<td>'+amount+'</td>'+
                                 '<td class="hidden-phone" style="font-size:11px"><strong>'+curText+'</strong></td>'+
                                 '<td class="right-align-text hidden-phone">'+
                                 '<input type="hidden" class="curId" value="'+curId+'" />'+
                                 '<span class"number">'+xchange+'</span>'+
                                 '</td><td style="text-align:right" class="right-align-text hidden-phone">'+
                                 '<span class"number">'+ugxAmount+'</span></td></tr>');

            $('.add_contract_price input[name="amount"]').val('');
            $('.add_contract_price input[name="rate"]').val('');
            $('.contract_prices .alert').hide();
            initDeleteLinks();
          }
        });


        $('.add_contract_price select').change(showHideXchangeRate);

        showHideXchangeRate();

        initDeleteLinks();

    })(jQuery, this, document);
    $(function () {
   //   alert("meoeoeoe");
   
  



      status_level = '';
              <?php
      if(!empty($formdata['prefid']) )
      {

      ?>
      status_level ='edit';



   function fetchlotss(formdata){
    
    console.log(formdata);
    $(".lotinfo").fadeOut('slow');
    var formdta = {};
 
    formdta['bidid'] =  '<?=$formdata['bidinvitation_id']; ?>'; 
    formdta['level'] = 'edit';
    formdta['procurementrefno'] = '<?=$formdata['procurement_details']['procurement_ref_no']?>';
    var getUrl = getBaseURL() + 'receipts/get_beb_lots_ajax';

    $.ajax({
            url: getUrl,
            type: 'POST',
            data: formdta,
            success: function(data)
            {
              <?php    if((!empty($level) && $level == 'editlot') ||  (!empty($formdata['lotid'])) ) {?>

                          fetchlotss(formdata);
                        <?php }    ?>
 

              if(data != 0)
              {
                  $(".optionlots").children().remove().end().append(data);
                  $(".lotinfo").fadeIn('slow');
                //  $(".chosen").trigger("chosen:updated")
              //    $("#optionlots").trigger("change")
                   console.log(data);
              }
              else {

              }
        
            },
            error:function(data)
            {
            console.log(data);
            }
    });


  }


        //date
        var date = new Date($("#start_date").val()),
            days = parseInt($("#days").val(), 10);

        if (!isNaN(date.getTime()) && !isNaN(days)) {
            date.setDate(date.getDate() + days);

            $("#end_date").val(date.toInputFormat());
        } else {
           // $("#end_date").val('');
        }

        //end


        //document.write('<input type="hidden" id="providerstatus" value="0" />');
        var getUrl = getBaseURL() + 'bids/procurement_recorddetails_contracts';

        if ($(this).hasClass('get_beb'))
            getUrl += '/b/get';
        console.log(getUrl);


    

      formdata = {proc_id: <?=$formdata['prefid']; ?>};
      formdata['procurementrefno'] = '<?=$formdata['procurement_details']['procurement_ref_no']?>';
      formdata['bidinvitationid'] = '<?=$formdata['bidinvitation_id']; ?>';
    formdata['receiptid'] = '<?=$formdata['receiptid']; ?>';
     
         <?php
      if(!empty($formdata['lotid']))
      {
      ?>
       formdata['lotid'] = '<?=$formdata['lotid']; ?>'; 
      <?php 
      }
      ?>
      <?php    if(!empty($level) && $level == 'editlot') {?>
 
        <?php } ?>
        var referenceno = $('#procurement-ref-no-contracts option:selected').text();
         //$("#procurement-ref-no-contracts").options[this.selectedIndex].text;
      //  formdata['procurementrefno'] = referenceno;
      //  this.options[this.selectedIndex].text

      console.log(formdata);
     //return;
        $("#sequencenumber").val("");
        $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
        $.ajax({
            url: getUrl,
            type: 'POST',
            data: formdata,
            success: function (msg) {
                $("#procurement_plan_details").html(msg);
                //get the procurement ID ::

                $.ajax({
                    url: getBaseURL() + 'bids/loadprocurementrefno',
                    type: 'POST',
                    data: formdata,
                    success: function (msg) {
                        if (msg != 0) {
                            $("#sequencenumber").removeClass("hidden");
                            $("#sequencenumber").val(msg);
                            console.log(msg);
                        }
                        else {
                            $("#sequencenumber").val("");
                        }
                                             

                    },
                    error: function (data) {
                      console.log("problem");
                    }
                });
                //end of events
            }
        });
        //  alert('<?=$formdata['prefid']; ?>');
        console.log('<?=$formdata['prefid']; ?>');
        //$(".get_beb").val('<?=$formdata['prefid']; ?>'),trigger('change');
        <?php
    }
    ?>

    })
  </script>
