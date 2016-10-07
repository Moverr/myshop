<? if(empty($requiredfields)) $requiredfields = array();?>
<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;<?=(!empty($form_title)? $form_title : 'User details') ?></h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body">
        <!-- BEGIN FORM-->
        <form id="bid-invitation-approval-form" action="<?=base_url() . 'public_holiday/save_holiday' . ((!empty($b))? '/b/'.$b : '' ) . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
            	
          <?php if(!empty($i)): ?>
                    	<input name="editid" value="<?=$i?>" type="hidden" />
<?php endif; ?>
                
                <div class="control-group">
                    <label class="control-label">Title: <?=text_danger_template('*')?></label>
                    <div class="controls">
                        <input required="" value="<?php if(!empty($formdata['title'])) echo htmlspecialchars_decode($formdata['title'], ENT_QUOTES);?>" id="title" name="title" type="text" class="">
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label">Date: <?=text_danger_template('*')?></label>
                    <div class="controls">
                        <input required="" value="<?php if(!empty($formdata['holiday_date'])) echo $formdata['holiday_date'];?>" id="holidaydate" data-date-format="yyyy-mm-dd" name="holiday_date" type="text" class="m-ctrl-medium date-picker input-small from_date">
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label">Description:</label>
                    <div class="controls">


                        <textarea   name="description" id="editor0" class="form-control ckeditor message1" >
                            <?php if(!empty($formdata['description'])) echo htmlspecialchars_decode($formdata['description'], ENT_QUOTES);?>
                        </textarea>

                        <script>
                            $(document).ready(function(){
                                CKEDITOR.replace( 'editor0',
                                    {
                                        filebrowserBrowseUrl : '<?=base_url()?>kcfinder/browse.php?type=files',
                                        filebrowserImageBrowseUrl : '<?=base_url()?>kcfinder/browse.php?type=images',
                                        filebrowserFlashBrowseUrl : '<?=base_url()?>kcfinder/browse.php?type=flash',
                                        filebrowserUploadUrl : '<?=base_url()?>kcfinder/upload.php?type=files',
                                        filebrowserImageUploadUrl : '<?=base_url()?>kcfinder/upload.php?type=images',
                                        filebrowserFlashUploadUrl : '<?=base_url()?>kcfinder/upload.php?type=flash'
                                    });




                            });

                        </script>
                    </div>
                </div>
                    
                
                                            
            <div class="form-actions">
                <button id="save-public-holiday" type="submit" name="save_public_holiday" value="save" class="btn  ">
                	  Save public holiday
                </button>
				  <button type="reset" name="save_addenda" value="save" class="btn  ">
                	  Reset
                </button>
                <button type="button" name="cancel" value="cancel" class="btn" onClick="javascript:location.href='<?=base_url();?>public_holiday/lists/'; ">
				  Cancel</button>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>