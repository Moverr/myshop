<?php if(empty($requiredfields)) $requiredfields = array();


 

?>
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
        <form action="<?=base_url() . 'items/additem' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
          <div class="form_details">
            <div class="user_details">

             <div class="control-group <?=(in_array('category', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Category </label>
                    <div class="controls">
                        <select class="input-xxlarge m-wrap" name="category" tabindex="1">
                            <?=get_select_options($category['page_list'], 'id', 'category', (!empty($formdata['category'])? $formdata['category'] : '' ))?>
                        </select>
                          <?=(in_array('category', $requiredfields)? '<span class="help-inline">Please Select Category </span>': '')?>

                    </div>
                </div>

                <!-- Item Name -->
              <div class="control-group <?=(in_array('item', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Item Name <span>*</span></label>
                    <div class="controls">
                        <input type="text" name="item" value="<?=(!empty($formdata['item'])? $formdata['item'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('item', $requiredfields)? '<span class="help-inline">Please enter Item  name</span>': '')?>
                    </div>
                </div>

            <!-- Item Abbreviation -->
            <div class="control-group <?=(in_array('abbreviation', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Abbreviation <span>*</span></label>
                    <div class="controls">
                        <input type="text" name="abbreviation" value="<?=(!empty($formdata['abbreviation'])? $formdata['abbreviation'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('abbreviation', $requiredfields)? '<span class="help-inline">Please enter Abbreviation </span>': '')?>
                    </div>
                </div>


                <div class="control-group  <?=(in_array('details', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Details * </label>
                    <div class="controls">
                        <textarea name="details" value="<?=(!empty($formdata['details'])? $formdata['details'] : '' )?>" class="input-xxlarge"  ><?=(!empty($formdata['details'])? $formdata['details'] : '' )?></textarea>   
                         <?=(in_array('details', $requiredfields)? '<span class="help-inline">Please enter Details </span>': '')?>
                    </div>
                </div>
                
                
            
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue">  Save </button>
                <button type="reset" name="cancel" value="cancel" class="btn"> Clear </button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>