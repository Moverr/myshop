    <?php  if(empty($requiredfields)) $requiredfields = array();?>
    <style>
    .subject_of_procurement { display: none; }
    </style>
   
    <script type="text/javascript">
	
	 
		msg = 0;
		 <?php
		if(!empty($msg))
		{
		?>
		msg = 1;
		<?php
		}
		  ?>
        $(function(){
		msg = 0;
		<?php
		if(!empty($msg) && !empty($formdata) && empty($v))
		{
		?>
		msg = 1;
		<?php
		 }
		  ?>
    

        //manage lots on IFB level

       

            $("#quantityifb").change(function(){
                var  quantityifb = this.value;
                var procurement_details_quantity = $("#procurement_details_quantity").val();
                chekcer(procurement_details_quantity,quantityifb);
               
                           

            });

            $("#bidinv").submit(function(event)
            {
				 y


               var validitystatus = $("#bidvaliditystatus").val();
               if(validitystatus > 0)
               {
                 event.preventDefault();
                 alertmsgs("Bid Validity has Expired ");
                 return;            
               }
                 var quantityifb = $("#quantityifb").val();              
                 var procurement_details_quantity = $("#procurement_details_quantity").val();
         
         if(revenue =='N')
                 { 
                  if(quantifiable == 'Y')
                  chekcer(procurement_details_quantity,quantityifb);
                 else
                   pass = 1;
                 }
                 else
                 {
                  pass = 1;
                 }
				
				//IF ITS NOT AN EDIT OR SOMETHING 
				 if(msg == 1 )
				 {
					if(procurement_pass == 0)
					{						
						 event.preventDefault();
						 alertmsgs("Invalid Procurement Reference Number");
						 return;
					} 
				 }
                 
                 if(pass == 0)
                 event.preventDefault();
                 if(procurement_pass == 0)
                {
                 event.preventDefault();
                 alertmsgs("Invalid Procurement Reference Number");
                 return;
                }
            });

            <?php

            if(!empty($formdata['procurement_ref_no'])) 
            {?>
            procurement_pass = 1;

            <?php }
            else
            {
                ?>
                procurement_pass = 0;
                <?php
            }
            ?>
            $(".unique_no").change(function(){

            //check for information from the server ::
            var sequencenum = '';
            if(inactive_sequence_number == 0)
               sequencenum = $("#sequencenumber").val();
            else
               sequencenum = '';
            var refn = this.value;
            if(refn.length <= 0)
            return;

            formdata = {};        
            url = "<?=base_url().'bids/search_refno'; ?>";
            formdata['refno'] = sequencenum+''+refn; 
            $(".procurement_alert").html("Proccessing ....");
            $.ajax({
            type: "POST",
            url:  url,
            data:formdata,
            success: function(data, textStatus, jqXHR){
              console.log(data);        
              if(data== 1)
              {
                $(".procurement_alert").html("Reference Number Exists");
                procurement_pass = 0;
              }
              else if(data ==0)
              {
                 $(".procurement_alert").html('');
                 procurement_pass = 1;
              }
              else
              {
                 $(".procurement_alert").html("Contact Administrator");
                 procurement_pass = 0;
              }
            },
            error:function(data , textStatus, jqXHR)
            {
                console.log("SERVER ERROR "+data);

            }
            });

                
            });

            function chekcer(procurement_details_quantity,quantityifb){
                //alert('pass');
                 $(".alert").fadeOut('slow');
                str = '';
                  var diff = procurement_details_quantity - quantityifb;
                    if( diff >= 0 )
                    {
                        //alert('pass');
                        pass = 1;
                    }
                    else
                    {
                       // alert('fail');

                         str = "IFB Quantity Can Not Be Greater than Quantity Available for Procurement Entry"
                        

                        
                          $(".alert").fadeIn('slow');
                          $(".alert").html(str);
                          console.log(str);
                          pass = 0; 
                          $('html, body').animate({scrollTop : 0},800);
                    }
            }
        })
    </script>
    <div class="widget">
        <div class="widget-title">
            <h4><i class="fa fa-reorder"></i>&nbsp;<?=(!empty($form_title)? $form_title : '') ?></h4>
                <span class="tools">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-remove"></a>
                </span>
        </div>
        <div class="widget-body">
            <?php 
      $x = 2;
      #$procurement_plan_entries;
      /*
        DO NOT SHOW PROCUREMENT PLAN ENTRIES ON LOAD 
      */
                if($x == 1): 
                    print format_notice('WARNING: There are no procurement entries available for bid invitation');
                else: 
            ?>            
                <!-- BEGIN FORM-->
                <form  id="bidinv" action="<?=base_url() . 'bids/save_bid_invitation' . ((!empty($i))? '/i/'.$i : '' )?>" enctype="multipart/form-data" method="post" class="form-horizontal">
                    <div class="form_details">

                    <!-- Branches -->
                    <div class="control-group <?=(in_array('procurement_id', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Branch </label>
                            <div class="controls">
                 

                          <select  <?=!empty($i) ?'disabled' :'' ; ?> id="pde_branch" class="input-xlarge m-wrap   pde_branch" name="pde_branch">
                           
                           <?=get_select_options($branches, 'shortcode', 'branchname', (!empty($branchid)? $branchid : '' ))?> 
                        </select>
                        </div>
                        </div>
                      



                    <!-- end -->
