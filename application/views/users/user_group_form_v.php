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
        <form action="<?=base_url() . 'admin/save_user_group' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="control-group">
                <label class="control-label">User group name <span>*</span></label>
                <div class="controls">
                    <input type="text" name="groupname" value="<?=(!empty($formdata['groupname'])? $formdata['groupname'] : '' )?>" class="input-xxlarge" required/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Notes</label>
                <div class="controls">
                    <textarea class="input-xxlarge" rows="3" name="comments"><?=(!empty($formdata['comments'])? $formdata['comments'] : '' )?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue"><i class="fa fa-ok"></i> Save</button>
                <button type="button" onClick="javascript:location.href='<?=base_url();?>admin/manage_user_groups' " name="cancel" value="cancel" class="btn"><i class="fa fa-remove"></i> Cancel</button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>