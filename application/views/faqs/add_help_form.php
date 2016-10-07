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
        <form action="<?=base_url() . 'faqs/add_help' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="control-group">
                <label class="control-label">Help Topic<span>*</span></label>
                <div class="controls">
                  <input type="text" name="topic" value="<?=(!empty($formdata['topic'])? $formdata['topic'] : '' )?>" class="input-xxlarge" required/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Help Topic Header</label>
                <div class="controls">
                  <input type="text" name="header" value="<?=(!empty($formdata['header'])? $formdata['header'] : '' )?>" class="input-xxlarge" required/>
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
            <div class="control-group">
                <label class="control-label">Help Description <span>*</span></label>
                <div class="controls">
                <textarea id="mytextarea" name="description" value="<?=(!empty($formdata['description'])? $formdata['description'] : '' )?>"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="save" value="save" class="btn blue"><i class="fa fa-ok"></i> Save</button>
                <button type="RESET" name="cancel" value="cancel" class="btn"><i class="fa fa-remove"></i> CLEAR</button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>