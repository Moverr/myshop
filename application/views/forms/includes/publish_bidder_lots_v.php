<?php 
if(!empty($area) && in_array($area, array('publish_bidder_lots')))
{

	$type_oem = 0;
		$ddate_octhe = '';
		$num_orb = 0;
		$date_oce_r = '';
		$date_oaoterbt_cc = '';
		$beb_expiry_date = '';
        $final_evaluation_report_approval_date = '';
		$evaluation_commencement_date  =  '';
		$nationality = '';
		$currency = '';
        $pid = '';
        $num_bids_local = '';
		$contractprice ='';
		$date_beb_expires = '';
		$readoutprice_ugx = '';
		$justification = '';



 
	 		if((isset($bebresult) )&& (!empty($bebresult))){
			# print_array($bebresult);
				$type_oem = $bebresult[0]['type_oem'];
				$ddate_octhe = Date('d/m/Y',strtotime($bebresult[0]['ddate_octhe']));
				$num_orb = $bebresult[0]['num_orb'];
				$date_oce_r	= Date('d/m/Y',strtotime($bebresult[0]['date_oce_r']));
				$date_oaoterbt_cc =  Date('d/m/Y',strtotime($bebresult[0]['date_oaoterbt_cc']));
				$beb_expiry_date =  Date('d/m/Y',strtotime($bebresult[0]['beb_expiry_date']));
				$final_evaluation_report_approval_date = Date('d/m/Y',strtotime($bebresult[0]['final_evaluation_report_approval_date']));
                $evaluation_commencement_date = Date('d/m/Y',strtotime($bebresult[0]['evaluation_commencement_date']));
				$nationality = $bebresult[0]['nationality'];
				$currency =  $bebresult[0]['currency'];
				$pid = $bebresult[0]['pid'];
				$contractprice = $bebresult[0]['contractprice'];
				$num_bids_local = $bebresult[0]['num_orb_local'];
				$bestvaluetedbidder  = $bebresult[0]['id'];				
				$this->session->set_userdata('bestevaluated', $bestvaluetedbidder );
				$date_beb_expires =  $bebresult[0]['beb_expiry_date'];
				$justification = trim($bebresult[0]['justification']);
				$lot_id_info = $bebresult[0]['received_lot']; 

         # Pick BEB  ID 
         $bebid = mysql_real_escape_string($bebresult[0]['id']);
         $this->session->set_userdata(array('bebid'=>$bebid));

				
				?>
				<script type="text/javascript">
				lot_id = <?=$lot_id_info; ?> 

				// additional information 
				 checkser = 0;
    var datas_v = 0;
      readdata = {};
      redit_jv =  Array();
      currencies_added = Array();
      
      xcount_me = 0;
    $("#readoutprice_add").click(function(){
count_me = 0;
xcount_me = xcount_me + 1;
if(xcount_me > 2)
  return;
//alert("eeekareaea");
      checkser  = 1;
      var dataid = this.id;
      var dataelements =   $("#"+dataid).attr('data-elements');
	  console.log("passed this stage");


      if(dataelements.length > 0)
      {
       var fieldNameArr=dataelements.split("<>");
      }      
      else
      {
       fieldNameArr = Array();
      }
	  console.log(fieldNameArr);
         
	  
       if((dataelements!= ' ') &&(dataelements.length > 0))
            {
            	
            	 
                for(var i=0;i<fieldNameArr.length;i++)
                 {
                    //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED
                     var lke = fieldNameArr[i].split("*");
                      elementfield = lke[1];
                     // alert(elementfield);
                     console.log("ELEMENT PSSAEDE m<nr/.");
                   
                     console.log(elementfield);

                         formvalue = elementfield =='readoutprice'?  $("#"+elementfield).val()  : $("#"+elementfield).val();
                         
                         if((elementfield == "readoutprice")){
                           if(formvalue.length > 3 && isNaN(formvalue))
                           {
                              formvalue = numonly(formvalue);                             
                            }
                            
                         }

                         if((elementfield == "exchangerate") &&( inxt == 0)){
                        //  alert('mover')
                          formvalue = 0;
                         }

                        if((elementfield == "exchangerate") &&( inxt != 0)){
                            if(formvalue.length > 3)
                          {                           
                            formvalue = numonly(formvalue);
                          //  alert('Inputt');
                          }
                        }


                     if(elementfield == "currency") 
                     {
                    //alert("movers");
                     var a = currencies_added.indexOf(formvalue);
                    // alert(a);
                     console.log("FORM VALUE AT THIS POINT");
                     console.log(formvalue);
                     if (a > -1)
                     {
                      alertmsg("An Amount In "+formvalue+" has already been added");
                      return;
                     }
                     currencies_added.push(formvalue);                      

                     }



                         if(fieldNameArr[i].charAt(0)=="*"){
                         // alert("passs");
                         // alert(formvalue.length);

                         
    var datatype =  $("#"+elementfield).attr('datatype');
                    switch(datatype)
                    {
                        case 'money':

                         if(formvalue.length <= 0)
                          {
                          alertmsg('Fill Blanks');
                           return false;
                          }
             if(formvalue.length > 0)
                {
                //alert(fieldNameArr[i]);
                        var valu = isNumber(formvalue);
                      //alert('mover');
                        if(valu == false)
                        {
                            alertmsg('Invalid Entry, Enter Digits');
                            $("#"+elementfield).css('border', 'solid 3px #FFE79B');
                            return false;
                        }
            	}
                        break;
                       default:
             break;

          }
                      }

                        else
                        {
                            elementfield = fieldNameArr[i];
                            formvalue = $("#"+elementfield).val();
                        }

     readdata[elementfield+'_'+datas_v] =formvalue;

      $("#"+elementfield).val('');

         }



      }



    //redit_jv.push(readdata);
     console.log(readdata);
    xx = 0;


      st = " <table class='table table-striped searchable '><tr><th></th><th> Amount </th> <th> Currency </th> <th> Exchange Rate </th> </tr>";
     xcount = 0;
     count_me = 0;
     xvc = Array();
     $.each(readdata,function(value,key){
     xcount ++;
      xvc = value.split('_');
     // alert(xvc[1]);


          if(xcount == 1)
          {
           //count_me = datas_v--;
             st += "<tr id='xx_"+xvc[1]+"'> <td><a href='javascript:void(0);delet_readout("+xvc[1]+");'><i clas='fa fa-trash'>Del</i> </a> </td> <td> "+key+"</td>";
            
          }
          else if(xcount == 2)
          {
           st += " <td>"+key.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+"</td> ";
          }
          else if(xcount == 3)
          {
          st += " <td>"+key.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")+"</td> </tr> ";
          xcount = 0;
        
           
          }
         console.log("_____"+key);
       // st+="<tr><td>"+;
       });


       datas_v ++;

        
      st +="";
      $(".price_Currency").html(st).fadeIn('slow');
      get_amount(datas_v,readdata);
       console.log(st);
       data_v= 0;
         $("#exchangerate").fadeOut('fast');
       return ;

       });
total_price = 0;
function get_amount(count)
{
	console.log(readdata);
	readout_price = 0;
	exchangerate = 1;
	price_in_ugx = 0;
	price_added = 0;
	total_price = 0;
 
xc = 0;
	while(xc < count)
	{	

		 
			if( readdata['readoutprice_'+xc] > 0)
			{
				if(readdata['exchangerate_'+xc] > 0)
				{
					total_price +=  parseInt(readdata['readoutprice_'+xc]) * parseInt(readdata['exchangerate_'+xc]);
					
				}
				else
				{
					total_price +=  parseInt(readdata['readoutprice_'+xc]) ;
					
				}
				
			}
 	 
 
		xc ++; 

	}

	$(".contractprice").val(total_price).trigger('change');
	//compare prices
		 
}

 
$(".contractprice").change(function(){
	$(".justification_div").fadeOut('slow');
	var contract_px = $(this).val();
	var readout_px = $("#readoutprice_ugx").val();
	if(readout_px.length  <= 0 && contract_px.length > 0 )
	{
		 
	//	alertmsg("Select Best Evaluated Bidder");
		status_contract_px = 0;
		$(".justification_div").fadeOut('slow');
		return ;
	}
	else
	{
		if(contract_px.length <= 0)
		{
			 
			$(".justification_div").fadeOut('slow');
			return;
		}
		
		if(readout_px != contract_px)
		{	 
			 
		      status_contract_px = 0;
		      $(".justification_div").fadeIn('slow');
		      return;
		   
		}
		$(".justification_div").fadeOut('slow');

	}


     
});

 //bid receipt
 function isNumber(input){
    var RE = /^-{0,1}\d*\.{0,1}\d+$/;
    return (RE.test(input));
}



    $('.numbercommas').keyup(function(e){
        if (/[^\d,]/g.test(this.value))
        {
          this.value = this.value.replace(/[^\d,]/g, '');
        }

        $(this).val(addCommas($(this).val()));
    });


  function numonly(vall){
        if (/\D/g.test(vall))
        {
        //Filter non-digits from input value.
        return vall.replace(/\D/g, '');
        }
  }


  function numcomas(vall){
      if (/[^\d,]/g.test(vall))
      {
      vallet = vall.replace(/[^\d,]/g, '');
      return vallet;

      }
    }



    $('.telephone').keyup(function(e){
        if($(this).val().substr(0,3) == '256')
      {
        $(this).val($(this).val().replace(/^256/, '0'));
      }

      if($(this).val().length>10)
        $(this).val($(this).val().substr(0,10));
     });
    // end of additional information 
				</script>
			<script src="<?=base_url()?>js/moverjs.js"></script>

				<script>			
					if(lott == 1)	{	
					
					$(".bebname").val(<?=$pid;?>).trigger('change');
					  status_contract_px = 1;
				   $("#readoutprice").val(<?=$contractprice; ?>).trigger('change');
				      $("#readoutprice_add").trigger('click');
				    //  $(".contractprice").trigger('change');	
			    	}


			 // NOde JS
			 status_responsive_bids = 0;
		stat = 0;
		stat2 = 0;
		
		$(".alert").fadeOut('slow');		
		localbids = $("#local_bids").val();
		total_bids = $("#total_bids").val();
		local_bids = 0;
		num_bids = 0;

		$("#num_bids_local").change(function(){
			 	 
			 	 status_responsive_bids = 0;
			     local_bids = $(this).val();
			 
			 var t = $.isNumeric(local_bids);
          
          if(t == true)
         {
         	 
         	if(local_bids > localbids)
         	{
         	alertmsg("Total Number of Responsive Local Bids Exceeds the Number of Local  receipts Received from Providers");         		
         	return false;
         	}

         }
       	else
        {
             alertmsg('Enter Digits'); return false;

         }


           if((num_bids <= total_bids) && (local_bids <= localbids))
 			status_responsive_bids = 1;
 		    else
 			status_responsive_bids = 0;

		});
		
	
		$("#num_bids").change(function(){
			//alert("passed");
			status_responsive_bids = 0;
			   num_bids = $(this).val();

			 var t = $.isNumeric(num_bids);
          if(t == true)
         {
         	 
         	if(num_bids > total_bids)
         	{
         	alertmsg("Total Number of Responsive Bids Exceeds the Number of receipts  Received from Providers");         		
         	return false;
         	}

         }
       else
        {
             alertmsg('Enter Digits'); return false;

         }
		 


		  if((num_bids <= total_bids) && (local_bids <= localbids))
 			status_responsive_bids = 1;
 		    else
 			status_responsive_bids = 0;

			
			/*if(total_bids > total_bids )
			{
				alert("Number of Bids is Greater than total Bids Added");
				$(".alert").fadeIn('slow');
				$(".alert").html("Number of Bids is Greater than total Bids Added");

			} */
		});


		lott = 1;

	</script>

				<?php
				}


		?>
		<!-- Publish Bidder Information with Lots -->
		<!-- if has lots dont dsiplay this part until selected lot else display -->
<div >

					<div class="accordion-group">
					<div class="accordion-heading">
							 <a class="accordion-toggle" href="#" data-toggle="collapse" data-parent="#accordion-562508"  >Bid Evaluation</a>
					</div>
					<div id="bid_evaluation" class="accordion-body  in ">
					<div class="accordion-inner">
					<div class="row-fluid">

	        <div class="control-group">
	        <label class="control-label"> Type of evaluation <br/>methodology applied</label>
	        <div class="controls">

					<select  class="  chosen evaluationmethods span6"   data-placeholder="Type of Evaluation" tabindex="1" name="evaluationmethods" id="evaluationmethods">
					<?php
						foreach ($evaluation_methods as $key => $value) {
					?>
					<option value="<?=$value['evaluation_method_id']; ?>" <?php if($value['evaluation_method_id'] == $type_oem) {?> <?php }?> ><?=$value['evaluation_method_name']; ?></option>
					<?php
						}
					 ?>
					 </select>

	         </div>
	         </div>

	         <div class="control-group">
	         <label class="control-label"> Date of commencement of the evaluation</label>
	         <div class="controls">
	         <input class=" m-ctrl-medium date-picker   dob_commencement span6"     type="text" value="<?=$ddate_octhe; ?>" id="dob_commencement" name="dob_commencement" />
	         </div>
	         </div>

	         <div class="control-group">
	         <label class="control-label"> Number of technically responsive bids evaluated /  of which were local </label>
	         <div class="controls">
	         <input type="text" class=" span6 num_bids" value="<?=$num_orb; ?>"  datatype="numeric" name="num_bids" id="num_bids">
	         <input type="text" class=" input-medium  num_bids_local" value="<?=$num_bids_local; ?>"  datatype="numeric" name="num_bids_local" id="num_bids_local">
	         
	         </div>
	         </div>

	         <div class="control-group">
	         <label class="control-label">Date of combined  evaluation report</label>
	         <div class="controls">
	         <input class=" m-ctrl-medium date-picker   dob_evaluation span6"     name="dob_evaluation" id="dob_evaluation"  type="text" value="<?=$date_oce_r; ?>" />
		       </div>
	         </div>

	         <div class="control-group">
	         <label class="control-label">Date of approval of the final  evaluation report by the contracts committee</label>
	         <div class="controls">
	         <input class=" m-ctrl-medium date-picker  dob_cc span6" type="text" value="<?=$date_oaoterbt_cc;?>" id="dob_cc" name="dob_cc" />
		       </div>
	         </div>
					 <?php
							#print_r($evaluation_methods);
					 ?>
						</div>
						</div>
						</div>
						
				  	</div>


					<div class="accordion-group">
						<div class="accordion-heading">
						<a class="accordion-toggle"  href="#" data-toggle="collapse" data-parent="#accordion-562508"  >Best Evaluated Bidder Details </a>
						</div>
						<div id="bidder_details_contactprice" class="accordion-body  in ">
							<div class="accordion-inner">
							<div class="row-fluid">


                  <?php
              #    print_r($lots);

                  if(!empty($lots))
                  {
                  ?>


                  <div class="control-group">
                    <label class=" control-label" >
                    Select Lot
                    </label>
                    <div class="controls">
                      <?php
                        //print_r($lots[0]);
                      ?>
                      <select class="span6 ifbslot" id="ifbslot" name="ifbslot" dataref="selecc">
                      <option value="0"> Select  </option>
                      <?php
                      foreach ($lots as   $record) {
                      ?>
                      <option value="<?php  print_r($record['id']); ?>" >   <?= $record['lot_title']; ?> </option>
                      <?php
                      }
                      ?>

                      </select>
                    </div>
                 </div>


                  <div class="control-group">
                    <label class=" control-label" >
                   Added Lots
                    </label>
                    <div class="controls lotted_bebs">
                     <table class="table table-stripped " style="width:50%;">
                    <tr>
                    	<th>Lot title</th>
                    	<th> BEB </th>
                    </tr>
                    <?php
						if(!empty($lots_added))
						{
							foreach ($lots_added as $key => $record) {
								# code...
								 	$query = $this->db->query("select * from providers where providerid in(".rtrim($record['providers'],',').")")->result_array();
								?>
									<tr>
				                    	<th><?= $record['lot_title']; ?></th>
				                    	<th> <?= $query[0]['providernames'] ?> </th>
				                    </tr>
								<?php
							}
							 
						}
					?>
			 
                    </table>
                    </div>
                 </div>



                <?php } ?>

<script>
 
  


   
  </script>


	 						<div class="control-group">
	                   <label class="control-label"> Name of Bidder</label>
	                   <div class="controls">
	                   <select class="span6 chosen selectedbeb bebname" id="bebname" name="bebname" data-placeholder="BEB Name" tabindex="1" onChange="javascript:updatelist(this.value)">
										 <option  data-readoutprice="" data-country="" value="0"> Select Bidder</option>
										 <?php
										 foreach ($providerslist as $key => $value) {
	if(((strpos($value['providerid'] ,",")!== false)) &&  (preg_match('/[0-9]+/', $value['providerid'] )))
	{
		$providers  = rtrim($value['providerid'],",");
		$query = mysql_query("select * from providers where providerid in (".$providers.") ");
		$row = mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("".mysql_error());
	 	$provider = "";
		while($vaue = mysql_fetch_array($row))
		{
			$provider  .=strpos($vaue['providernames'] ,"0") !== false ? '' : $vaue['providernames'].' , ';
		}
		$prvider = rtrim($provider,' ,');
		?>
    <option data-readoutprice="<?=$value['read_price']; ?>"  data-country="<?=$value['nationality']; ?>" value="<?=$value['receiptid'] ?>"><?=$prvider.' &nbsp [JV] '; ?>     </option>
    <?php
		#print_r($prvider);

	  }
	  else
	  {
		$query = mysql_query("select * from providers where providerid = ".$value['providerid']);
		$records = mysql_fetch_array($query);
	#	echo $records['providernames'];
		?>
    <option  data-readoutprice="<?=$value['read_price']; ?>"  data-country="<?=$value['nationality']; ?>"  value="<?=$value['receiptid'] ?>"><?= $records['providernames']; ?>     </option>
    <?php
	  }

											# code...
										/*	?>
											<option value="<?=$value['providerid']; ?>"><?=$value['providernames']; ?></option>
											<?php */
										   }
										?>
		 </select>
	   </div>
		 <?php
		 #print_r($providerslist[0]);
		 ?>
		</div>

								 <div class="control-group">
	                              <label class="control-label"> Country of Registration</label>
	                             <div class="controls">
	                             <select class="span6 chosen beb_nationality" name="beb_nationality" data-placeholder="Nationality" tabindex="1" id="beb_nationality">
											<?php
											$que = mysql_query("SELECT * FROM countries") or die("".mysql_error());
											while ($ans = mysql_fetch_array($que)) {
												# code...
												if($ans['country_name'] == 'uganda')
												{
													?>
													<option selected value="<?=$ans['country_name']; ?>" <?php if($ans['country_name'] == $nationality){ ?> selected <?php } ?>> <?=$ans['country_name'] ?> </option>

													<?php
												}else
												{
												?>
													<option value="<?=$ans['country_name']; ?>"> <?=$ans['country_name'] ?> </option>
												<?php
											}
											}
											?>


									</select>
	                             </div>


								  	<?php
								  	#print_r($providerslist[0]);
								  	?>

								 </div>



								 <div class="control-group">
	                              <label class="control-label"> Date BEB Expires</label>
	                            <div class="controls">
	                            <input class=" m-ctrl-medium date-picker   date_beb_expires span6"     name="date_beb_expires" id="date_beb_expires"  type="text" value="<?=$date_beb_expires; ?>" />
		                         </div>


								  	<?php
								  	#print_r($providerslist[0]);
								  	?>

								 </div>









							</div>
						</div>
					</div>


			<div class="accordion-group">
					<div class="accordion-heading">
							 <a class="accordion-toggle" href="#" data-toggle="collapse" data-parent="#accordion-562508" >Contract Price</a>
					</div>
						<div id="unsuccessful_bidders" class="accordion-body  in ">
							<div class="accordion-inner">
					  			<div class="row-fluid">
					  					<div class="row-fluid">
        <div class="control-group">
				<label class=" control-label">   </label>
        <div class="controls">
          <input type="hidden" class=" numbercommas2  span6 readoutprice_ugx" datatype="money" id="readoutprice_ugx" name="readoutprice_ugx" value="<?=!empty($readoutprice_ugx) ? number_format($readoutprice_ugx) : ''; ?>">
	      <input type="hidden" class=" numbercommas2  span6 contractprice" datatype="money" id="contractprice" name="contractprice" value="<?=!empty($contractprice) ? number_format($contractprice) : ''; ?>">
      &nbsp;
      <?php
       $recod = mysql_query("select * from currencies ") or die("".mysql_error()) ;
       ?>
      <input type='hidden' class="input-medium chosen currence" data-placeholder="currence " id="currence" name="currence" tabindex="1" value="UGX" >
 
		 



        </div>

        <!-- Contract Price -->
        <br/>
        <!-- Add Contract Price -->
             <div class="row-fluid">
              <div class="control-group">
              <label class="control-label">Contract Price * : </label>
              <div class="controls">

              <select class="input-small  chosen currency" data-placeholder="Currency " id="currency" name="currency" tabindex="1">
               <?php
          while($cur  =  mysql_fetch_array($recod)){
          ?>
          <option><?php print_r($cur['title']); ?> </option>
          <?php
          }
          ?>
              </select>
               <input type="text" id="readoutprice"  placeholder="Contract  Price" style="margin-left:5px;" datatype="money" name="readoutprice" datatye="money"  class="readoutprice input-medium numbercommas"  value="<?=!empty($contractprice) ? number_format($contractprice) : ''; ?>"       />
               <input type="text" id="exchangerate"  placeholder="Exchange Rate" style="margin-left:5px;display:none" datatype="money" name="exchangerate" datatye="money"  class="readoutprice input-medium numbercommas "  />

 
    
              <button type="button" name="save" value="save"   id="readoutprice_add" data-elements="*readoutprice<>*currency<>exchangerate" class="btn blue readoutprice_add"  > Add </button> 

              </div>

              <!-- Readout Price -->
              <br/>
               <div class="controls price_Currency" style="width:50%;">
		        </div>
              <!-- End -->

<br/>
                
           </div>
            </div>
      <!-- End -->
      <!-- Provide Justification -->
       <div  style="display:none;" class="row-fluid  justification_div"  >
              <div class="control-group">
              		<label class="control-label">Enter Justification </label>

		              <div class="controls">
				            <textarea class="formcontrol span4 justification" id="justification" required>	            	
				            <?=!empty($justification) ? trim($justification) : ''; ?>
				            </textarea>
		              </div> 
                
               </div>
        </div>


      <!-- End -->
        <!-- End -->
			</div>


		</div>



							</div>
						</div>
					</div>


					<div class="accordion-group">
						<div class="accordion-heading">
							 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-562508"  >Unsuccessful Bidders</a>
						</div>
						<div id="unsuccessful_bidders" class="accordion-body  in ">
							<div class="accordion-inner">
								   <div class="row-fluid" id="unbidderlist">
										<?php


	#print_r($unsuccesful_bidders);

				?>
				<!--
						<table class="table table-striped" id="sample_1">
					<thead>
						<tr>
							<th>
								#
							</th>
							<th>
								Bidders Name
							</th>
							<th>
								Bidder Nationality
							</th>
							<th>
								Reason
							</th>




						</tr>
					</thead>
					<tbody>
-->
						<?php
						/*
						$x = 0;
						foreach ($unsuccesful_bidders as $key => $value) {
							# code...
							$x ++;
							?>
							<tr id="<?=$value['receiptid'] ?>" dataid="<?=$value['receiptid'] ?>">
							<td>
							 <?= $x; ?>
							</td>
							<td>
								 <?=$value['providernames'] ?>
							</td>
							<td>
								 <?=$value['nationality'] ?>
							</td>
							<td>
								<select class="span12 " data-placeholder="Nationality" tabindex="1" onChange="javascript:reason(this.value,<?=$value['receiptid'] ?>)">
								   <option value="0">Select Reason </option>
								   <option value="Over priced" > Over priced </option>
								 </select>
							</td>
						</tr>
							<?php
						} */

						?>



	<!-- </table> -->
	<div class="alert alert-info">
                  <button data-dismiss="alert" class="close">×</button> 
      No Best Evaluated Bidder Selected
       </div>
 

					 </div>



							</div>


						</div>
					</div>
			<!--	<button type="submit" name="view" value="view" class="btn blue plishtype"><i class="icon-folder-open"></i> View</button>
			-->
				<button type="submit" name="save" value="save" class="btn blue plishtype " <?php if(!empty($lots)){  ?> onClick="javascript:btn_status(1)" <?php } else { ?>  onClick="javascript:btn_status(1)" <?php } ?> >  Update Lot  <?php if(!empty($lots)){  echo" And Add"; } ?></button>
				<button type="reset" name="reset" value="reset" class="btn blue resettn "  >  Clear </button>
        <?php if(!empty($lots)){ ?>	<button type="submit" name="save" value="save" class="btn blue plishtype" onClick="javascript:btn_status(2)" > Save   And Finish </button> <?php } ?>
         <?php if(!empty($lots)){  ?>  	<a  href="<?=base_url()."receipts/manage_bebs"; ?>"    class="btn" >  FINISH</a>
           <?php  } ?>
			 <?php if(empty($lots)){  ?>	<button type="submit" name="publish" value="publish" class="btn plishtype" onClick="javascript:btn_status(1)">Update and  Publish </button> <?php } ?>

				<a  href="<?=base_url()."receipts/manage_bebs"; ?>"    class="btn" >  FINISH</a>


				</div>
			</form>
			</div>
			</div>
		<!-- End --> 
		<?php
	

}