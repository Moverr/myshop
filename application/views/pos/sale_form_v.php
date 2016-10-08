<?php if(empty($requiredfields)) $requiredfields = array();?>
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Customer Details : </h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body">
        <!-- BEGIN FORM-->
        <form action="<?=base_url() . 'stock/add_stock' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
          <div class="form_details">
            <div class="user_details">


            <!-- FInancial Year --> 
             <div class="control-group <?=(in_array('start_year', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Date of Sale :  </label>
                    <div class="controls">
                       <input class="  date-picker" id="start_year" name="start_year"  type="text" value="<?php if(!empty($formdata['start_year'])) echo $formdata['start_year'];?>" /> 

                           <?=(in_array('item', $requiredfields)? '<span class="help-inline">Please Enter Financial Year </span>': '')?>


                    </div>
                </div>

               


            <!-- Purchase No -->
              <div class="control-group <?=(in_array('purchase_no', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Order No # <span>*</span></label>
                    <div class="controls">
                        <input type="text" class="" name="purchase_no" value="<?=(!empty($formdata['purchase_no'])? $formdata['purchase_no'] : '' )?>" class="input-xlarge" />
                        <?=(in_array('purchase_no', $requiredfields)? '<span class="help-inline">Please enter Purchase Number</span>': '')?>
                    </div>
                </div>


                 <!-- Purchase No -->
              <div class="control-group <?=(in_array('purchase_no', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Customer Names <span>*</span></label>
                    <div class="controls">
                        <input type="text" class="" name="purchase_no" value="<?=(!empty($formdata['purchase_no'])? $formdata['purchase_no'] : '' )?>" class="input-xlarge" />
                        <?=(in_array('purchase_no', $requiredfields)? '<span class="help-inline">Please enter Purchase Number</span>': '')?>
                    </div>
                </div>


   <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;Item Details : </h4>
         
    </div>

                 <!-- Purchase No -->
              <div class="control-group <?=(in_array('purchase_no', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Select Item <span>*</span></label>
                    <div class="controls" style="width:100%;">
                    <select  class="input-medium chosen stocked_item" style="margin-top:40px;">
                    <?=get_select_options($items['page_list'],'id','name',''); ?>
                    </select>
                        
                       
                         <input type="text" class="input-small numbersonly item_quantity" style="margin-top:-21px;"  id="item_quantity" name="item_quantity" value="" placeholder="Quantity"  />
                         
                       
                           <input type="text" name="unitselling_price"
                                   value="<?= (!empty($formdata['unitselling_price']) ? addCommas($formdata['unitselling_price'], 0) : '') ?>"
                                   class="input-small numbersonly unitselling_price"  style="margin-top:-21px;"
                                     placeholder="Selling Price" id="unitselling_price"   />
                            
                                
                              <input class=" input-small numbersonly rate unitselling_price_exchange_rate " name="unitselling_price_exchange_rate" placeholder="Exchange rate" type="text" value="<?=(!empty($formdata['unitselling_price_exchange_rate'])? addCommas($formdata['unitselling_price_exchange_rate'], 1) : '' )?>"
                               style="<?=(empty($formdata['unitselling_price_currency']) ||  $formdata['unitselling_price_currency'] ==1 ) ? "display:none;":""  ?> margin-top:-21px;" id="unitselling_price_exchange_rate"
                              
                               />
                              
                           
                            <select id="unitselling_price_currency" class="input-small m-wrap" style="margin-top:-21px;" 
                                    name="unitselling_price_currency">
                                <?= get_select_options($currencies, 'id', 'title', (!empty($formdata['unitselling_price_currency']) ? $formdata['unitselling_price_currency'] : 1)) ?>
                            </select>

                           <button type="button" style="margin-top:-21px;"  class="btn  add_sale">ADD</button>

                    
                    </div>
                    <div class="alert controls stock_details" >
                    SOMETHING TO SHOW 
                    </div>
                </div>






                
            
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue"><i class="fa fa-ok"></i> Save</button>
                <button type="submit" name="cancel" value="cancel" class="btn"><i class="fa fa-remove"></i> Cancel</button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>


