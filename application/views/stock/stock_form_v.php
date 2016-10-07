<?php if(empty($requiredfields)) $requiredfields = array();?>
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;<?=(!empty($form_title)? $form_title : 'User details') ?></h4>
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
             <div class="control-group <?=(in_array('date_of_stock', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Date of Stock :  </label>
                    <div class="controls  ">
                       <input class=" input-append  input-xxlarge  date-picker" id="date_of_stock" name="date_of_stock"  type="text" value="<?php if(!empty($formdata['date_of_stock'])) echo $formdata['date_of_stock'];?>" /> 
<span class="add-on"><i class="fa fa-calendar"></i></span>
                           <?=(in_array('date_of_stock', $requiredfields)? '<span class="help-inline">Please Enter Date of Stock </span>': '')?>
 

                    </div>
                </div>

                <!-- Item Id -->
                <div class="control-group <?=(in_array('item', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Item :  </label>
                    <div class="controls">
                        <select class="input-xxlarge m-wrap item stock_information" name="item" tabindex="1">
                            <?=get_select_options($items['page_list'], 'id', 'name', (!empty($formdata['item'])? $formdata['item'] : '' ))?>
                        </select>
                          <?=(in_array('item', $requiredfields)? '<span class="help-inline">Please Select Item </span>': '')?>

                    </div>
                </div>



            <!-- Purchase No -->
              <div class="control-group <?=(in_array('purchase_no', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Purchase  # <span>*</span></label>
                    <div class="controls">
                     

                        <input type="text" class="input-xxlarge" name="purchase_no" value="<?=(!empty($formdata['purchase_no'])? $formdata['purchase_no'] : '' )?>" class="input-xxlarge" />

                        <?=(in_array('purchase_no', $requiredfields)? '<span class="help-inline">Please enter Purchase Number</span>': '')?>
                    </div>
                </div>



            <!-- Unit Measure -->
              <div class="control-group <?=(in_array('unit_measure', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Unit Measure <span>*</span></label>
                    <div class="controls">
                        <input type="text" class="input-xxlarge" name="unit_measure" value="<?=(!empty($formdata['unit_measure'])? $formdata['unit_measure'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('unit_measure', $requiredfields)? '<span class="help-inline">Please Select Unit Measure</span>': '')?>
                    </div>
                </div>


            <!-- Purchase No -->
              <div class="control-group <?=(in_array('number_of_items', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Number of Items <span>*</span></label>
                    <div class="controls">
                        <input type="text" class="numbersonly input-xxlarge" name="number_of_items" value="<?=(!empty($formdata['number_of_items'])? $formdata['number_of_items'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('number_of_items', $requiredfields)? '<span class="help-inline">Please enter Number of Items </span>': '')?>
                    </div>
                </div>


                  <!-- Unit Selling Px No -->
                      <!-- Estimated Amounts -->
                    <div
                        class="control-group <?= (in_array('unitselling_price', $requiredfields) ? 'error' : '') ?> <?= (in_array('unitselling_price_currency', $requiredfields) ? 'error' : '') ?> ">
                        <label class="control-label"> Selling Price <span>*</span></label>

                        <div class="controls">
                            <input type="text" name="unitselling_price"
                                   value="<?= (!empty($formdata['unitselling_price']) ? addCommas($formdata['unitselling_price'], 0) : '') ?>"
                                   class="input-medium numbercommas"
                                       />
                            
                                
                              <input class=" input-medium numbercommas rate unitselling_price_exchange_rate " name="unitselling_price_exchange_rate" placeholder="Exchange rate" type="text" value="<?=(!empty($formdata['unitselling_price_exchange_rate'])? addCommas($formdata['unitselling_price_exchange_rate'], 1) : '' )?>"
                               style="<?=(empty($formdata['unitselling_price_currency']) ||  $formdata['unitselling_price_currency'] ==1 ) ? "display:none;":""  ?>"
                              
                               />
                              
                           
                            <select id="unitselling_price_currency" class="input-small m-wrap"
                                    name="unitselling_price_currency">
                                <?= get_select_options($currencies, 'id', 'title', (!empty($formdata['unitselling_price_currency']) ? $formdata['unitselling_price_currency'] : 1)) ?>
                            </select>

                              <?=(in_array('unitselling_price', $requiredfields)? '<span class="help-inline">Please enter Selling Price </span>': '')?>

                        </div>
                    </div>



                               <!-- Estimated Amounts -->
                    <div
                        class="control-group <?= (in_array('reserve_price', $requiredfields) ? 'error' : '') ?> <?= (in_array('reserve_price_currency', $requiredfields) ? 'error' : '') ?> ">
                        <label class="control-label"> Reserve Price <span>*</span></label>

                        <div class="controls">
                            <input type="text" name="reserve_price"
                                   value="<?= (!empty($formdata['reserve_price']) ? addCommas($formdata['reserve_price'], 0) : '') ?>"
                                   class="input-medium numbercommas"
                                       />
                            
                                
                              <input class=" input-medium numbercommas rate reserve_price_exchange_rate " name="reserve_price_exchange_rate" placeholder="Exchange rate" type="text" value="<?=(!empty($formdata['reserve_price_exchange_rate'])? addCommas($formdata['reserve_price_exchange_rate'], 1) : '' )?>"
                               style="<?=(empty($formdata['reserve_price_currency']) ||  $formdata['reserve_price_currency'] ==1 ) ? "display:none;":""  ?>"
                              
                               />
                              
                           
                            <select id="reserve_price_currency" class="input-small m-wrap"
                                    name="reserve_price_currency">
                                <?= get_select_options($currencies, 'id', 'title', (!empty($formdata['reserve_price_currency']) ? $formdata['reserve_price_currency'] : 1)) ?>
                            </select>

                             <?=(in_array('unitselling_price', $requiredfields)? '<span class="help-inline">Please enter Reserve  Price </span>': '')?>

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
         $("#unitselling_price_currency").change(function () {
                                     
                                    var cur_id = $(this).val();
                                    if(cur_id == 1)
                                    $(".unitselling_price_exchange_rate").val("0").fadeOut('fast');
                                    else
                                    $(".unitselling_price_exchange_rate").fadeIn('fast');
                                    });



        $("#reserve_price_currency").change(function () {
                                     
                                    var cur_id = $(this).val();
                                    if(cur_id == 1)
                                    $(".reserve_price_exchange_rate").val("0").fadeOut('fast');
                                    else
                                    $(".reserve_price_exchange_rate").fadeIn('fast');
                                    });


     

        $(".date-picker2").datepicker( {
            format: " yyyy", // Notice the Extra space at the beginning
            viewMode: "years",
            minViewMode: "years"
        }).on('changeDate', function (ev) {
      if($(this).attr('id') == 'date_of_stock'){
        var dateParts = ev.date.toString().split(' ');
        $("#end_year").val(parseInt(dateParts[3]) + 1);
      }     
    });


    })
</script>