<!-- Financial Years Ting --> 
                      <div class="control-group <?=(in_array('procurement_id', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Financial Year </label>
                            <div class="controls">

                            <script>

               

               $(function(){ 

  clearForm = function()
                            {
                                $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio,#ifb_financial_year').val('');
                                $(':checkbox, :radio').prop('checked', false);
                                
                            }


             
                /*
                $(':input','#myform')
                 .not(':button, :submit, :reset, :hidden')
                 .val('')
                 */

                              $("#ifb_financial_year").change(function(){

                               formdataa = {};                               
                                
                                {
                                    // clearForm();
                                
                                 $("#procurement_plan_details").html("");
                                    
                                    var fin_year = this.value;
                                    formdataa['financial_year'] = fin_year
                                    var getUrl = getBaseURL() + 'bids/fetch_procurement_plan_entries';
                                  $('#procurementref-no').empty().append("<option>Proccessing...</option>");
                                  $.ajax({
                                      url: getUrl,
                                      type: 'POST',
                                      data: formdataa,
                    dataType: "html",
                                      success: function(data)
                                      {  
                                     
                                     /* var obj = JSON.parse(data); 
                                      
                                      var str_options = "";
                                       for (var row in obj) {
                                       // console.log(obj[row]);
                                        str_options += "<option value='"+row+"'>" +obj[row]+"</option>";
                                       }   */
                                      $('#procurementref-no').empty().append(data);
                    console.log("SERVER RECEIVED : "+data);


                                      },

                                      error:function(data)
                                      {
                                        console.log("SERVER ERROR : "+data);
                                      }


                                      });
                                  }

                              });
                            })
                            </script>
                 
                          <select  <?=!empty($i) ?'disabled' :'' ; ?> id="ifb_financial_year" class="input-xlarge m-wrap   financial_year" name="financial_year">
                           
                           <?=get_select_options($financial_years, 'fy', 'label', (!empty($current_financial_year)? $current_financial_year : '' ))?> 
                        </select>


                            </div>
                     </div>

                        <div class="control-group <?=(in_array('procurement_id', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Subject of procurement<span>*</span></label>
                            <div class="controls">
    <script>
     $(function(){
                            haslots = 0;
     $(".haslots").click(function(){
      open_method = "1<>2<>9";
      
           var splitted_method = open_method.split("<>");
       
           if($(this).is(':checked'))
                {

    //  var textbutton = $(".savebutton").html();
        
     
                  //loop through to see if the method exists
                  var xh = 0;
                  for(var i=0;i<splitted_method.length;i++)
                  {
                    //alert( splitted_method[i] + " __ " + obj.procurement_method);
                    if(splitted_method[i] == obj.procurement_method)
                    {
                      xh = 1;
                    }
                  }
          
                    if(xh == 0)
                  {
                $(".publish_button").fadeOut('slow');
                $(".savebutton").fadeIn('slow');
                 $(".savebutton").html('Save and Add Lots');
              $(".savealone").fadeIn('slow');
                  }
                  else
                  {
                $(".publish_button").fadeIn('slow');      
                $(".savebutton").fadeOut('slow');  
                 $(".publish_button").html('Publish and Add Lots');       
          $(".savealone").fadeIn('slow');
                  }

                             
                //  $(".publish_button").fadeOut('slow');
                 haslots = 1;
                }
                else
                {

       var xh = 0;
                  for(var i=0;i<splitted_method.length;i++)
                  {
                    //alert( splitted_method[i] + " __ " + obj.procurement_method);
                    if(splitted_method[i] == obj.procurement_method)
                    {
                      xh = 1;
                    }
                  }
          

                     if(xh == 0)
                  {
                $(".publish_button").fadeOut('slow');
                $(".savebutton").fadeIn('slow');
                 $(".savebutton").html('Save');
        $(".savealone").fadeOut('slow');
                  }
                  else
                  {
                $(".publish_button").fadeIn('slow');      
                $(".savebutton").fadeOut('slow');  
                 $(".publish_button").html('Save And Publish IFB');  
                $(".savealone").fadeIn('slow');         
                 }


            // //  $(".bidvalidatey").addClass('hidden'); 
            //      $(".savebutton").html('Save And Publish');
            //       haslots = 0;
            //       if(xh > 0 )
            //        $(".publish_button").fadeIn('slow');


                }


        });

    var xh = 0;

    <?php

    if(!empty($formdata['procurement_id']))
    {
        ?>
    
 <?php
 $str  = "";
 if(!empty($i))
 $str = "bidid/".$i;
  ?>
  var  url_extension = '<?=$str; ?>';
   

     
    procurement_entry_id = 0;
        /* START PROCUREMENT DETAILS FETCH */
    haslots = 0;
    var getUrl = getBaseURL() + 'bids/procurement_record_details/'+url_extension;
      var open_method = "1<>2<>9";
        if($(this).hasClass('get_beb'))
          getUrl += '/b/get'; 
          console.log(getUrl);
          var quantityifb = $("#quantityifb").val();
          formdata = {proc_id: '<?=$formdata['procurement_id']; ?>',ifbquantity:quantityifb};  
          procurement_entry_id = '<?=$formdata['procurement_id']; ?>';
          console.log(formdata);
        $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
                $.ajax({
                    url: getUrl,
                    type: 'POST',
                    data: formdata,
                    success: function(msg)
                    {
                        


                        $("#procurement_plan_details").html(msg);
           
                         <?php
             //If VIew is directly from Procurement Plan Entries  
             // Trigger Sequence Number to load Procurement Ref Number at IFB Level
        
      if(!empty($v))
												{ ?>
															   $.ajax({
																url: getBaseURL() + 'bids/loadprocurementrefno',
																type: 'POST',
																data: formdata,
																success: function(msg)
																{
																  if(msg != 0)
																  {
																	$("#sequencenumber").removeClass("hidden");
																    $("#sequencenumber").val(msg);
																    console.log(msg);													   
																    console.log("Trouble Begins Here");
													    
													   
													   
																	if(procurement_entry_id <= 0)
																	return;
										   

										  alertmsg("Proccessing More Procurement Entry Details ");
										  var formdata = {};
										  formdata['proc_id'] = procurement_entry_id;
										  branch_shortcode = $("#pde_branch").val();
										  if(branch_shortcode.length > 0)
										  {
										   // alert(branch_shortcode);
										   formdata['branch_shortcode'] = branch_shortcode;       
										  }
										 
										 var getUrl = getBaseURL() + 'bids/json_encode_procurement_entry';

										   $.ajax({
														url: getUrl,
														type: 'POST',
														data: formdata,
														success: function(data)
														{
														 // alert("Received");
														  var open_method = "1<>2<>9";
														  var json = data;
														  obj = JSON.parse(json)
														  console.log(obj);   
														  var ifb_address = '';
														  ifb_address =  obj.pde_address;                  
														  if(branch_shortcode.length > 0)                  
														  ifb_address =  obj.branch_address;
														  entry_proc_method = obj.procurement_method; 
														   
														  // procurement_entry_details
														  console.log("487 movers :REVENUE STATUS "+revenue); 
														  
														if(entry_proc_method == 10)
														{
															$(".option_ifbs").fadeOut('slow');
														}
														else
														{
														  $(".option_ifbs").fadeIn('slow');												  
												  
														  $("#documents_inspection_address").html(ifb_address);
														  //alert(ifb_address);   
														  $(".bid_openning_date").val(obj.bid_closing_date);
														  $(".bid_submission_deadline").val(obj.bid_closing_date);                   
														  $(".invitation_to_bid_date").val(obj.bid_issue_date);
														  $(".contract_award").val(obj.contract_award);
														  $(".approval_date").val(obj.contracts_committee_approval_date);
														  $(".display_of_beb_notice").val(obj.best_evaluated_bidder_date);
														  $(".bid_evaluation_from").val(obj.submission_of_evaluation_report_to_cc);
														  $(".bid_evaluation_to").val(obj.cc_approval_of_evaluation_report);
														  // $(".quantityifb").val(obj.quantity).trigger("change");
														  quantifiable = obj.quantifiable;												  
														  revenue = obj.revenue;
												  
												         console.log("Answer : PASSED");
												  
														if(revenue == 'N')
														{
														  
																	  if(quantifiable == 'Y')
																		{
																			
																		  $(".quantity_div").fadeIn('slow');          
																		  $(".quantityifb").val( obj.quantity -  obj.ifb_quantity_added).trigger("change");
																	
																		}
																		else
																		{
																			
																		  console.log(quantifiable+" QUANTIFIABLE STATUS ");
																		  $(".quantityifb").val('0');
																		  $(".quantity_div").fadeOut('slow');
																		  pass = 1;
																		  $(".alert").fadeOut('slow');
																		  
																		}
														  }
														  else
														  {
																			  $(".quantityifb").val('0');
																			  $(".quantity_div").fadeOut('slow');
																			  pass = 1;
																			  $(".alert").fadeOut('slow');
														  }
											


											  
												 
												 
														
														}
												
													
													  splitted_method = open_method.split("<>");
													  check_ifb_method();
													
													
													
													//trigger change on Procurement Method IFB
													$("#procurement_method_ifb").trigger("change");
																		 
													},
													error:function(data)
													{
													 console.log(data);
													}

													});

													   
													 
										}
										else
										{
											$("#sequencenumber").val("");
										}


										},
										error:function(data)
										{
											console.log(msg);
										}
									});
 
                         <?php
                       }
                       else
                       {
                        ?>

                        <?php
                       }
                       ?>
                         
                          
                    }
                });
        
        
         //Trigger IFB METHOD change status
		  console.log(":::::::::::::::::::::::::::::");
          $("#procurement_method_ifb").trigger("change");
        
        //auto get button at runtime depending on the method

        /* var getUrl = getBaseURL() + 'bids/json_encode_procurement_entry';

         $.ajax({
                url: getUrl,
                type: 'POST',
                data: formdata,
                success: function(data)
                {
                
                  var json = data;
                  obj = JSON.parse(json)
                  console.log(obj);

           entry_proc_method = obj.procurement_method;
         $("#procurement_method_ifb").trigger("change");
               
                     var splitted_method = open_method.split("<>");
                  //loop through to see if the method exists
                  var xh = 0;
                  for(var i=0;i<splitted_method.length;i++)
                  {
                    //alert( splitted_method[i] + " __ " + obj.procurement_method);
                    if(splitted_method[i] == obj.procurement_method)
                    {
                      xh = 1;
                    }
                  }

                  if(xh == 0)
                  {
                    $(".publish_button").fadeOut('slow');
                      $(".savebutton").fadeIn('slow');
                    
                  }
                  else
                  {
                  $(".publish_button").fadeIn('slow');
                    $(".savebutton").fadeOut('slow');
                  }


                  //$(".bid_issue_date").val(obj.bid_issue_date);

                  //bid_openning_date
                },
                error:function(data)
                {
                        console.log(data);
                }

            });  */
      

    //manage has lots 

    //fetch procurement entry details
    function procurement_entrydetails()
    {
      
        if(procurement_entry_id <= 0)
        return;
     
        alertmsg("Proccessing More Procurement Entry Details ");
        
        var formdata = {};
        formdata['proc_id'] = procurement_entry_id;
        var getUrl = getBaseURL() + 'bids/json_encode_procurement_entry';

              $.ajax({
                    url: getUrl,
                    type: 'POST',
                    data: formdata,
                    success: function(data)
                    {
                     $(".alert").fadeOut('slow');
                      var open_method = "1<>2<>9";
                      var json = data;
                      obj = JSON.parse(json)
                      console.log(obj);  
                  
                      var splitted_method = open_method.split("<>");
                      //loop through to see if the method exists
                      
                      for(var i=0;i<splitted_method.length;i++)
                      {
                        //alert( splitted_method[i] + " __ " + obj.procurement_method);
                        if(splitted_method[i] == obj.procurement_method)
                        {
                          xh = 1;
                        }
                      }

                      if(xh == 0)
                      {
                        $(".publish_button").fadeOut('slow');
                      }
                      else
                      {
                        if(haslots == 0)
                        $(".publish_button").fadeIn('slow');                    
                      }

                      
                      
                      //$(".bid_issue_date").val(obj.bid_issue_date);

                      //bid_openning_date                 
                    },
                    error:function(data)
                    {
                            console.log(data);
                    }

                });


    }


        /* END OF PROCUREMENT DETAILS FETCH */
     $('#procurementref-no').val('<?=$formdata['procurement_id']; ?>').trigger('change');
     <?php
        }
    ?>
     })
     </script>
     
      
				<!-- Display Procurement Reference Numbers --> 
                 <select id="procurementref-no" class="input-xlarge m-wrap    prid procurement_idxs" name="procurement_id" tabindex="1">
                                   
                  <?php  if(!empty($formdata['procurement_id'])){ ?>
                                    <?=get_select_options($procurement_plan_entries, 'procurement_id', 'subject_of_procurement', (!empty($formdata['procurement_id'])? $formdata['procurement_id'] : '' ))?>
                  <?php } ?>                               

                 </select>
              <input type="text" name="subject_details" id="subject_details" value=" <?=(!empty($formdata['subject_details'])? $formdata['subject_details'] : '' ); ?> " class="input-large subject_details">

                                <?php 
                                if(!empty($formdata['procurement_id']))
                                {
                                    ?>
                               
                                <script>
                                $(function(){

                                      $(".prid").val("<?=$formdata['procurement_id']; ?>").trigger('change');

                                    console.log('...');
                                 })
                               
                                </script>
                                  <?php } ?>
                                <?=(in_array('procurement_ref_no', $requiredfields)? '<span class="help-inline">Please select a procurement reference number</span>': '')?>
                            </div>
                        </div>
                        <div id="procurement_plan_details">
                        <?php
                      #  print_r($formdata);
                        ?>
                            <?php if(!empty($formdata['procurement_details'])): ?>
                            <?php $procurement_details = $formdata['procurement_details']; ?>
                                <div class="control-group  subject_of_procurement">
                                    <label class="control-label">Subject of procurement:</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details['subject_of_procurement'])? $procurement_details['subject_of_procurement'] : '<i>undefined</i>')?>
                                        <input type="hidden" name="procurement_details[subject_of_procurement]" value="<?=$procurement_details['subject_of_procurement']?>" />
                    <!-- Hidden Field to Determine Revenue status --> 
                    <input type="hidden" id="revenue" name="procurement_details[revenue]" value="<?=$procurement_details['revenue'];?>"/>
                                 
                   
                                 
                                
                                  
                                <div class="control-group">
                                    <label class="control-label">Source of Funding:</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details['funding_source'])? $procurement_details['funding_source'] : '<i>undefined</i>')?>
                                        <input type="hidden" name="procurement_details[funding_source]" value="<?=!empty($procurement_details['funding_source']) ? $procurement_details['funding_source'] :'';?>" />
                                    </div>
                                </div
    <?php

    #print_r($procurement_details);

                        if(!empty($procurement_details_quantity)){
                          // $total_ifb_q = $procurement_details['quantity'] - $procurement_details['total_ifb_quantity'];
        
        ?>
                                   <div class="control-group">
                                    <label class="control-label">Quantity :</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details_quantity)? $procurement_details_quantity : '<i>undefined</i>')?>
                                        <input type="hidden"  id="procurement_details_quantity" name="procurement_details_quantity" value="<?=$procurement_details_quantity; ?>" />
                                    </div>
                                </div>

    <?php }
    ?>
                                <div class="control-group">
                                    <label class="control-label">Method of procurement:</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>')?>
                                        <input type="hidden" name="procurement_details[procurement_method]" value="<?=$procurement_details['procurement_method']?>" />
                                    </div>
                                </div>
                            
                                <div class="control-group">
                                    <label class="control-label">Financial Year:</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details['financial_year'])? $procurement_details['financial_year'] : '<i>undefined</i>')?>
                                        <input type="hidden" name="procurement_details[financial_year]" value="<?=$procurement_details['financial_year']?>" />
                                    </div>
                                </div>
                                
                                <div class="control-group">
                                    <label class="control-label">Method of Procurement:</label>
                                    <div class="controls">
                                        <?=(!empty($procurement_details['procurement_method'])? $procurement_details['procurement_method'] : '<i>undefined</i>')?>
                                        <input type="hidden" name="procurement_details[procurement_method]" value="<?=$procurement_details['procurement_method']?>" />
                                    </div>
                                </div>                           
                             <?php endif; ?>
                        </div>
                        <hr/>
                        
                        <div class="control-group <?=(in_array('procurement_ref_no', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Procurement reference number:<span>*</span>
                            <br/> Custom? <input <?=(!empty($formdata['custom_reference'])) ?'checked' :'' ; ?> type="checkbox" id="custom_reference" class="custom_reference" name="custom_reference" /></label>
                            <div class="controls">
                            <?php
                           # print_r($formdata);
                            if(!empty($formdata['procurement_ref_no'])){
                            
                            if(!empty($formdata['custom_reference']))
                            {
                              #print_r($formdata['custom_reference']);
                             # exit();
                               $formdata['sequencenumber']  = (empty($formdata['sequencenumber'])) ? $formdata['procurement_ref_no'] : $formdata['sequencenumber'];
                            }
                            else
                            {

                            $dataarray = explode("/", $formdata['procurement_ref_no']);
                            $reference_id = '';
                            //$formdata['sequencenumber'] =  . $dataarray[1]."/". $dataarray[2]."/";
                            for($i=0; $i<count($dataarray)-1; $i++)
                            {
                              $reference_id .=  $dataarray[$i]."/";                            
                            }
                            $formdata['sequencenumber'] =  $reference_id;
                            $formdata['procurement_ref_no'] = $dataarray[count($dataarray)-1]; 
                            }

                           }


                            ?>
                             <input  style="<?=!empty($formdata['custom_reference'])?'display:none;':''; ?>"  type="text" name="sequencenumber" id="sequencenumber" value="<?=(!empty($formdata['sequencenumber'])? $formdata['sequencenumber'] : '' )?>" class="input-large " readonly />
                         
                                <input type="text" name="procurement_ref_no" id="procurement_ref_no" value="<?=(!empty($formdata['procurement_ref_no'])? $formdata['procurement_ref_no'] : '' )?>" class="input-large unique_no" />
                                <div class="procurement_alert"> </div>
                            </div>
                        </div>


                    <!-- Branches -->
                    <?php
                       if(!empty($formdata['entity_procured_for']))
                       {
                        $current_pde =  $formdata['entity_procured_for'];                                     
                       }
                        
                    ?>
                    <div class="control-group <?=(in_array('entity_procured_for', $requiredfields)? 'error': '')?>">
                    <label class="control-label">Entity Being Procured For </label>
                    <div class="controls">
                    <select    id="entity_procured_for" class="input-xlarge m-wrap   entity_procured_for chosen" name="entity_procured_for">
                    <?=get_select_options($pdes, 'pdeid', 'pdename', (!empty($current_pde)? $current_pde : '' ))?> 
                    </select>
                    </div>
                    </div>
                    <!-- end -->

                   
                      <!-- Procurement Method -->
                        <div class="control-group <?=(in_array('procurement_method_ifb', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Procurement method  </label>

                            <div class="controls">
                                <select id="procurement_method_ifb" onChange="javascript:check_ifb_method_for_justificatin(this.value);" class=" chosen input-large span3" name="procurement_method_ifb" data-placeholder="Choose a Category" tabindex="1">
                                    <?=get_select_options(get_active_procurement_methods(), 'id', 'title', (!empty($formdata['procurement_method_ifb'])? $formdata['procurement_method_ifb'] : '' ))?>
                                </select> 
                              <input type="hidden" id="ifb_method_justification_status" name="ifb_method_justification_status" value="<?=!empty($formdata['ifb_method_justification_status']) ? $formdata['ifb_method_justification_status'] : 'N' ?>" />
                             </div>
                        </div>
                        <!-- End of Procurement Method -->

            <!-- Justification Proccess -->
                        <div  style="<?php if((empty($formdata['ifb_method_justification_status'])  || ($formdata['ifb_method_justification_status'] == 'N')) && (empty($formdata['procurement_method_ifb'])) ){ echo 'display:none;';} ?> " class=" justification_panel control-group <?=(in_array('ifb_method_justification', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Justify why you choose this procurement method  </label>
                            <div class="controls"> 
                                <textarea name="ifb_method_justification" id="ifb_method_justification" class="ifb_method_justification" >
                                <?=!empty($formdata['ifb_method_justification']) ? $formdata['ifb_method_justification'] : '' ; ?>
                                </textarea>
                            </div>
                        </div>
              <!-- end of Justification -->
              <!-- End -->
        


                        <!-- Budgtet COde -->
                        <div class="control-group <?=(in_array('vote_no', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Budget code:<span>*</span></label>
                            <div class="controls">
                                <input type="text" name="vote_no" value="<?=(!empty($formdata['vote_no'])? $formdata['vote_no'] : '' )?>" class="input-medium" />
                            </div>
                        </div>

                        <!-- Initiated By -->
                        <div class="control-group <?=(in_array('initiated_by', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Initiated by:<span>*</span></label>
                            <div class="controls">
                                <input type="text" name="initiated_by" value="<?=(!empty($formdata['initiated_by'])? $formdata['initiated_by'] : '' )?>" class="input-medium" />
                            </div>
                        </div>

                        <!-- Quantity Bidded   -->
                        <div class="control-group <?=(in_array('quantityifb', $requiredfields)? 'error': '')?> quantity_div ">
                            <label class="control-label">Quantity :<span>*</span></label>
                            <div class="controls">
                            <?php
                            if(!empty($formdata['quantity']))
                            {
                            
                            $formdata['quantityifb'] =   $formdata['quantity'] ; 
                                                     
                            }
                            

                            ?>
                               <input type="text" id="quantityifb" name="quantityifb" value="<?=(!empty($formdata['quantityifb'])? $formdata['quantityifb'] : '0' )?>" class="input-medium quantityifb" />
                         </div>
                        </div>

            <!-- Date of Confirmation by AO -->
             <?php
            if(!empty($formdata['dateofconfirmationoffunds']) && empty($formdata['dateofconfirmationbyao']))
            {
              $formdata['dateofconfirmationbyao'] =  $formdata['dateofconfirmationoffunds'];
            }
          ?>
      
              <div class="control-group <?=(in_array('dateofconfirmationbyao', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Date of Confirmation of funds by Accounting Officer:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['dateofconfirmationbyao'])? display_date( $formdata['dateofconfirmationbyao']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="dateofconfirmationbyao" name="dateofconfirmationbyao" data-date="<?=(!empty($formdata['dateofconfirmationbyao'])? display_date( $formdata['dateofconfirmationbyao']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker dateofconfirmationbyao " type="text" value="<?=(!empty($formdata['dateofconfirmationbyao'])? display_date( $formdata['dateofconfirmationbyao']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                  <button type="button" onClick="javascript:   $('#dateofconfirmationbyao').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div> 


              <!-- Additional Notes -->
              <div class="control-group">
                            <label class="control-label"> Additional Notes <span></span></label>
                            <div class="controls">
                            <textarea   class="span4" name="additional_notes" id="additional_notes">
                              
                             <?= (!empty($formdata['additional_notes']) )  ? $formdata['additional_notes'] :'' ?>
                              </textarea>
                            </div>
                        </div> 


                        <!-- Estimated Amounts -->
              <div class="control-group">
                            <label class="control-label"> Estimated Amount <span></span></label>
                            <div class="controls">
                            <input type="text" name="estimated_amount" value="<?=(!empty($formdata['estimated_amount'])? addCommas($formdata['estimated_amount'], 0) : '' )?>" class="input-medium numbercommas" />
                                <select id="estimated_amount_currency" class="input-small m-wrap" name="estimated_amount_currency">
                                    <?=get_select_options($currencies, 'id', 'title', (!empty($formdata['estimated_amount_currency'])? $formdata['estimated_amount_currency'] : 1 ))?>
                                </select>
                            </div>
                        </div> 

            
              <!-- Optional Elements IFB -->
            <div class="option_ifbs">


            
                        <!-- Date Initiated -->
                        
                        <div class="control-group <?=(in_array('date_initiated', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contract Committee Approval Date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['date_initiated'])? display_date( $formdata['date_initiated']) : date('Y-m-d'))?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="approval_date" name="date_initiated" data-date="<?=(!empty($formdata['date_initiated'])? display_date( $formdata['date_initiated']) : date('Y-m-d'))?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker approval_date "  type="text" value="<?=(!empty($formdata['date_initiated'])? display_date( $formdata['date_initiated']) : '' )?>">
                                    <span class="add-on">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                     <button type="button" onClick="javascript: $('#approval_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>

                        
                        <div class="control-group <?=(in_array('bid_documents_price', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Price of bidding documents:</label>
                            <div class="controls">
                                <input type="text" name="bid_documents_price" value="<?=(!empty($formdata['bid_documents_price'])? addCommas($formdata['bid_documents_price'], 0) : '' )?>" class="input-medium numbercommas" />
                                <select id="bid-documents-currency" class="input-small m-wrap" name="bid_documents_currency">
                                <?=get_select_options($currencies, 'id', 'title', (!empty($formdata['bid_documents_currency'])? $formdata['bid_documents_currency'] : 1 ))?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('bid_security', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid security:</label>
                            <div class="controls">
                                <input type="text" name="bid_security_amount" value="<?=(!empty($formdata['bid_security_amount'])? addCommas($formdata['bid_security_amount'], 0) : '' )?>" class="input-medium numbercommas" />
                                <select id="bid-security-currency" class="input-small m-wrap" name="bid_security_currency">
                                    <?=get_select_options($currencies, 'id', 'title', (!empty($formdata['bid_security_currency'])? $formdata['bid_security_currency'] : 1 ))?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('invitation_to_bid_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Invitation to bid date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['invitation_to_bid_date'])? display_date( $formdata['invitation_to_bid_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="invitation_to_bid_date" name="invitation_to_bid_date" data-date="<?=(!empty($formdata['invitation_to_bid_date'])? display_date( $formdata['invitation_to_bid_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker invitation_to_bid_date"  type="text" value="<?=(!empty($formdata['invitation_to_bid_date'])? display_date( $formdata['invitation_to_bid_date']) : '' )?>">
                                    <span class="add-on">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <button type="button" onClick="javascript: $('#invitation_to_bid_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('pre_bid_meeting_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Pre-bid meeting date: <span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['pre_bid_meeting_date'])? display_date( $formdata['pre_bid_meeting_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="pre_bid_meeting_date"  name="pre_bid_meeting_date" data-date="<?=(!empty($formdata['pre_bid_meeting_date'])? display_date( $formdata['pre_bid_meeting_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker" type="text" value="<?=(!empty($formdata['pre_bid_meeting_date'])? display_date( $formdata['pre_bid_meeting_date']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                </div>
                                <button type="button" onClick="javascript:$('#pre_bid_meeting_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                <div class="input-append bootstrap-timepicker-component">

                                    <input id="pre_bid_meetingdate" name="pre_bid_meeting_date_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['pre_bid_meeting_date_time']) ? $formdata['pre_bid_meeting_date_time'] : (!empty($formdata['pre_bid_meeting_date'])? display_time('h:i A', trim(substr($formdata['pre_bid_meeting_date'], 10, 10))) : '' )?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                </div>
                                  <button type="button" onClick="javascript:$('#pre_bid_meetingdate').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                 

                            </div>
                        </div>
                        <div class="control-group <?=(in_array('bid_submission_deadline', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Deadline of bid submission:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_submission_deadline'])? display_date( $formdata['bid_submission_deadline']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_submission_deadline" name="bid_submission_deadline" data-date="<?=(!empty($formdata['bid_submission_deadline'])? display_date( $formdata['bid_submission_deadline']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker bid_submission_deadline" type="text" value="<?=(!empty($formdata['bid_submission_deadline'])? display_date( $formdata['bid_submission_deadline']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_submission_deadline').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                                <div class="input-append bootstrap-timepicker-component">
                                    <input id="bid_submission_deadline_time" name="bid_submission_deadline_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['bid_submission_deadline_time']) ? $formdata['bid_submission_deadline_time'] : (!empty($formdata['bid_submission_deadline'])? display_time('h:i A', trim(substr($formdata['bid_submission_deadline'], 10, 10))) : '' )?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_submission_deadline_time').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('documents_inspection_address', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Address where documents will be inspected:<span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="documents_inspection_address" id="documents_inspection_address" class="input-xxlarge"><?=(!empty($formdata['documents_inspection_address'])? $formdata['documents_inspection_address'] : '' )?></textarea>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('documents_address_issue', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Address  where documents will be issued:<span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="documents_address_issue" id="documents_address_issue" class="input-xxlarge"><?=(!empty($formdata['documents_address_issue'])? $formdata['documents_address_issue'] : '' )?></textarea>
                                <span>
                                <input type="checkbox" name="same_as_above" id="same_as_inspection" value="same" />
                                <label for="same_as_inspection" style="display:inline">
                                    Tick if same as above or state
                                </label>
                                </span>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('bid_receipt_address', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Address bids must be delivered to:<span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="bid_receipt_address" id="bid_receipt_address" class="input-xxlarge"><?=(!empty($formdata['bid_receipt_address'])? $formdata['bid_receipt_address'] : '' )?></textarea>
                                <span>
                                <input type="checkbox" name="same_as_above" id="same_as_issue" value="same" />
                                <label for="same_as_issue" style="display:inline">
                                    Tick if same as above or state
                                </label>
                                </span>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('bid_openning_address', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid opening address:<span>*</span></label>
                            <div class="controls">
                                <textarea rows="3" name="bid_openning_address" id="bid_openning_address" class="input-xxlarge"><?=(!empty($formdata['bid_openning_address'])? $formdata['bid_openning_address'] : '' )?></textarea>
                                <span>
                                <input type="checkbox" name="same_as_above" id="same_as_deliver" value="same" />
                                <label for="same_as_deliver" style="display:inline">
                                    Tick if same as above or state
                                </label>
                                </span>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('bid_openning_date', $requiredfields) || in_array('bid_openning_date_time', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid opening date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_openning_date'])? display_date( $formdata['bid_openning_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_openning_date" name="bid_openning_date" data-date="<?=(!empty($formdata['bid_openning_date'])? display_date( $formdata['bid_openning_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker bid_openning_date" type="text" value="<?=(!empty($formdata['bid_openning_date'])? display_date( $formdata['bid_openning_date']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                  <button type="button" onClick="javascript:$('#bid_openning_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                                <div class="input-append bootstrap-timepicker-component">
                                                                                                                                                             
                                    <input id="bid_openning_date_time" name="bid_openning_date_time" class="input-mini m-ctrl-small timepicker-default" value="<?=!empty($formdata['bid_openning_date_time']) ? $formdata['bid_openning_date_time'] : (!empty($formdata['bid_openning_date'])? display_time('h:i A', trim(substr($formdata['bid_openning_date'], 10, 10))) : '' )?>" type="text" />
                                    <span class="add-on"><i class="fa fa-clock-o"></i></span>
                                     <button type="button" onClick="javascript:$('#bid_openning_date_time').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('bid_evaluation_from', $requiredfields) || in_array('bid_evaluation_to', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid evaluation period:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_evaluation_from'])? display_date( $formdata['bid_evaluation_from']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_evaluation_from" name="bid_evaluation_from" data-date="<?=(!empty($formdata['bid_evaluation_from'])? display_date( $formdata['bid_evaluation_from']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" placeholder="From" class="m-ctrl-medium date-picker bid_evaluation_from" type="text" value="<?=(!empty($formdata['bid_evaluation_from'])? display_date( $formdata['bid_evaluation_from']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_evaluation_from').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bid_evaluation_to'])? display_date( $formdata['bid_evaluation_to']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bid_evaluation_to" name="bid_evaluation_to"  placeholder="To" data-date="<?=(!empty($formdata['bid_evaluation_to'])? display_date( $formdata['bid_evaluation_to']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker bid_evaluation_to" type="text" value="<?=(!empty($formdata['bid_evaluation_to'])? display_date( $formdata['bid_evaluation_to']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#bid_evaluation_to').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>    

                     <div class="control-group <?=(in_array('display_of_beb_notice', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Display of BEB notice:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['display_of_beb_notice'])? display_date( $formdata['display_of_beb_notice']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="display_of_beb_notice" name="display_of_beb_notice" data-date="<?=(!empty($formdata['display_of_beb_notice'])? display_date( $formdata['display_of_beb_notice']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker display_of_beb_notice" type="text" value="<?=(!empty($formdata['display_of_beb_notice'])? display_date( $formdata['display_of_beb_notice']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                     <button type="button" onClick="javascript:$('#display_of_beb_notice').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div> 
            
                        
                        <div class="control-group <?=(in_array('contract_award_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contract award date:<span>*</span></label>
                            <div class="controls">
                                <div class="input-append date date-picker" data-date="<?=(!empty($formdata['contract_award_date'])? display_date( $formdata['contract_award_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="contract_award_date" name="contract_award_date" data-date="<?=(!empty($formdata['contract_award_date'])? display_date( $formdata['contract_award_date']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium date-picker contract_award" type="text" value="<?=(!empty($formdata['contract_award_date'])? display_date( $formdata['contract_award_date']) : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                    <button type="button" onClick="javascript:$('#contract_award_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                                </div>
                            </div>
                        </div>
                               
                     

                       


<?php
// bidvalidity
//bidvalidityperiod
if(!empty($formdata['hasbidvalididy']))
  $formdata['bidvalidity'] = $formdata['hasbidvalididy'];

if(!empty($formdata['bidvalidtity']))
  $formdata['bidvalidityperiod'] = $formdata['bidvalidtity'];



?>
  <!-- bid validity period -->
  <script type="text/javascript">

        $(function(){

    
     // alert messages ::
    var alertmsg = function(msg){
    $(".alert").fadeOut('slow');
    $(".alert").fadeIn('slow');$(".alert").html(msg);
    scrollers();

    }
    var alertmsgs = function(){
        $(".alert").fadeOut('slow');
    }
  

      inactive_sequence_number =  <?=(!empty($formdata['custom_reference'])) ? 1 :0 ; ?>;
      console.log(inactive_sequence_number);
          //custom_reference
          $("#custom_reference").click(function(){
           
           if(this.checked  == true)
            {
              $("#sequencenumber").removeAttr('readonly');
              $("#sequencenumber").fadeOut('slow');
              inactive_sequence_number = 1;
               



              //procurementref-no
            }
          else
            {

              $("#sequencenumber").attr('readonly','');
              $("#sequencenumber").fadeIn("slow");
              inactive_sequence_number= 0;

            }

          })
            $(".bidvalidity_q").click(function(){
               
              if($(this).is(':checked'))
                {
                $(".bidvalidatey").removeClass('hidden');   
                $(this).val('y');      
                }
                else
                {
               $(".bidvalidatey").addClass('hidden'); 
                  $(this).val('n');    
                }
            })
        })
    </script>
                    <div class="control-group">
                            <label class="control-label"> Bid Validity Expires On <span></span></label>
                            <div class="controls">
                            <input type="checkbox" value="n" <?php if(!empty($formdata['bidvalidity'])){ ?> checked <?php } ?> name="hasbidvalididy" class="bidvalidity_q"/>
                            </div>
                        </div> 

                    <?php
                   # print_r($formdata);
                    ?>

    <!-- end of validity period -->
    <!-- bid validity period -->
                    <div class="control-group  bidvalidatey  <?php if(empty($formdata['bidvalidity'])){ ?> hidden <?php } ?> ">
                            <label class="control-label"> Date </label>
                            <div class="controls">
                             <div class="input-append date date-picker" data-date="<?=(!empty($formdata['bidvalidity'])? display_date( $formdata['bidvalidityperiod']) : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                                    <input id="bidvalidtity" name="bidvalidtity" data-date="<?=(!empty($formdata['bidvalidity'])?  $formdata['bidvalidityperiod'] : date('Y-m-d') )?>" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class=" m-ctrl-medium date-picker" type="text" value="<?=(!empty($formdata['bidvalidity'])? $formdata['bidvalidityperiod'] : '' )?>" />
                                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                                </div>
                                  <button type="button" onClick="javascript:$('#bidvalidtity').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                
                         <!--     <input name="bidvalidtity"  placeholder="Days" class="input-large" value="0" type="text" />
                       -->       </div>
                        </div> 

          <!-- end of validity period -->

              <?php
                 if(empty($formdata['procurement_ref_no'])) 
                 {
              ?>
                    <script>
                     $(function(){
                     $(".clearbtn").click(function(){
                          $(".date-picker").attr('value','');
                            $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').attr('value','');
                             $(':checkbox, :radio').prop('checked', false);
                     /* $(".date-picker").val('');
                       alert("moeoe")  */         
                     });
                     });
                     </script>
                     <?php 
                   }
                 #  print_r($formdata['haslots']);
                   ?>
             <!-- Does It have Lots -->
                         <div class="control-group">
                            <label class="control-label"> Does It Have Lots? <span></span></label>
                            <div class="controls">
                            <input type="checkbox"   <?php if(!empty($formdata['haslots']) &&($formdata['haslots'] == 'Y' ) ){ ?>  checked <?php } ?> name="haslots" class="haslots"/>
                            </div>
                        </div> 



             




</div>

     
                  <div class="form-actions">
                        <button type="reset" name="cancel" value="cancel" class="btn">Clear</button>
                        <?php if(!empty($formdata['haslots']) && ($formdata['haslots'] == 'Y'))
                        {
                          ?>

                            <button type="submit" name="save" value="save_lots" class="btn blue savebutton">Save  And Add Lots </button>
                           <button type="submit" name="savealone" value="savealone" class="btn blue savealone">Save </button>
                 <?php   if(empty($formdata['btnstatus'])) { ?> 
                            <button type="submit" name="approve" value="approve" class="btn blue publish_button">Publish IFB  And Add Lots </button>
                  
                        <?php
                      }
                        }
                       ?> 

                           <?php    if( !empty($formdata['haslots']) && ($formdata['haslots'] == 'N')){ ?>
                         <?php   if(empty($formdata['btnstatus'])) { ?> 
                            <button type="submit" name="approve" value="approve" class="btn blue publish_button">Save And Publish IFB </button>
                            <?php } ?>
                            <button type="submit" name="save" value="save" class="btn blue savebutton">Save  </button>
                         
                        <?php }
             if(empty($formdata['haslots']))
            {
            ?>
             
               <button type="submit" name="approve" value="approve" class="btn blue publish_button">Save And Publish IFB </button>
               

                           <button type="submit" name="save" value="save" class="btn blue savebutton">Save  </button>
             <?php
            } 
                      
            ?>
                    </div>
          
                </form>
                <!-- END FORM-->    
            <?php endif; ?>
            
        </div>
    </div>