<script type="text/javascript">
$(function(){

  //$( "#dialog-message" ).dialog();
var item_id = 0;
var quantity = 0;
var selling_px = 0;
var exchange_rate = 0;
var reserve_price = 0;
var reserve_price_exchange_rate = 0;
var reserve_price_currency = 1;
var available_stock = "";

var item_quantity = '';
var unitselling_price = '';
var total_unit_sellingpx = '';

function open_dialog(title,msg){

    $( "#dialog" ).dialog({
          modal: true,
          buttons: {
          
          Ok: function() {

              $( this ).dialog( "close" );
            }
          }
        }); 

           $(".ui-dialog-title").html(title);
           $(".ui-dialog-content p").html(msg);
    }



    $(".add_sale").click(function(){
      if(item_id == 0 )
      {
         open_dialog("Sales Information ","Please Select an Item and Add all the needed Quantity, Seling Price before you add a sale."); 
        return;
      }

      // Get the quantity
      item_quantity = $("#item_quantity").val();
      if(item_quantity.length <= 1){
          open_dialog("Sales Information ","Please Enter the Item Quantity"); 
          $("#item_quantity").focus();
      }

      // Unit Selling Price 
      unitselling_price = $("#unitselling_price").val();         
      if(unitselling_price.length <= 1){
          open_dialog("Sales Information ","Please Enter Unit Selling Price "); 
          $("#item_quantity").focus();
      }

       unitselling_price_exchange_rate = 1;

       //If Currence insnt in UGX 
       if(cur_id > 1)
       {
            unitselling_price_exchange_rate = $("#unitselling_price_exchange_rate").val();
            if(unitselling_price_exchange_rate <= 1 || unitselling_price_exchange_rate === undefined){
                 
                 open_dialog("Sales Information ","Please Enter the Exchange Rate"); 
                 $("#unitselling_price_exchange_rate").focus();
                
                return;
            }


       }



       //Selling Price : should not be greater than  available stock in the 
       if(available_stock > 0 )
       {
          //Check Quantity
          if(available_stock < item_quantity )
          {
           open_dialog("Stock Information ","Availalble Stock "+available_stock+" is Less Than the Entered Quantity "+item_quantity);   
           return;         
          }
          //Check Reseve Price 
          total_reserve_price = (reserve_price_currency > 1) ? (reserve_price * reserve_price_exchange_rate )  : reserve_price ;
 
           
          // Total Unit Selling Px
         total_unit_sellingpx = (cur_id > 1) ? (unitselling_price * unitselling_price_exchange_rate )  : unitselling_price ;

         if(total_unit_sellingpx >  total_reserve_price ){
             open_dialog("Sales Information "," Total Selling Price "+total_unit_sellingpx+" UGx is Greater Than the Total Reserve Price "+total_reserve_price+" UGx <br/> <strong>NOTE</strong> These amounts are changed to Uganda Shillings based on the exchange rates set ");   
           return;   
         }



          //var total_reserve_px = 


        
       }
       else
       {
           open_dialog("Stock Information ","Please Re-Stock this Item"); 
                 $("#unitselling_price_exchange_rate").focus();
            return;
       }
        

      // alert(unitselling_price_exchange_rate);
      // get selling price

      //get the exchange rate

      
    });


      
        
    $(".stocked_item").change(function(){
           
            //Get the ID of the item
             item_id = $(this).val();
             $(".stock_details").fadeIn("fast");

            $(".stock_details").fadeIn('slow',function(){
                $(".stock_details").html("Proccessing ... ");
             //   open_dialog("Point of Sale",'Processsing .... ');
             form_data = {};
             form_data['item_id'] = item_id;

               url = getBaseURL()+"stock/get_stock_detail";

               $.ajax({
                    url:  url,
                     type: 'POST',
                    data: form_data,
                    success: function(data, textStatus, jqXHR){
                        
                        console.log(data);
                         if(data==0 )
                         {
                              open_dialog("Stock Information "," There are no records found  in the stock for this item "); 
                              $(".stock_details").fadeOut("fast");
                             return;
                         }
                         server_response  = JSON.parse(data); 
                         console.log(server_response);

                         available_stock = (server_response.stock_added - server_response.stock_removed);
                         reserve_price_currency = server_response.reserve_price_currency;
                         reserve_price_exchange_rate = server_response.reserve_price_exchange_rate;
                         reserve_price = server_response.reserve_price;

                         var item_details = "<ul>"+
                                            "<li> Item Name </li>"+
                                            "<li> Available Stock "+(server_response.stock_added - server_response.stock_removed)+" </li>"+
                                            "<li> Unit Measure "+(server_response.unit_measure)+"</li>"+
                                            "<li> Selling Price "+(server_response.unit_selling_price*server_response.unit_selling_price_exhange_rate)+" Ugx </li>"+

                                             "<li> Reserve Price "+(server_response.reserve_price*server_response.reserve_price_exchange_rate)+" Ugx </li>"+

                                         
                                            "</ul>";

                                $(".stock_details").html(item_details);

                           
                         
                         
                    },
                    error:function(data , textStatus, jqXHR)
                    {
                        open_dialog("Ajax Error","Server Side Error <br/> Contact System Administrator");

                        console.log(data);
                    return 0;
                    }
                });



            });

            //fetch the stock data of the item via json

         });

        var cur_id  = 1;
         $("#unitselling_price_currency").change(function () {
                                     
                                  cur_id  = $(this).val();
                                    if(cur_id == 1)
                                    $(".unitselling_price_exchange_rate").val("0").fadeOut('fast');
                                    else
                                    $(".unitselling_price_exchange_rate").fadeIn('fast');
                                    });
        



        $("#reserve_price_currency").change(function () {
                                     
                                    var cur_id = $(this).val();
                                    if(cur_id == 1)
                                    $(".reserve_price_exchange_rate").val("").fadeOut('fast');
                                    else
                                    $(".reserve_price_exchange_rate").fadeIn('fast');
                                    });


     

        $(".date-picker2").datepicker( {
            format: " yyyy", // Notice the Extra space at the beginning
            viewMode: "years",
            minViewMode: "years"
        }).on('changeDate', function (ev) {
      if($(this).attr('id') == 'start_year'){
        var dateParts = ev.date.toString().split(' ');
        $("#end_year").val(parseInt(dateParts[3]) + 1);
      }     
    });


    })
</script>

