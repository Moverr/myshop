<div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;<?=(!empty($form_title)? $form_title : 'User group permissions') ?></h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body">
        <!-- BEGIN FORM-->
        <form id="form1" name="form1" method="post" action="<?php echo base_url();?>admin/update_permissions" class="form-horizontal">
		<?php if(!empty($all_permissions)){ ?>
				<div class="permissions_wrapper">
                	<?php
						$counter = 0;
						$oldsection = "";
						
						print '<ul class="permissions">';
						
						foreach($all_permissions AS $row)
						{
							$section = $row['section'];
							  
							if($section != $oldsection)
							{
								if($oldsection != '') print '</ul>';
								
								print '<li class="permission_section"><div><a href="javascript:void(0)">'. $section .'</a></div><ul>';
							}
							  
							  
							print "<li><span><input class='check_permission' name='permissions[]' id='permission_".$row['id']."' type='checkbox' value='".$row['id']."'";
								
							if(in_array($row['id'], $permissions_list)) print " checked";
							
							print "/></span><span class='permission' style='font-size: 13px;' width='99%' nowrap>".$row['permission']."</span></li>";
							  
							if($counter == (count($all_permissions) - 1)) echo "</ul>";
								
							$oldsection = $row['section'];
							$counter++;
						} 
					
						print '</ul>';
						
						if(!empty($i) )
							print '<div class="form-actions">'.
							  	  '<input type="hidden" name="editid" value="'. decryptValue($i) .'" />'.
                			  	  '<button type="submit" name="updatepermissions" value="save" class="btn blue">'.
								  '<i class="fa fa-ok"></i> Update permissions</button>&nbsp;&nbsp;'.
                			  	  '<button type="submit" name="cancel" value="cancel" class="btn"><i class="fa fa-remove"></i> Cancel</button>'.
            				  	  '</div>';
					?>
                    
                </div>
		<?php
			}else {
				echo 'There are no permissions accessed by '. (!empty($groupdetails['groupname'])? $groupdetails['groupname'] : 'the user group.' );
			}
		?>
        </form>
    	<!-- END FORM-->
	</div>
</div>