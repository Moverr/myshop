<?php if(empty($requiredfields)) $requiredfields = array(); 

$i = 'insert';
if(!empty($formtype))
{
   
   switch($formtype)
   {
      case 'edit':
      $i  = 'update/'.$record;    
      break;
   }

}


?>


            <div class="row-fluid">
               <div class="span12">
                  <div class="widget box blue" id="form_wizard_1">
                     <div class="widget-title">
                        <h4>
                           <i class="fa fa-reorder"></i> Add Branch  
                        </h4>
                        <span class="tools">
                           <a href="javascript:;" class="fa fa-chevron-down"></a>
                           <a href="javascript:;" class="fa fa-remove"></a>
                        </span>
                     </div>

                     <!-- Add Branch -->
<!-- data-elements="*branch_address<>*shortcode<>*branchname<>*pde"  -->
  
      <div class="widget-body form span-12">
      <!-- Following Javascript Standards -->
    

         <form action="<?=base_url() . 'branches/save' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">


       <?php



    # print_r($active_pdes['page_list']);
     //  print_r($formdata['page_list'][0]['pdeid']);
      # print_r($formdata['page_list']);
       ?>
       <!-- PDE -->
        <div class="control-group <?=(in_array('shop', $requiredfields)? 'error': '')?> ">
                <label class="control-label">SHOP  <span>*</span></label>
                 <div class="controls">
                    <select  name="shop" id="shop"   class="  select2" >

                     <?=get_select_options($active_shops['page_list'], 'id', 'shopname', (!empty($formdata['shop'])? $formdata['shop'] : '' ))?>
                     

                
                    </select>
                 </div>
        </div>

       <!-- end of PDE Selection -->

      <!-- Branch Name -->
       <div class="control-group <?=(in_array('branchname', $requiredfields)? 'error': '')?> ">
                <label class="control-label">Branch Name <span>*</span></label>
                <div class="controls">
                    <input type="text" name="branchname" id="branchname" value="<?=(!empty($formdata['page_list'][0]['branchname'])? $formdata['page_list'][0]['branchname'] : '' )?>" />
                </div>
        </div>

        <!-- Short Code -->
         <div class="control-group <?=(in_array('shortcode', $requiredfields)? 'error': '')?> ">
                <label class="control-label">Short Code <span>*</span></label>
                <div class="controls">
                    <input type="text" name="shortcode"  id="shortcode"  value="<?=(!empty($formdata['page_list'][0]['shortcode'])? $formdata['page_list'][0]['shortcode'] : '' )?>" />
                </div>
        </div>

         <!-- Short Code -->
         <div class="control-group <?=(in_array('branch_address', $requiredfields)? 'error': '')?> ">
                <label class="control-label">Address <span>*</span></label>
                <div class="controls">
                    <textarea type="text" style="height:80px; width: 300px;"  class="  branch_address" name="branch_address" id="branch_address" datatype="text"><?=(!empty($formdata['page_list'][0]['address'])? $formdata['page_list'][0]['address'] : '' )?></textarea>
                </div>
        </div>

        <!-- Buttons Ish  using Javascript to deliver progress :: -->
         <div class="control-group">
             <div class="controls">
             <button type="submit" name="save" value="save"  class="btn btn-default" > SAVE</button>
             <button type="reset" name="reset" value="reset" class="btn btn-default" > CLEAR</button>  
             </div>
        </div>               

        <!-- end buttons -->

</form>

  </div>
  </div>
  </div>
            <!-- END PAGE CONTENT-->