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
        <form action="<?=base_url() . 'items/addcategory' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
          <div class="form_details">
            <div class="user_details">

            <!-- Item Category  -->
              <div class="control-group <?=(in_array('itemcategory', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Category Name <span>*</span></label>
                    <div class="controls">
                        <input type="text" name="itemcategory" value="<?=(!empty($formdata['itemcategory'])? $formdata['itemcategory'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('itemcategory', $requiredfields)? '<span class="help-inline">Please enter category  name</span>': '')?>
                    </div>
                </div>

            <!-- Category Abbreviation -->

               <div class="control-group <?=(in_array('abbreviation', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Abbreviation <span></span></label>
                    <div class="controls">
                        <input type="text" name="abbreviation" value="<?=(!empty($formdata['abbreviation'])? $formdata['abbreviation'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('abbreviation', $requiredfields)? '<span class="help-inline">Please enter Abbreviation </span>': '')?>
                    </div>
                </div>

                <!-- Details -->

                <div class="control-group  <?=(in_array('details', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Details * </label>
                    <div class="controls">
                        <textarea name="details" value="<?=(!empty($formdata['details'])? $formdata['details'] : '' )?>" class="input-xxlarge"  ><?=(!empty($formdata['details'])? $formdata['details'] : '' )?></textarea>   
                         <?=(in_array('details', $requiredfields)? '<span class="help-inline">Please enter Details </span>': '')?>
                    </div>
                </div>
                
                
            
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue">  Save </button>
                <button type="reset" name="cancel" value="cancel" class="btn">  Clear </button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>