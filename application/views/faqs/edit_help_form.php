<? if(empty($requiredfields)) $requiredfields = array();?>
<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;<?=(!empty($form_title)? $form_title : 'Help Section Details') ?></h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body">
        <!-- BEGIN FORM-->
        <form action="<?=base_url() . 'faqs/edit_help_section' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
        	<div class="form_details">
       		  <div class="user_details">
            	<div class="control-group <?=(in_array('faq_topic', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Help Section Topic <span>*</span></label>
                    <div class="controls">
                        <input type="text" name="topic" value="<?=(!empty($formdata['faq_topic'])? $formdata['faq_topic'] : '' )?>" class="input-xlarge" />
                        <?=(in_array('faq_topic', $requiredfields)? '<span class="help-inline">Specify Help Topic</span>': '')?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Help Section Header</label>
                    <div class="controls">
                        <input type="text" name="header" value="<?=(!empty($formdata['faq_header'])? $formdata['faq_header'] : '' )?>" class="input-xlarge" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Help Description Image</label>
                        <div class="controls">
                            <div data-provides="fileupload" class="fileupload fileupload-new"><input type="hidden">
                                <div class="input-append">
                                    <div class="uneditable-input">
                                        <i class="icon-file fileupload-exists"></i>
                                        <span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                        <span class="fileupload-new">Select file</span>
                                        <span class="fileupload-exists">Change</span>
                                        <input type="file" class="default" name="userfile" id="userfile">
                                    </span>
                                    <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="control-group <?=(in_array('faq_description', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Help Section Description</label>
                    <div class="controls">
                        <textarea name="description" id="mytextarea" style="width:100%"><?=(!empty($formdata['faq_description'])? $formdata['faq_description'] : '' )?></textarea>
                        <?=(in_array('faq_description', $requiredfields)? '<span class="help-inline">Enter help description</span>': '')?>
                    </div>
                </div>
       		  </div>
            
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue"><i class="fa fa-ok"></i> Save</button>
                <a  type="button" name="cancel" value="cancel" class="btn" href="<?=base_url();?>faqs/list_all_help"><i class="fa fa-remove"></i> Cancel</a>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>