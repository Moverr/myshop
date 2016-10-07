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
        <form action="<?=base_url() . 'shops/add' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
          <div class="form_details">
            <div class="user_details">
              <div class="control-group <?=(in_array('shopname', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Shop Name <span>*</span></label>
                    <div class="controls">
                        <input type="text" name="shopname" value="<?=(!empty($formdata['shopname'])? $formdata['shopname'] : '' )?>" class="input-xxlarge" />
                        <?=(in_array('shopname', $requiredfields)? '<span class="help-inline">Please enter Shop  name</span>': '')?>
                    </div>
                </div>
                <div class="control-group  <?=(in_array('address', $requiredfields)? 'error': '')?> ">
                    <label class="control-label">Address* </label>
                    <div class="controls">
                        <textarea name="address" value="<?=(!empty($formdata['address'])? $formdata['address'] : '' )?>" class="input-xxlarge"  ><?=(!empty($formdata['address'])? $formdata['address'] : '' )?></textarea>   
                         <?=(in_array('address', $requiredfields)? '<span class="help-inline">Please enter Address </span>': '')?>
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