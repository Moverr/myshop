 <!-- BEGIN PAGE CONTENT-->
            
<?php
# created by  mover 
#included into the play : so it would be nice if its plugged in Ajaxly
/*
Check if Pde name Exists in the Db..   server side checks 
*/
 


   $pdetype =  '';
     $details = ''; 
      $pdetypeid = 0;

if((!empty($pdetypes)) && (count($pdetypes) > 0) )
{
   foreach ($pdetypes as $key => $row) {
      # code...
   $pdetype =  $row['pdetype'];
     $details =  $row['details'];
      
              
        $pdetypeid = $row['pdetypeid'];
   }


}

$i = 'insert';
if(!empty($formtype))
{
   
   switch($formtype)
   {
      case 'edit':
      $i  = 'update/'.$pdetypeid;    
      break;
   }

}

?>


            <div class="row-fluid">
               <div class="span12">
                  <div class="widget box blue" id="form_wizard_1">
                     <div class="widget-title">
                        <h4>
                           <i class="icon-reorder"></i> PDE Type Registratio 
                        </h4>
                       
                     </div>
      <div class="widget-body form">
               <div class="form-wizard">
                             
                               
                              <div class="tab-content">
                             
                                 <div class="tab-pane active" id="tab1">
                                   <form action="#" class="form-horizontal pdetypeform" id="pdetyperegistration" name="pdetyperegistration"  data-type="newrecord"  data-cheks="pdename<>pdecode" data-check-action="<?=base_url();?>pdes/ajax_pde_validation/<?=$i; ?>" data-action="<?=base_url();?>pdetypes/ajax_formsubmit/1/<?=$i; ?>"    data-elements="*pdetype<>*pdetypedescription" >
                        
                                    <h3>PDE Type Registration : </h3>

                                    <div class="control-group">
                                    <div class="row-fluid">
      <div class="span12">
         <div class="row-fluid">
            <div class="span12">
            
             <strong class="span4">PDE Type : * </strong>
             

              <input type="text"  class="span8 pdetype" id="pdetype"  name="pdetype" <?php if($formtype =='edit'){ echo 'value="'.$pdetype.'"' ; } ?> datatype="text" />
            </div>
           
         </div>
<br/>
          <div class="row-fluid">
             <div class="span12">
             
              <strong class="span4">Description : * </strong>
              <textarea value=""    class="span8 pdetypedescription" id="pdetypedescription"   name="pdetypedescription" datatype="text"  rows="10">
             <?= $details; ?> 
              </textarea>
            </div>
             
           
         </div>
         <br/>
           <div class="row-fluid">
             <div class="span4">
             </div>
         <div class="span8">
       <button class="btn btn-default btn-sucess" type="submit">Submit</button>
       <button class="btn btn-default btn-sucess" type="Reset">Reset</button>
       
    </div>
  </div>
  
<br/>
 
            



      </div>

   </div>



                                       
                                    </div>
                                    </form>
                                 
                                 
                                 </div>
 
                                
                                  
                              </div>
                              
                           </div> 
                           </form>                      
                     </div> 

                      
                  </div>
               </div>
            </div>
            <!-- END PAGE CONTENT-->