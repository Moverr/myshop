//victors code

 
 //end of procurement scripts
// comfirm delete victor
function confirmDeleteEntity(url,message){
    if(window.confirm(message)){
        window.location.href=url;
    }
}


//baser utl victor
//baser utl victor
function getBaseURL(){
    var pageURL=document.location.href;
    var urlArray=pageURL.split("/");
    var BaseURL=urlArray[0]+"//"+urlArray[2]+"/";

    if(urlArray[2]=='localhost') 
BaseURL=BaseURL+'myshop/';
  else
     BaseURL=BaseURL;
 
    return BaseURL;
}



// instant check mover, :: pending approval on ::
var statusquo = 1;
function instantcheck(st,url)
{
   //   $(".alert").fadeOut('slow');
  $(".alert").fadeIn('slow');
  $(".alert").html("Proccessing ... ");
     form_data = {pdename:st};

      $.ajax({
        url:  url,
         type: 'POST',
        data: form_data,
        success: function(data, textStatus, jqXHR){

            console.log(data);
            if(data == 0)
            {
            statusquo = 1;
             $(".alert").fadeOut('slow');
            }
            else
            {

         $(".alert").fadeIn('slow');$(".alert").html(data);
        $('html, body').animate({scrollTop : 0},800);
        statusquo = 0;
            }


        },
        error:function(data , textStatus, jqXHR)
        {
             console.log(data);
        return 0;
        }
    });
}
function fetchproc_method(){

if((procurementtype > 0) && (estimated_amount > 0)){
  if(status_level == 0)
    alertmsg("Proccessing  Threshold please wait ... ");

  // $('#procurement-method').empty().append("<option> Proccessing.. </option>");
   //console.log("Proccessing...");
    url = getBaseURL()+"procurement/fetch_procurement_methods_json";
    $.ajax({
        url:  url,
         type: 'POST',
        data: formdata,
        success: function(data, textStatus, jqXHR){

     // $('#procurement-method').empty().append(data);
            console.log(data);
      threshold = JSON.parse(data); 
      console.log(threshold);  
      $(".alert").fadeOut('slow');
         /*    if(procumethod > 0)
            $("#procurement-method").val(""+procumethod+"");      
      */
 check_justification();

        },
        error:function(data , textStatus, jqXHR)
        {
             console.log(data);

        }
    });
}

}
function inArray(needle, haystack) {  
     var length = haystack.length;    
      for(var i = 0; i < haystack.length; i++)
      {
      console.log(haystack[i]);  
        if(haystack[i] == needle) 
          return true; 
      }
      return false;    
}


msg = 0;

entry_proc_method = '';
  ifb_proc_method = '';

console.log("INITIALIZING::");
console.log(entry_proc_method);
  // alert messages ::
   function alertmsg(msg){
    $(".alert").fadeOut('slow');
    $(".alert").fadeIn('slow');$(".alert").html(msg);
    scrollers();

    }
     function alertmsgs(){
        $(".alert").fadeOut('slow');
    }
  
  
    function scrollers(){
    $('html, body').animate({scrollTop : 0},800);
     }

 // check ifb method for justification 
function check_ifb_method_for_justificatin(ifb_method)
{
 // alert(ifb_method);
  ifb_proc_method = ifb_method;
  //alert(ifb_method);
  //alert(entry_proc_method);
  if(entry_proc_method != ''  && ifb_method != '' )
  {
    if(ifb_method != entry_proc_method)
    {
      $("#ifb_method_justification_status").val('Y');
      $(".justification_panel").fadeIn("slow");       
    }
    else
    {
       $("#ifb_method_justification_status").val('N');
      $(".justification_panel").fadeOut("slow");    
    }
  }
  else
  {
   
    
  if((ifb_method != '' ) && (msg == 0) ){
    alertmsg("Select Subject of Procurement ");
  }
    else
    {
       $("#ifb_method_justification_status").val('N');
        $(".justification_panel").fadeOut("slow");    
    }
  }
}




// DUE FUNCTIONALITY
$(function(){




         $(".invitation_to_bid_date").change(function(){

            

         var invitation_to_bid_date =    $(this).val();               
          

             url = baseurl()+'bids/validate_ifb_date/';
           // formdata['method'] = 
            formdata['invitation_to_bid_date'] =  invitation_to_bid_date;
            formdata['entry_proc_method'] = entry_proc_method;
             formdata['ifb_proc_method'] = ifb_proc_method;

            

            console.log("data :passed");
           console.log(formdata);
             console.log("data :End");
           
            $.ajax({
                type: "POST",
                url: url,
                data: formdata,
                success: function (data, textStatus, jqXHR) {
                    console.log(data);

                    $("#bid_submission_deadline").val(data);
                    
                },
                error: function (data, textStatus, jqXHR) {
                    console.log("SERVER ERROR " + data);

                }
            });


         })

       // procurementtype = 0;
       // exchange_rate = 0;
        //estimated_amount = 0;
       // formdata = {};
    xc = 0;
    check_justification =  function()
{
   
   
     if(threshold == '' || procurementmethod <= 0)           
         return;

       console.log(":::::::::::::::::::::");
       console.log(procurementmethod);
       console.log(threshold);
       console.log("-----------------------");


 
       var status =   inArray(procurementmethod, threshold);
      
        console.log(status);
       if(status == false)
       {
        if(status_level==0)
         alertmsg("Please Justify Why You Chose this Procurement Method");
         $(".justification_field").fadeIn("slow");
         $("#justification").attr("required","required");
         $("#justified").val('Y');
         $("#justification").attr("style","border:1px solid RED;");
       }
       else
       {
         $(".alert").fadeOut('slow');        
         $("#justification").removeAttr("required");
         $(".justification_field").fadeOut("slow");
         $("#justified").val('N');
       }
     
     if(xc > 1)
     status_level = 0;
     xc ++;
}


     
       

      $("#procurement_method").change(function(){


            console.log("Hello");
            procurementmethod = this.value;  
             formdata['procurement_method']   = procurementmethod;            
         
             fetchproc_method(); 

           
        
      });

 



    


      $("#procurement_type").change(function(){
            procurementtype = this.value;
            formdata['procurement_type']   = procurementtype;
            console.log(formdata); //  alert(procurementtype);
            formdata['procurement_method'] =   procurementmethod =   $(".procurement_method").val(); 
       // alert(procurementmethod);

           
            fetchproc_method();
         
        

        });


         $("#exchangerate").change(function(){
                 exchange_rate = this.value;
                 exchange_rate = exchange_rate.replace(/\D/g,'');
                 formdata['exchange_rate']   = exchange_rate;
                 console.log(formdata);

                  $("#exchangerate").val(addCommas(exchange_rate));
                //   if(threshold == '')
           // {             
            fetchproc_method();
           // }
                   // check_justification();
                      //  alert(procurementtype);

        });

            $("#estimatedamount").change(function(){

             estimated_amount = this.value;
             estimated_amount = estimated_amount.replace(/\D/g,'');
             formdata['estimated_amount']   = estimated_amount;

            
            $("#estimatedamount").val(addCommas(estimated_amount));
                      
            fetchproc_method();
            
       //check_justification();
        });

    if(estimated_amount > 0 || estimated_amount !='undefined' )
  {
    estimated_amount = estimated_amount;
    procurementtype = $("#procurement_type").val();
    exchange_rate =   $(".exchangerate").val();
    formdata['procurement_type'] =  procurementtype;
    formdata['estimated_amount']  = estimated_amount;
    formdata['exchange_rate'] = exchange_rate;

    $(".estimatedamount").trigger('change');
                    
       fetchproc_method(); 

      
       
  }
  //estimatedamounts = $(".estimatedamount").val();
  //estimatedamounts = estimatedamounts.replace(/\D/g,'');


  $('.numbercommas').change(function(e){ 

     //Replace this Value with commas with space
     var str =   this.value;
     str = str.replace(/\,/g,'');
     this.value =  str;


      if(isNaN(this.value) == false)
      {
             this.value = Math.round(this.value);
      }
      else
      {        
        

            if (/[^\d,]/g.test(this.value))
            {
              this.value = this.value.replace(/[^\d,]/g, '');
            }

             this.value = Math.round(this.value);

      }
            

      $(this).val(addCommas($(this).val()));
    });

  $('.numbersonly').keyup(function(e){
      if (/\D/g.test(this.value))
      {
          // Filter non-digits from input value.
          this.value = this.value.replace(/\D/g, '');
      }
  });


  $('.telephone').keyup(function(e){
      if($(this).val().substr(0,3) == '256')
    {
      $(this).val($(this).val().replace(/^256/, '0'));
    }

    if($(this).val().length>10)
      $(this).val($(this).val().substr(0,10));
  });


  //IFB BUTTONS STATUS LOGGER
  
function ifb_button_status()
{
   if(procurement_entry_id <= 0)
    return;
  alertmsg("Proccessing More Settings ");
  var formdata = {};
  formdata['proc_id'] = procurement_entry_id;
 var getUrl = getBaseURL() + 'bids/json_encode_procurement_entry';

   $.ajax({
                url: getUrl,
                type: 'POST',
                data: formdata,
                success: function(data)
                {
                  var open_method = "1<>2<>9<>11";
                  var json = data;
                  obj = JSON.parse(json)
                  console.log(obj);   
                  
                  
                 
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

            });

}

function check_ifb_method()
  {
     
   
     var open_method = "1<>2<>9<>11";
     splitted_method = open_method.split("<>");
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
            console.log("Pass and Publish");
                $(".publish_button").fadeIn('slow');      
                $(".savebutton").fadeOut('slow');         
                    }
                    
  }

  var fib_method  = 0;

$("#procurement_method_ifb").change(function(){
  
  
  
  if(revenue == undefined)
    procurement_entry_details();
    /*
  IFB METHOD 
  */
 
 fib_method = $(this).val();
 
   if(fib_method == 8)
      {
        $(".displayofbebnotice").fadeOut('slow');
      }else{
        if(entry_proc_method != 8 )
        $(".displayofbebnotice").fadeIn('slow');
      }

/*
if Method is Expression of Interest tagged to ID 11
*/
 if(fib_method  == 11)
      {
       $(".expression_of_interest").fadeIn('slow');
                                  $(".label_notice_exp1").html("'Publication of Notice of Expression of Interest");
                                  $(".label_notice_exp2").html("Closing date for receipt of Expression of Interest");
                                  $(".label_notice_exp3").html("Evaluation of Expressions of Interest");
                                                                     $(".label_notice_exp4").html("Display of shortlist");
                                                                     
                                                                      $(".bid_receipt_address").html(" Address EOI  must be delivered to: ");
                                      $(".bid_openning_address").html(" EOI opening address:");
                                       $(".bidvalidity").html("EOI Validity Expires On");
                                         $(".bid_security").html("EOI security: ");
                                           $(".pre_bid_meeting_date").html("Pre-EOI meeting date:");
                                                                     $(".label_bidding_documents").html("Price of EOI  documents: ");
                                                                        $(".bid_opening_date").html("EOI Opening Date: ");
  
                                                               
                                                          
       }else{

  // if method of procurement aint changed then run the damn thing
         if(fib_method != '')
     {
       $(".expression_of_interest").fadeOut('slow');
                                      $(".label_notice_exp1").html("Invitation to bid date");
                                    $(".label_notice_exp2").html("Deadline of bid submission");
                                    $(".label_notice_exp3").html("Bid evaluation period");
                                    $(".label_notice_exp4").html("Display of BEB notice");
                                   
                                      
                                       $(".bid_receipt_address").html(" Address bids must be delivered to:");
                                                                  $(".bid_openning_address").html(" Bid opening address:");
                                                                    $(".bidvalidity").html("Bid Validity Expires On");
                                                                      $(".bid_security").html("Bid security:");
                                                                       $(".pre_bid_meeting_date").html(" Pre-bid meeting date:");
                                                                    $(".label_bidding_documents").html("Price of bidding documents:");
                                                                      $(".bid_opening_date").html("Bid Opening Date: ");
                                                          
                              
                                                       
                                                          
     }
         
    
    
      }


        // IFB Method 
        if(fib_method == 10)
                {
                $(".option_ifbs").fadeOut('slow');

                }
                else
                { 
              $(".option_ifbs").fadeIn('slow');
                    if((entry_proc_method == 10) && (fib_method == '') )
                    {
                    $(".option_ifbs").fadeOut('slow');
                    }
                
                }
        

console.log("IFB Method triggered");
if(revenue =='Y')
  $(".quantity_div").fadeOut('slow');
else
 {
   if(quantifiable == 'Y')
   $(".quantity_div").fadeIn('slow');
   else 
    $(".quantity_div").fadeOut('slow');
}
 
  var selected_method = $(this).val();
  console.log("IFB METHOD :"+selected_method);
  if(selected_method > 0)
    obj.procurement_method = selected_method;



/*
Trigger IFB Change if
its not an Edit 

*/
if(view == 1){

  var record =  $(".invitation_to_bid_date").val();
   $(".invitation_to_bid_date").val(record).trigger("change");
}
//Trigger Change Invitation to Bid Date Value  and See what happens
   

    check_ifb_method();



})

splitted_method = 0;

 
//fetch procurement entry details
function procurement_entry_details()
{
  
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
                  var open_method = "1<>2<>9<>11";
                  var json = data;
                  obj = JSON.parse(json)
                  console.log(obj);   
                  var ifb_address = '';
                   ifb_address =  obj.pde_address;                  
                  if(branch_shortcode.length > 0)                  
                    ifb_address =  obj.branch_address;
                  
                   
            revenue = (revenue.length > 0) ? revenue : obj.revenue;
          quantifiable = (quantifiable.length > 0) ? quantifiable : obj.quantifiable;
          // procurement_entry_details
                  console.log("487 :REVENUE STATUS "+revenue);
        
      
                //$("#documents_inspection_address").val(ifb_address);
        
        entry_proc_method = obj.procurement_method; 
        
        
          if(entry_proc_method == 8)
      {
        $(".displayofbebnotice").fadeOut('slow');
      }else{
        if(entry_proc_method != 8 )
        $(".displayofbebnotice").fadeIn('slow');
      }

/*
if Method is Expression of Interest tagged to ID 11
*/
  if(entry_proc_method == 11)
      {
        $(".expression_of_interest").fadeIn('slow');
                                  $(".label_notice_exp1").html("'Publication of Notice of Expression of Interest");
                                  $(".label_notice_exp2").html("Closing date for receipt of Expression of Interest");
                                  $(".label_notice_exp3").html("Evaluation of Expressions of Interest");
                                                                     $(".label_notice_exp4").html("Display of shortlist");
                                                                     
                                                                      $(".bid_receipt_address").html(" Address EOI  must be delivered to: ");
                                      $(".bid_openning_address").html(" EOI opening address:");
                                       $(".bidvalidity").html("EOI Validity Expires On");
                                         $(".bid_security").html("EOI security: ");
                                           $(".pre_bid_meeting_date").html("Pre-EOI meeting date:");
                                                                     $(".label_bidding_documents").html("Price of EOI  documents: ");
                                                                        $(".bid_opening_date").html("EOI Opening Date: ");
  
                                                               
    
       }else{
         if((entry_proc_method != 11) && (fib_method  != 11) )
     {
$(".expression_of_interest").fadeOut('slow');
                                      $(".label_notice_exp1").html("Invitation to bid date");
                                    $(".label_notice_exp2").html("Deadline of bid submission");
                                    $(".label_notice_exp3").html("Bid evaluation period");
                                    $(".label_notice_exp4").html("Display of BEB notice");
                                   
                                      
                                       $(".bid_receipt_address").html(" Address bids must be delivered to:");
                                                                  $(".bid_openning_address").html(" Bid opening address:");
                                                                    $(".bidvalidity").html("Bid Validity Expires On");
                                                                      $(".bid_security").html("Bid security:");
                                                                       $(".pre_bid_meeting_date").html(" Pre-bid meeting date:");
                                                                    $(".label_bidding_documents").html("Price of bidding documents:");
                                                                      $(".bid_opening_date").html("Bid Opening Date: ");
                                                          
     }
        
      }


    

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
          console.log("PASER PASER");
        
          
          
          
          console.log("Answer : PASSED");
          
                            if(revenue == 'N'){
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

var procurement_entry_id = 0;


var procurement_entry_id = 0;
var branch_shortcode = '';

 $('#pde_branch').change(function(){
     branch_shortcode = $(this).val();    
     
     if((procurement_entry_id!=0)){
      formdata = {proc_id:procurement_entry_id};
      formdata['branch_shortcode'] = branch_shortcode;
      alertmsg('Proccessing Shortcode');
      //get stuff
        $.ajax({
                        url: getBaseURL() + 'bids/loadprocurementrefno',
                        type: 'POST',
                        data: formdata,
                        success: function(msg)
                        {
                          $(".alert").fadeOut('slow');
                             if(msg != 0)
                            {
                             $("#sequencenumber").removeClass("hidden");
                             $("#sequencenumber").val(msg);
                             console.log(msg);
                              
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
             

     }
        
 });

  $('#procurementref-no').change(function(){
    var getUrl = getBaseURL() + 'bids/procurement_record_details';

    if($(this).hasClass('get_beb'))
      getUrl += '/b/get';
      console.log(getUrl);
      formdata = {proc_id: $(this).val()};
      branch_shortcode = $("#pde_branch").val();
      formdata['branch_shortcode'] = branch_shortcode;
      procurement_entry_id = $(this).val();
    $("#sequencenumber").val("");
    $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
            $.ajax({
                url: getUrl,
                type: 'POST',
                data: formdata,
                success: function(msg)
                {
                    $("#procurement_plan_details").html(msg);
                      //get the procurement ID ::

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
                           //fetch procurement entry details 
                             procurement_entry_details();
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
                    //end of events
                }
            });
  });

  
  
  
  var fetchlots = function(formdata){
   
    $(".lotinfo").fadeOut('slow');
    var formdta = formdata;
    formdta['bidid'] = formdata['bidinvitationid'];
    formdta['procurementrefno'] = formdata['procurementrefno'];
    var getUrl = getBaseURL() + 'receipts/get_beb_lots_ajax';
    console.log(formdata);
    $.ajax({
            url: getUrl,
            type: 'POST',
            data: formdta,
            success: function(data)
            {

              if(data != 0)
              {
                  $(".optionlots").children().remove().end().append(data);
                  $(".lotinfo").fadeIn('slow');
                //  $(".chosen").trigger("chosen:updated")
              //    $("#optionlots").trigger("change")
                   console.log(data);
              }
              else {

              }

            },
            error:function(data)
            {
            console.log(data);
            }
    });


  }
  
    //get bebs on selected lots under contracts
  $("#optionlots").change(function(){
    var getUrl = getBaseURL() + 'receipts/fetchbeb_lots_ajax';
    var lotid = this.value;
    var formdata = {};
    formdata['lotid'] = lotid;
  //  alertmsg("Proccessing BEB ... ");
    $(".bebawarded").html('Processing BEB ..');
    $.ajax({
    url: getUrl,
    type: 'POST',
    data: formdata,
    success: function(data)
    {
      console.log(data);
      if(data != 0)
      {
        $(".bebawarded").html("<h4>SELECTED PROVIDER </h4> <input type='hidden' id='providerstatus' value='0' /> "+data);
        $(".savebtn").attr('name','saveadd');
          $(".savebtn").html('Save And Add ');
      }else {
        $(".bebawarded").html("No BEB Selected");
      }

    },
    error:function(data)
    {
      console.log(data);
    }
    });


  });
  
    // procurement ref no on change contracts
  // Procurement Ref Number on Change
  $('#procurement-ref-no-contracts').change(function(){

    var getUrl = getBaseURL() + 'bids/procurement_recorddetails_contracts';
    var referenceno =   this.options[this.selectedIndex].text


    if($(this).hasClass('get_beb'))
      getUrl += '/b/get';
      console.log(getUrl);
     // alert(this.text);
      var reference_bidinvitation = $(this).val();
      var refe_bid = reference_bidinvitation.split("__");
      //alrt("reached");
     console.log(refe_bid);
      formdata = {proc_id:refe_bid[0]};
      formdata['procurementrefno'] = referenceno;
      formdata['bidinvitationid'] = refe_bid[1];
    formdata['receiptid'] = refe_bid[2];
      console.log(formdata);
    $("#sequencenumber").val("");
    $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
            $.ajax({
                url: getUrl,
                type: 'POST',
                data: formdata,
                success: function(msg)
                {
                    $("#procurement_plan_details").html(msg);
                      //get the procurement ID ::

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

                             //fetch lot information
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

            fetchlots(formdata);

                    //end of events
                }
            });
  });
  
  
  
  
  
  
 // Procurement Ref Number on Change
  $('#procurement-ref-no').change(function(){

    var getUrl = getBaseURL() + 'bids/procurement_recorddetails';
    var referenceno =   this.options[this.selectedIndex].text


    if($(this).hasClass('get_beb'))
      getUrl += '/b/get';
      console.log(getUrl);
     // alert(this.text);
      var reference_bidinvitation = $(this).val();
      var refe_bid = reference_bidinvitation.split("__");
      //alrt("reached");
     console.log(refe_bid);
      formdata = {proc_id:refe_bid[0]};
      formdata['procurementrefno'] = referenceno;
      formdata['bidinvitationid'] = refe_bid[1];
      console.log(formdata);
    $("#sequencenumber").val("");
    $("#procurement_plan_details").html('<img src="../images/loading.gif" />');
            $.ajax({
                url: getUrl,
                type: 'POST',
                data: formdata,
                success: function(msg)
                {
                    $("#procurement_plan_details").html(msg);
                      //get the procurement ID ::

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

                             //fetch lot information
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

            fetchlots(formdata);

                    //end of events
                }
            });
  });
  
  
$('#report-type').change(function(){
    if($(this).val() == 'PP')
    {
      $('.procurement_plan_control, .neutral_control').css({display: 'block'});
    }
    else if($(this).val() == 'LP')
    {
      $('.procurement_plan_control, .neutral_control').show();
      $('.procurement_plan_control').hide();
    }
    else
    {
      $('.procurement_plan_control, .neutral_control').hide();
      $('.procurement_plan_control').hide();
    }
  });


$('#ifb-report-type').change(function(){
    if($(this).val() == 'PIFB')
    {
      $('.bid_submission_control').hide();
      $('.published_ifb_control').show();
      $('.exception_control').hide();
    }
    else if($(this).val() == 'IFBD')
    {
      $('.bid_submission_control').show();
      $('.published_ifb_control').hide();
      $('.exception_control').hide();
    }
    else if($(this).val() == 'BER')
    {
      $('.exception_control').show();
      $('.bid_submission_control').hide();
      $('.published_ifb_control').hide();
    }
    else
    {
      $('.exception_control').hide();
      $('.bid_submission_control').hide();
      $('.published_ifb_control').hide();
    }
  });


$('#beb-report-type').change(function(){
    if($(this).val() == 'PBEB')
    {
      $('.exception_control').hide();
      $('.beb_publish_date_control').show();
      $('.beb_expiry_date_control').hide();
    }
    else if($(this).val() == 'EBN')
    {
      $('.beb_expiry_date_control').show();
      $('.exception_control').hide();
      $('.beb_publish_date_control').hide();
    }

    else if($(this).val() == 'BER')
    {
      $('.exception_control').show();
      $('.beb_publish_date_control').hide();
      $('.beb_expiry_date_control').hide();
    }
    else
    {
      $('.exception_control').hide();
      $('.beb_publish_date_control').hide();
      $('.beb_expiry_date_control').hide();
    }
  });


$('#contracts-report-type').change(function(){
    if($(this).val() == 'LC' || $(this).val() == 'CDC')
    {
      $('.completion_control').show();
      $('.late_control').show();
      $('.contract_award_control').hide();
      $('.contracts_completed_control').hide();

    }
    else if($(this).val() == 'AC')
    {
      $('.contract_award_control').show();
      $('.completion_control').hide();
      $('.late_control').hide();
      $('.contracts_completed_control').hide();
    }
    else if($(this).val() == 'CC')
    {
      $('.contracts_completed_control').show();
      $('.contract_award_control').hide();
      $('.completion_control').hide();
      $('.late_control').hide();
    }
    else
    {
      $('.contract_award_control').show();
      $('.completion_control').hide();
      $('.late_control').hide();
      $('.contracts_completed_control').hide();
    }
  });


//search query victor
$('#search-query').on('keyup', function(){

        var form_data = {searchQuery: $(this).val()};


        if(getBaseURL() != $('#search-form').attr('action')){

            $("#results").html('<img src="'+baseurl()+'images/loading.gif" />');
            $.ajax({
                url: $('#search-form').attr('action'),
                type: 'POST',
                data: form_data,
                success: function(msg)
                {
                   // alert(msg);
                    $("#results").html(msg);
                }
            });
        }
        else{
            //alert(getBaseURL());
        }
    });



$('.permission_section>div').click(function(){
        if($(this).next('ul').css('display') == 'block')
        {
            $(this).next('ul').slideUp();

        }else{
            $('.permission_section ul').slideUp();
            $(this).next('ul').slideDown();
        }
    });

$('.permission_section ul span.permission').click(function(){
        var thisCheck = $(this).parent('li').find('input.check_permission:first');

        if($(thisCheck).is(':checked'))
        {
            $(thisCheck).prop('checked', false);
            $(thisCheck).parent('span').removeClass('checked');

        }else{
            $(thisCheck).prop('checked', true);
            $(thisCheck).parent('span').addClass('checked');
        }
    });


   $('.actived').on('click', function(){

    var pid = $(this).closest('tr').attr('id');
    var decider = pid.split('_');
    var pdename = $("#pdaname_"+decider[1]).html();

            switch(decider[0]){
                case 'active':
                url = baseurl()+'pdes/fetchdetails/'+decider[1];
                viewdetails(pid,url,pdename);
                break;
                case 'archive':
                url = baseurl()+'pdes/fetchdetails/'+decider[1];
                viewdetails(pid,url,pdename);
                break;
                default:
                break;
            }


        });



// MANAGE DELETE RESORE AND UPDATE FUC
$('.savedelreceipt').on('click', function(){


    var decider = this.id;
    var idq =  decider.split('_');

     switch(idq[0])
     {
        case 'savedelreceipt':
        url = baseurl()+'receipts/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'restore':
        url = baseurl()+'receipts/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Restore a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'del':
        url = baseurl()+'receipts/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Paramanently Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        default:

        break;
     }

});







// DISPOSAL MANAGE DELETE RESORE AND UPDATE FUC
$('.savedeldisposalreceipt').on('click', function(){


    var decider = this.id;
    var idq =  decider.split('_');

     switch(idq[0])
     {
        case 'savedelreceipt':
        url = baseurl()+'disposal/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'restore':
        url = baseurl()+'disposal/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Restore a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'del':
        url = baseurl()+'disposal/delreceipts_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Paramanently Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        default:

        break;
     }

});






   // MANAGE DELETE RESORE AND UPDATE FUC
$('.savedelpde').on('click', function(){


    var decider = this.id;
    var idq =  decider.split('_');

     switch(idq[0])
     {
        case 'deleteitem':
         var url = $(this).data('url')+"/id/"+idq[1];

        //URL 
        var b = confirm('You Are About to Delete a Record')
        if(b == true){
     
          var formdata = {};
          formdata['id'] = idq[1];

          console.log(url);
          console.log(formdata);

          $.ajax({
                  type: "POST",
                  url:  url,
                  data:formdata,
                  success: function(data, textStatus, jqXHR){


                  console.log(data);

                     if(data == 1)
                     {
                      $("#"+decider).closest('tr').fadeOut('slow');
                     }
                 // alert(data);

                  },
                  error:function(data , textStatus, jqXHR)
                  {
                       alert(data);
                  return 0;



                  }
              });

        }

        break;

        case 'savedelpde':
        url = baseurl()+'pdes/delpdes_ajax/archive/'+idq[1];
        var b = confirm('You Are About to Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'restore':
        url = baseurl()+'pdes/delpdes_ajax/restore/'+idq[1];
        var b = confirm('You Are About to Restore a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        case 'del':
        url = baseurl()+'pdes/delpdes_ajax/delete/'+idq[1];
        var b = confirm('You Are About to Paramanently Delete a Record')
        if(b == true){
         var rslt = ajx_delete(url,decider);
        }
        break;
        default:

        break;
     }

});



});





// AJAX DELETE
ajx_delete = function(url,ids){
     $.ajax({
        type: "GET",
        url:  url,
        success: function(data, textStatus, jqXHR){


           if(data == 1)
           {
            $("#"+ids).closest('tr').fadeOut('slow');
           }
       // alert(data);

        },
        error:function(data , textStatus, jqXHR)
        {
             alert(data);
        return 0;



        }
    });
}




var viewdetails = function(st,url,title){
        ajx_fetch(url,'modal-body',title,'');
        }

ajx_fetch = function(url,displayLayer,title,data)
{

$("#myshopmodel").modal('show');

   $('.modal-title').html(title);
    $("#"+displayLayer).html('Proccessing .. ');
    $.ajax({
        type: "GET",
        url:  url,
        data: data,
        success: function(data, textStatus, jqXHR){

           $("#"+displayLayer).html(data);

        },
        error:function(data , textStatus, jqXHR)
        {
        alert(data);
           // alert(data);


        }
    });
}


//BASE URL
function baseurl(){
 pageURL=document.location.href;
    var urlArray=pageURL.split("/");
    var BaseURL=urlArray[0]+"//"+urlArray[2]+"/";
    if(urlArray[2]=='localhost')
    BaseURL=BaseURL+'myshop/';
  else
     BaseURL=BaseURL;

 
    return BaseURL;
}

  //end

  //defined  variables
  var usergt = {};
  
  
var estimated_amount = 0;
var formdata = {};

$(function(){
   $('.actived').on('click', function(){


    var pid = $(this).closest('tr').attr('id');
    var decider = pid.split('_');
    var pdename = $("#pdaname_"+decider[1]).html();

    switch(decider[0]){
        case 'active':
        url = baseurl()+'pdes/fetchdetails/'+decider[1];
        viewdetails(pid,url,pdename);
        break;
        case 'archive':
        url = baseurl()+'pdes/fetchdetails/'+decider[1];
        viewdetails(pid,url,pdename);
        break;
        default:
        break;

    }


        });

   $('.userselect').change(function(){

var usergroup = this.id;
var user = this.value;
usergt[usergroup] = user;
console.log(usergt);

   });




$(".userselect_multiple option:selected").each(function () {
   // $(this).click(function () {
         var usergroup = this.id;
         var user  = this.value;
         usergt[usergroup] = user;
         console.log(usergt);

          // alert(userid);

  //  });
});


$(".userselect_multiple option").each(function () {
    $(this).click(function () {
         var usergroup = this.id;
         var user  = this.value;
        if(this.selected){


         usergt[usergroup] = user;
         console.log(usergt);

      }else
      {
        delete usergt[usergroup];
         console.log(usergt);
      }

   });
});




});


// AJAX DELETE
ajx_delete = function(url,ids){
    console.log(url);
     $.ajax({
        type: "GET",
        url:  url,
        success: function(data, textStatus, jqXHR){

console.log(data);
           if(data == 1)
           {
            $("#"+ids).closest('tr').fadeOut('slow');
           }
       // alert(data);

        },
        error:function(data , textStatus, jqXHR)
        {
             console.log(data);
        return 0;
        }
    });
}


var viewdetails = function(st,url,title){
        ajx_fetch(url,'modal-body',title,'');
        }

ajx_fetch = function(url,displayLayer,title,data)
{

$("#myshopmodel").modal('show');

   $('.modal-title').html(title);
    $("#"+displayLayer).html('Proccessing .. ');
    $.ajax({
        type: "GET",
        url:  url,
        data: data,
        success: function(data, textStatus, jqXHR){

           $("#"+displayLayer).html(data);

        },
        error:function(data , textStatus, jqXHR)
        {
        console.log(data);
           // alert(data);


        }
    });
}


 

function addCommas (nStr){
    nStr = nStr.toString().replace(/,/g, '');
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function triggerKnobs(){
  $('.knob').each(function () {
           var $this = $(this);
           var myVal = $(this).val();
           // alert(myVal);
           $this.knob({readOnly: true});

           $({value: 0}).animate({value: myVal},
          {
               duration: 2000,
               easing: 'swing',
               step: function () {
                   $this.val(Math.ceil(this.value)).trigger('change');
               }
           })
  });
}

var App = function () {
  $.fn.addCommas = function() {
    $(this).each(function(){
    $(this).val(addCommas($(this).val()));
    });
  };
vlaue = '';
  $('.date-picker')
    .datepicker({dateFormat: "dd/mm/yyyy"})
    .on('changeDate', function(e){
      

       
        var id_label = this.id;

         console.log("LABEL "+id_label);

          $(this).datepicker('hide');
          

        //invitation_to_bid_date
        if(id_label == "invitation_to_bid_date")
        {
            
             vlaue = $("#invitation_to_bid_date").val();
            console.log("SELECTED VALUE :  "+vlaue);
            $(".invitation_to_bid_date").val(vlaue).trigger("change");
        }

        if(id_label == "start_date")
        {            
             vlaue = $(this).val();
             console.log("MOVERS VALUE :  "+vlaue)
             $(".start_date").val(vlaue).trigger("change");
             return ;
        }




        
       
  });

  $('.timepicker-default').timepicker({
    defaultTime: 'value',
    minuteStep: 1,
    disableFocus: true,
    template: 'dropdown'
  });

  triggerKnobs();

  //dashboard-stats-toggle
  $('#dashboard-stats-financial-year').on('change', function(){
      var form_data = {financial_year: $(this).val()};
      if($(this).val() != ''){
        $(".results-stats-overview").html('<img src="../images/loading.gif" />');
        $.ajax({
          url: baseurl()+'user/load_dashboard_stats',
          type: 'POST',
          data: form_data,
          success: function(msg)
          {
             // alert(msg);
             $(".results-stats-overview").html(msg);

             triggerKnobs();
          }
        });
      }
    });


/*

  $('#same_as_inspection, #same_as_issue, #same_as_deliver').on('click', function(){

    if($(this).is(':checked'))
    {
      switch($(this).attr('id'))
      {
        case 'same_as_inspection':
          $('#documents_address_issue').val($('#documents_inspection_address').val());
          break;

        case 'same_as_issue':
          $('#bid_receipt_address').val($('#documents_address_issue').val());
          break;

        case 'same_as_deliver':
          $('#bid_openning_address').val($('#bid_receipt_address').val());
          break;
      }
    }
  });
  * */
  
  $('#same_as_inspection, #same_as_issue, #same_as_deliver,#same_as_delivered_to,#same_as_issed_from').on('click', function(){

    if($(this).is(':checked'))
    {
      switch($(this).attr('id'))
      {
        case 'same_as_inspection':
          $('#documents_address_issue').val($('#documents_inspection_address').val());
          break;

        case 'same_as_issue':
          $('#bid_receipt_address').val($('#documents_address_issue').val());
          break;

        case 'same_as_deliver':
          $('#bid_openning_address').val($('#bid_receipt_address').val());
          break;
          
             case 'same_as_issed_from':
          $('#documents_address_delivered_to').val($('#documents_address_issue').val().trim());
          break;
          
          
            case 'same_as_delivered_to':
          $('#bid_opening_address').val($('#documents_address_delivered_to').val().trim());
          break;    
          
         
          
      }
    }
  });



    var isMainPage = false;
    var isMapPage = false;
    var isIE8 = false;


    var handleDashboardCalendar = function () {

        if (!jQuery().fullCalendar) {
            return;
        }

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var h = {};

        if ($(window).width() <= 320) {
            h = {
                left: 'title, prev,next',
                center: '',
                right: 'today,month,agendaWeek,agendaDay'
            };
        } else {
            h = {
                left: 'title',
                center: '',
                right: 'prev,next,today,month,agendaWeek,agendaDay'
            };
        }

        $('#calendar').html("");
        $('#calendar').fullCalendar({
            header: h,
            editable: true,
            events: [{
                title: 'All Day Event',
                start: new Date(y, m, 1),
                className: 'label label-default',
            }, {
                title: 'Long Event',
                start: new Date(y, m, d - 5),
                end: new Date(y, m, d - 2),
                className: 'label label-success',
            }, {
                title: 'Repeating Event',
                start: new Date(y, m, d - 3, 16, 0),
                allDay: false,
                className: 'label label-default',
            }, {
                title: 'Repeating Event',
                start: new Date(y, m, d + 4, 16, 0),
                allDay: false,
                className: 'label label-important',
            }, {
                title: 'Meeting',
                start: new Date(y, m, d, 10, 30),
                allDay: false,
                className: 'label label-info',
            }, {
                title: 'Lunch',
                start: new Date(y, m, d, 12, 0),
                end: new Date(y, m, d, 14, 0),
                allDay: false,
                className: 'label label-warning',
            }, {
                title: 'Birthday Party',
                start: new Date(y, m, d + 1, 19, 0),
                end: new Date(y, m, d + 1, 22, 30),
                allDay: false,
                className: 'label label-success',
            }, {
                title: 'Click for Google',
                start: new Date(y, m, 28),
                end: new Date(y, m, 29),
                url: 'http://google.com/',
                className: 'label label-warning',
            }]
        });

    }

    var handleCalendar = function () {

        if (!jQuery().fullCalendar) {
            return;
        }

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var h = {};

        if ($(window).width() <= 320) {
            h = {
                left: 'title, prev,next',
                center: '',
                right: 'today,month,agendaWeek,agendaDay'
            };
        } else {
            h = {
                left: 'title',
                center: '',
                right: 'prev,next,today,month,agendaWeek,agendaDay'
            };
        }

        var initDrag = function (el) {
            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end
            var eventObject = {
                title: $.trim(el.text()) // use the element's text as the event title
            };
            // store the Event Object in the DOM element so we can get to it later
            el.data('eventObject', eventObject);
            // make the event draggable using jQuery UI
            el.draggable({
                zIndex: 999,
                revert: true, // will cause the event to go back to its
                revertDuration: 0 //  original position after the drag
            });
        }

        var addEvent = function (title, priority) {
            title = title.length == 0 ? "Untitled Event" : title;
            priority = priority.length == 0 ? "default" : priority;

            var html = $('<div data-class="label label-' + priority + '" class="external-event label label-' + priority + '">' + title + '</div>');
            jQuery('#event_box').append(html);
            initDrag(html);
        }

        $('#external-events div.external-event').each(function () {
            initDrag($(this))
        });

        $('#event_add').click(function () {
            var title = $('#event_title').val();
            var priority = $('#event_priority').val();
            addEvent(title, priority);
        });

        //modify chosen options
        var handleDropdown = function () {
            $('#event_priority_chzn .chzn-search').hide(); //hide search box
            $('#event_priority_chzn_o_1').html('<span class="label label-default">' + $('#event_priority_chzn_o_1').text() + '</span>');
            $('#event_priority_chzn_o_2').html('<span class="label label-success">' + $('#event_priority_chzn_o_2').text() + '</span>');
            $('#event_priority_chzn_o_3').html('<span class="label label-info">' + $('#event_priority_chzn_o_3').text() + '</span>');
            $('#event_priority_chzn_o_4').html('<span class="label label-warning">' + $('#event_priority_chzn_o_4').text() + '</span>');
            $('#event_priority_chzn_o_5').html('<span class="label label-important">' + $('#event_priority_chzn_o_5').text() + '</span>');
        }

        $('#event_priority_chzn').click(handleDropdown);

        //predefined events
        addEvent("My Event 1", "default");
        addEvent("My Event 2", "success");
        addEvent("My Event 3", "info");
        addEvent("My Event 4", "warning");
        addEvent("My Event 5", "important");
        addEvent("My Event 6", "success");
        addEvent("My Event 7", "info");
        addEvent("My Event 8", "warning");
        addEvent("My Event 9", "success");
        addEvent("My Event 10", "default");

        $('#calendar').fullCalendar({
            header: h,
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            drop: function (date, allDay) { // this function is called when something is dropped

                // retrieve the dropped element's stored Event Object
                var originalEventObject = $(this).data('eventObject');
                // we need to copy it, so that multiple events don't have a reference to the same object
                var copiedEventObject = $.extend({}, originalEventObject);

                // assign it the date that was reported
                copiedEventObject.start = date;
                copiedEventObject.allDay = allDay;
                copiedEventObject.className = $(this).attr("data-class");

                // render the event on the calendar
                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

                // is the "remove after drop" checkbox checked?
                if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove();
                }
            },
            events: [{
                title: 'All Day Event',
                start: new Date(y, m, 1),
                className: 'label label-default',
            }, {
                title: 'Long Event',
                start: new Date(y, m, d - 5),
                end: new Date(y, m, d - 2),
                className: 'label label-success',
            }, {
                id: 999,
                title: 'Repeating Event',
                start: new Date(y, m, d - 3, 16, 0),
                allDay: false,
                className: 'label label-default',
            }, {
                id: 999,
                title: 'Repeating Event',
                start: new Date(y, m, d + 4, 16, 0),
                allDay: false,
                className: 'label label-important',
            }, {
                title: 'Meeting',
                start: new Date(y, m, d, 10, 30),
                allDay: false,
                className: 'label label-info',
            }, {
                title: 'Lunch',
                start: new Date(y, m, d, 12, 0),
                end: new Date(y, m, d, 14, 0),
                allDay: false,
                className: 'label label-warning',
            }, {
                title: 'Birthday Party',
                start: new Date(y, m, d + 1, 19, 0),
                end: new Date(y, m, d + 1, 22, 30),
                allDay: false,
                className: 'label label-success',
            }, {
                title: 'Click for Google',
                start: new Date(y, m, 28),
                end: new Date(y, m, 29),
                url: 'http://google.com/',
                className: 'label label-warning',
            }]
        });

    }

    var handleChat = function () {
        var cont = $('#chats');
        var list = $('.chats', cont);
        var form = $('.chat-form', cont);
        var input = $('input', form);
        var btn = $('.btn', form);

        var handleClick = function () {
            var text = input.val();
            if (text.length == 0) {
                return;
            }

            var time = new Date();
            var time_str = time.toString('dd/MMM,yyyy HH:MM');
            var tpl = '';
            tpl += '<li class="out">';
            tpl += '<img class="avatar" alt="" src="img/avatar1.jpg"/>';
            tpl += '<div class="message">';
            tpl += '<span class="arrow"></span>';
            tpl += '<a href="#" class="name">Sumon Ahmed</a>&nbsp;';
            tpl += '<span class="datetime">at ' + time_str + '</span>';
            tpl += '<span class="body">';
            tpl += text;
            tpl += '</span>';
            tpl += '</div>';
            tpl += '</li>';

            var msg = list.append(tpl);
            input.val("");
            $('.scroller', cont).slimScroll({
                scrollTo: list.height()
            });
        }

        btn.click(handleClick);
        input.keypress(function (e) {
            if (e.which == 13) {
                handleClick();
                return false; //<---- Add this line
            }
        });
    }

    var handleClockfaceTimePickers = function () {

        if (!jQuery().clockface) {
            return;
        }

        $('#clockface_1').clockface();

        $('#clockface_2').clockface({
            format: 'HH:mm',
            trigger: 'manual'
        });

        $('#clockface_2_toggle-btn').click(function (e) {
            e.stopPropagation();
            $('#clockface_2').clockface('toggle');
        });

        $('#clockface_3').clockface({
            format: 'H:mm'
        }).clockface('show', '14:30');
    }

    var handlePortletSortable = function () {
        if (!jQuery().sortable) {
            return;
        }
        $(".sortable").sortable({
            connectWith: '.sortable',
            iframeFix: false,
            items: 'div.widget',
            opacity: 0.8,
            helper: 'original',
            revert: true,
            forceHelperSize: true,
            placeholder: 'sortable-box-placeholder round-all',
            forcePlaceholderSize: true,
            tolerance: 'pointer'
        });

    }

    var handleMainMenu = function () {
        jQuery('#sidebar .has-sub > a').click(function () {
            var last = jQuery('.has-sub.open', $('#sidebar'));
            last.removeClass("open");
            jQuery('.arrow', last).removeClass("open");
            jQuery('.sub', last).slideUp(200);
            var sub = jQuery(this).next();
            if (sub.is(":visible")) {
                jQuery('.arrow', jQuery(this)).removeClass("open");
                jQuery(this).parent().removeClass("open");
                sub.slideUp(200);
            } else {
                jQuery('.arrow', jQuery(this)).addClass("open");
                jQuery(this).parent().addClass("open");
                sub.slideDown(200);
            }
        });
    }

    var handleWidgetTools = function () {
        jQuery('.widget .tools .fa fa-remove').click(function () {
            jQuery(this).parents(".widget").parent().remove();
        });

        jQuery('.widget .tools .fa fa-refresh').click(function () {
            var el = jQuery(this).parents(".widget");
            App.blockUI(el);
            window.setTimeout(function () {
                App.unblockUI(el);
            }, 1000);
        });

        jQuery('.widget .tools .fa fa-chevron-down, .widget .tools .fa fa-chevron-up').click(function () {
            var el = jQuery(this).parents(".widget").children(".widget-body");
            if (jQuery(this).hasClass("fa fa-chevron-down")) {
                jQuery(this).removeClass("fa fa-chevron-down").addClass("fa fa-chevron-up");
                el.slideUp(200);
            } else {
                jQuery(this).removeClass("fa fa-chevron-up").addClass("fa fa-chevron-down");
                el.slideDown(200);
            }
        });
    }

    var handleDashboardCharts = function () {

        // used by plot functions
        var data = [];
        var totalPoints = 200;

        // random data generator for plot charts
        function getRandomData() {
            if (data.length > 0) data = data.slice(1);
            // do a random walk
            while (data.length < totalPoints) {
                var prev = data.length > 0 ? data[data.length - 1] : 50;
                var y = prev + Math.random() * 10 - 5;
                if (y < 0) y = 0;
                if (y > 100) y = 100;
                data.push(y);
            }
            // zip the generated y values with the x values
            var res = [];
            for (var i = 0; i < data.length; ++i) res.push([i, data[i]])
            return res;
        }

        if (!jQuery.plot) {
            return;
        }

        function randValue() {
            return (Math.floor(Math.random() * (1 + 40 - 20))) + 20;
        }

        var pageviews = [
            [1, randValue()],
            [2, randValue()],
            [3, 2 + randValue()],
            [4, 3 + randValue()],
            [5, 5 + randValue()],
            [6, 10 + randValue()],
            [7, 15 + randValue()],
            [8, 20 + randValue()],
            [9, 25 + randValue()],
            [10, 30 + randValue()],
            [11, 35 + randValue()],
            [12, 25 + randValue()],
            [13, 15 + randValue()],
            [14, 20 + randValue()],
            [15, 45 + randValue()],
            [16, 50 + randValue()],
            [17, 65 + randValue()],
            [18, 70 + randValue()],
            [19, 85 + randValue()],
            [20, 80 + randValue()],
            [21, 75 + randValue()],
            [22, 80 + randValue()],
            [23, 75 + randValue()],
            [24, 70 + randValue()],
            [25, 65 + randValue()],
            [26, 75 + randValue()],
            [27, 80 + randValue()],
            [28, 85 + randValue()],
            [29, 90 + randValue()],
            [30, 95 + randValue()]
        ];
        var visitors = [
            [1, randValue() - 5],
            [2, randValue() - 5],
            [3, randValue() - 5],
            [4, 6 + randValue()],
            [5, 5 + randValue()],
            [6, 20 + randValue()],
            [7, 25 + randValue()],
            [8, 36 + randValue()],
            [9, 26 + randValue()],
            [10, 38 + randValue()],
            [11, 39 + randValue()],
            [12, 50 + randValue()],
            [13, 51 + randValue()],
            [14, 12 + randValue()],
            [15, 13 + randValue()],
            [16, 14 + randValue()],
            [17, 15 + randValue()],
            [18, 15 + randValue()],
            [19, 16 + randValue()],
            [20, 17 + randValue()],
            [21, 18 + randValue()],
            [22, 19 + randValue()],
            [23, 20 + randValue()],
            [24, 21 + randValue()],
            [25, 14 + randValue()],
            [26, 24 + randValue()],
            [27, 25 + randValue()],
            [28, 26 + randValue()],
            [29, 27 + randValue()],
            [30, 31 + randValue()]
        ];

        $('#site_statistics_loading').hide();
        $('#site_statistics_content').show();

        var plot = $.plot($("#site_statistics"), [{
            data: pageviews,
            label: "Unique Visits"
        }, {
            data: visitors,
            label: "Page Views"
        }], {
            series: {
                lines: {
                    show: true,
                    lineWidth: 2,
                    fill: true,
                    fillColor: {
                        colors: [{
                            opacity: 0.05
                        }, {
                            opacity: 0.01
                        }]
                    }
                },
                points: {
                    show: true
                },
                shadowSize: 2
            },
            grid: {
                hoverable: true,
                clickable: true,
                tickColor: "#eee",
                borderWidth: 0
            },
            colors: ["#A5D16C", "#FCB322", "#32C2CD"],
            xaxis: {
                ticks: 11,
                tickDecimals: 0
            },
            yaxis: {
                ticks: 11,
                tickDecimals: 0
            }
        });


        function showTooltip(x, y, contents) {
            $('<div id="tooltip">' + contents + '</div>').css({
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 15,
                border: '1px solid #333',
                padding: '4px',
                color: '#fff',
                'border-radius': '3px',
                'background-color': '#333',
                opacity: 0.80
            }).appendTo("body").fadeIn(200);
        }

        var previousPoint = null;
        $("#site_statistics").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = " + y);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });

        //server load
        var options = {
            series: {
                shadowSize: 1
            },
            lines: {
                show: true,
                lineWidth: 0.5,
                fill: true,
                fillColor: {
                    colors: [{
                        opacity: 0.1
                    }, {
                        opacity: 1
                    }]
                }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickFormatter: function (v) {
                    return v + "%";
                }
            },
            xaxis: {
                show: false
            },
            colors: ["#A5D16C"],
            grid: {
                tickColor: "#eaeaea",
                borderWidth: 0
            }
        };

        $('#load_statistics_loading').hide();
        $('#load_statistics_content').show();

        var updateInterval = 30;
        var plot = $.plot($("#load_statistics"), [getRandomData()], options);

        function update() {
            plot.setData([getRandomData()]);
            plot.draw();
            setTimeout(update, updateInterval);
        }
        update();
    }

    var handleCharts = function () {

        // used by plot functions
        var data = [];
        var totalPoints = 250;

        // random data generator for plot charts
        function getRandomData() {
            if (data.length > 0) data = data.slice(1);
            // do a random walk
            while (data.length < totalPoints) {
                var prev = data.length > 0 ? data[data.length - 1] : 50;
                var y = prev + Math.random() * 10 - 5;
                if (y < 0) y = 0;
                if (y > 100) y = 100;
                data.push(y);
            }
            // zip the generated y values with the x values
            var res = [];
            for (var i = 0; i < data.length; ++i) res.push([i, data[i]])
            return res;
        }


        if (!jQuery.plot) {
            return;
        }

        if ($("#chart_1").size() == 0) {
            return;
        }

        //Basic Chart
        function chart1() {
            var d1 = [];
            for (var i = 0; i < Math.PI * 2; i += 0.25)
            d1.push([i, Math.sin(i)]);

            var d2 = [];
            for (var i = 0; i < Math.PI * 2; i += 0.25)
            d2.push([i, Math.cos(i)]);

            var d3 = [];
            for (var i = 0; i < Math.PI * 2; i += 0.1)
            d3.push([i, Math.tan(i)]);

            $.plot($("#chart_1"), [{
                label: "sin(x)",
                data: d1
            }, {
                label: "cos(x)",
                data: d2
            }, {
                label: "tan(x)",
                data: d3
            }], {
                series: {
                    lines: {
                        show: true
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                    ticks: [0, [Math.PI / 2, "\u03c0/2"],
                        [Math.PI, "\u03c0"],
                        [Math.PI * 3 / 2, "3\u03c0/2"],
                        [Math.PI * 2, "2\u03c0"]
                    ]
                },
                yaxis: {
                    ticks: 10,
                    min: -2,
                    max: 2
                },
                grid: {
                    backgroundColor: {
                        colors: ["#fff", "#eee"]
                    }
                }
            });

        }

        //Interactive Chart
        function chart2() {
            function randValue() {
                return (Math.floor(Math.random() * (1 + 40 - 20))) + 20;
            }
            var pageviews = [
                [1, randValue()],
                [2, randValue()],
                [3, 2 + randValue()],
                [4, 3 + randValue()],
                [5, 5 + randValue()],
                [6, 10 + randValue()],
                [7, 15 + randValue()],
                [8, 20 + randValue()],
                [9, 25 + randValue()],
                [10, 30 + randValue()],
                [11, 35 + randValue()],
                [12, 25 + randValue()],
                [13, 15 + randValue()],
                [14, 20 + randValue()],
                [15, 45 + randValue()],
                [16, 50 + randValue()],
                [17, 65 + randValue()],
                [18, 70 + randValue()],
                [19, 85 + randValue()],
                [20, 80 + randValue()],
                [21, 75 + randValue()],
                [22, 80 + randValue()],
                [23, 75 + randValue()],
                [24, 70 + randValue()],
                [25, 65 + randValue()],
                [26, 75 + randValue()],
                [27, 80 + randValue()],
                [28, 85 + randValue()],
                [29, 90 + randValue()],
                [30, 95 + randValue()]
            ];
            var visitors = [
                [1, randValue() - 5],
                [2, randValue() - 5],
                [3, randValue() - 5],
                [4, 6 + randValue()],
                [5, 5 + randValue()],
                [6, 20 + randValue()],
                [7, 25 + randValue()],
                [8, 36 + randValue()],
                [9, 26 + randValue()],
                [10, 38 + randValue()],
                [11, 39 + randValue()],
                [12, 50 + randValue()],
                [13, 51 + randValue()],
                [14, 12 + randValue()],
                [15, 13 + randValue()],
                [16, 14 + randValue()],
                [17, 15 + randValue()],
                [18, 15 + randValue()],
                [19, 16 + randValue()],
                [20, 17 + randValue()],
                [21, 18 + randValue()],
                [22, 19 + randValue()],
                [23, 20 + randValue()],
                [24, 21 + randValue()],
                [25, 14 + randValue()],
                [26, 24 + randValue()],
                [27, 25 + randValue()],
                [28, 26 + randValue()],
                [29, 27 + randValue()],
                [30, 31 + randValue()]
            ];

            var plot = $.plot($("#chart_2"), [{
                data: pageviews,
                label: "Unique Visits"
            }, {
                data: visitors,
                label: "Page Views"
            }], {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 2,
                        fill: true,
                        fillColor: {
                            colors: [{
                                opacity: 0.05
                            }, {
                                opacity: 0.01
                            }]
                        }
                    },
                    points: {
                        show: true
                    },
                    shadowSize: 2
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: "#eee",
                    borderWidth: 0
                },
                colors: ["#FCB322", "#A5D16C", "#52e136"],
                xaxis: {
                    ticks: 11,
                    tickDecimals: 0
                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0
                }
            });


            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 15,
                    border: '1px solid #333',
                    padding: '4px',
                    color: '#fff',
                    'border-radius': '3px',
                    'background-color': '#333',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            var previousPoint = null;
            $("#chart_2").bind("plothover", function (event, pos, item) {
                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                            y = item.datapoint[1].toFixed(2);

                        showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = " + y);
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        }

        //Tracking Curves
        function chart3() {
            //tracking curves:

            var sin = [],
                cos = [];
            for (var i = 0; i < 14; i += 0.1) {
                sin.push([i, Math.sin(i)]);
                cos.push([i, Math.cos(i)]);
            }

            plot = $.plot($("#chart_3"), [{
                data: sin,
                label: "sin(x) = -0.00"
            }, {
                data: cos,
                label: "cos(x) = -0.00"
            }], {
                series: {
                    lines: {
                        show: true
                    }
                },
                crosshair: {
                    mode: "x"
                },
                grid: {
                    hoverable: true,
                    autoHighlight: false
                },
                colors: ["#FCB322", "#A5D16C", "#52e136"],
                yaxis: {
                    min: -1.2,
                    max: 1.2
                }
            });

            var legends = $("#chart_3 .legendLabel");
            legends.each(function () {
                // fix the widths so they don't jump around
                $(this).css('width', $(this).width());
            });

            var updateLegendTimeout = null;
            var latestPosition = null;

            function updateLegend() {
                updateLegendTimeout = null;

                var pos = latestPosition;

                var axes = plot.getAxes();
                if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max || pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) return;

                var i, j, dataset = plot.getData();
                for (i = 0; i < dataset.length; ++i) {
                    var series = dataset[i];

                    // find the nearest points, x-wise
                    for (j = 0; j < series.data.length; ++j)
                    if (series.data[j][0] > pos.x) break;

                    // now interpolate
                    var y, p1 = series.data[j - 1],
                        p2 = series.data[j];
                    if (p1 == null) y = p2[1];
                    else if (p2 == null) y = p1[1];
                    else y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);

                    legends.eq(i).text(series.label.replace(/=.*/, "= " + y.toFixed(2)));
                }
            }

            $("#chart_3").bind("plothover", function (event, pos, item) {
                latestPosition = pos;
                if (!updateLegendTimeout) updateLegendTimeout = setTimeout(updateLegend, 50);
            });
        }

        //Dynamic Chart
        function chart4() {
            //server load
            var options = {
                series: {
                    shadowSize: 1
                },
                lines: {
                    show: true,
                    lineWidth: 0.5,
                    fill: true,
                    fillColor: {
                        colors: [{
                            opacity: 0.1
                        }, {
                            opacity: 1
                        }]
                    }
                },
                yaxis: {
                    min: 0,
                    max: 100,
                    tickFormatter: function (v) {
                        return v + "%";
                    }
                },
                xaxis: {
                    show: false
                },
                colors: ["#6ef146"],
                grid: {
                    tickColor: "#a8a3a3",
                    borderWidth: 0
                }
            };

            var updateInterval = 30;
            var plot = $.plot($("#chart_4"), [getRandomData()], options);

            function update() {
                plot.setData([getRandomData()]);
                plot.draw();
                setTimeout(update, updateInterval);
            }
            update();
        }

        //bars with controls
        function chart5() {
            var d1 = [];
            for (var i = 0; i <= 10; i += 1)
            d1.push([i, parseInt(Math.random() * 30)]);

            var d2 = [];
            for (var i = 0; i <= 10; i += 1)
            d2.push([i, parseInt(Math.random() * 30)]);

            var d3 = [];
            for (var i = 0; i <= 10; i += 1)
            d3.push([i, parseInt(Math.random() * 30)]);

            var stack = 0,
                bars = true,
                lines = false,
                steps = false;

            function plotWithOptions() {
                $.plot($("#chart_5"), [d1, d2, d3], {
                    series: {
                        stack: stack,
                        lines: {
                            show: lines,
                            fill: true,
                            steps: steps
                        },
                        bars: {
                            show: bars,
                            barWidth: 0.6
                        }
                    }
                });
            }

            $(".stackControls input").click(function (e) {
                e.preventDefault();
                stack = $(this).val() == "With stacking" ? true : null;
                plotWithOptions();
            });
            $(".graphControls input").click(function (e) {
                e.preventDefault();
                bars = $(this).val().indexOf("Bars") != -1;
                lines = $(this).val().indexOf("Lines") != -1;
                steps = $(this).val().indexOf("steps") != -1;
                plotWithOptions();
            });

            plotWithOptions();
        }

        //graph
        function graphs() {

            var graphData = [];
            var series = Math.floor(Math.random() * 10) + 1;
            for (var i = 0; i < series; i++) {
                graphData[i] = {
                    label: "Series" + (i + 1),
                    data: Math.floor((Math.random() - 1) * 100) + 1
                }
            }

            $.plot($("#graph_1"), graphData, {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 1,
                            formatter: function (label, series) {
                                return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">' + label + '<br/>' + Math.round(series.percent) + '%</div>';
                            },
                            background: {
                                opacity: 0.8
                            }
                        }
                    }
                },
                legend: {
                    show: false
                }
            });


            $.plot($("#graph_2"), graphData, {
                series: {
                    pie: {
                        show: true,
                        radius: 1,
                        label: {
                            show: true,
                            radius: 3 / 4,
                            formatter: function (label, series) {
                                return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">' + label + '<br/>' + Math.round(series.percent) + '%</div>';
                            },
                            background: {
                                opacity: 0.5
                            }
                        }
                    }
                },
                legend: {
                    show: false
                }
            });

            $.plot($("#graph_3"), graphData, {
                series: {
                    pie: {
                        show: true
                    }
                },
                grid: {
                    hoverable: true,
                    clickable: true
                }
            });
            $("#graph_3").bind("plothover", pieHover);
            $("#graph_3").bind("plotclick", pieClick);

            function pieHover(event, pos, obj) {
                if (!obj) return;
                percent = parseFloat(obj.series.percent).toFixed(2);
                $("#hover").html('<span style="font-weight: bold; color: ' + obj.series.color + '">' + obj.series.label + ' (' + percent + '%)</span>');
            }

            function pieClick(event, pos, obj) {
                if (!obj) return;
                percent = parseFloat(obj.series.percent).toFixed(2);
                alert('' + obj.series.label + ': ' + percent + '%');
            }

            $.plot($("#graph_4"), graphData, {
                series: {
                    pie: {
                        innerRadius: 0.5,
                        show: true
                    }
                }
            });
        }

        chart1();
        chart2();
        chart3();
        chart4();
        chart5();
        graphs();
    }

    var handleFancyBox = function () {
        if (!jQuery().fancybox) {
            return;
        }

        if (jQuery(".fancybox-button").size() > 0) {
            jQuery(".fancybox-button").fancybox({
                groupAttr: 'data-rel',
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: true,
                helpers: {
                    title: {
                        type: 'inside'
                    }
                }
            });
        }
    }

    var handleLoginForm = function () {
        jQuery('#forget-password').click(function () {
            jQuery('#loginform').hide();
            jQuery('#forgotform').show(200);
        });

        jQuery('#forget-btn').click(function () {

            jQuery('#loginform').slideDown(200);
            jQuery('#forgotform').slideUp(200);
        });
    }

    var handleFixInputPlaceholderForIE = function () {
        //fix html5 placeholder attribute for ie7 & ie8
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) <= 9) { // ie7&ie8
            jQuery('input[placeholder], textarea[placeholder]').each(function () {

                var input = jQuery(this);

                jQuery(input).val(input.attr('placeholder'));

                jQuery(input).focus(function () {
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                });

                jQuery(input).blur(function () {
                    if (input.val() == '' || input.val() == input.attr('placeholder')) {
                        input.val(input.attr('placeholder'));
                    }
                });
            });
        }
    }

    var handleStyler = function () {
        var scrollHeight = '25px';

        jQuery('#theme-change').click(function () {
            if ($(this).attr("opened") && !$(this).attr("opening") && !$(this).attr("closing")) {
                $(this).removeAttr("opened");
                $(this).attr("closing", "1");

                $("#theme-change").css("overflow", "hidden").animate({
                    width: '20px',
                    height: '22px',
                    'padding-top': '3px'
                }, {
                    complete: function () {
                        $(this).removeAttr("closing");
                        $("#theme-change .settings").hide();
                    }
                });
            } else if (!$(this).attr("closing") && !$(this).attr("opening")) {
                $(this).attr("opening", "1");
                $("#theme-change").css("overflow", "visible").animate({
                    width: '190px',
                    height: scrollHeight,
                    'padding-top': '3px'
                }, {
                    complete: function () {
                        $(this).removeAttr("opening");
                        $(this).attr("opened", 1);
                    }
                });
                $("#theme-change .settings").show();
            }
        });

        jQuery('#theme-change .colors span').click(function () {
            var color = $(this).attr("data-style");
            setColor(color);
        });

        jQuery('#theme-change .layout input').change(function () {
            setLayout();
        });

        var setColor = function (color) {
            $('#style_color').attr("href", "css/style_" + color + ".css");
        }

    }

    var handlePulsate = function () {
        if (!jQuery().pulsate) {
            return;
        }

        if (isIE8 == true) {
            return; // pulsate plugin does not support IE8 and below
        }

        if (jQuery().pulsate) {
            jQuery('#pulsate-regular').pulsate({
                color: "#bf1c56"
            });

            jQuery('#pulsate-once').click(function () {
                $(this).pulsate({
                    color: "#399bc3",
                    repeat: false
                });
            });

            jQuery('#pulsate-hover').pulsate({
                color: "#5ebf5e",
                repeat: false,
                onHover: true
            });

            jQuery('#pulsate-crazy').click(function () {
                $(this).pulsate({
                    color: "#fdbe41",
                    reach: 50,
                    repeat: 10,
                    speed: 100,
                    glow: true
                });
            });
        }
    }

    var handlePeity = function () {
        if (!jQuery().peity) {
            return;
        }

        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 2) <= 8) { // ie7&ie8
            return;
        }

        $(".stat.bad .line-chart").peity("line", {
            height: 20,
            width: 50,
            colour: "#d12610",
            strokeColour: "#666"
        }).show();

        $(".stat.bad .bar-chart").peity("bar", {
            height: 20,
            width: 50,
            colour: "#d12610",
            strokeColour: "#666"
        }).show();

        $(".stat.ok .line-chart").peity("line", {
            height: 20,
            width: 50,
            colour: "#37b7f3",
            strokeColour: "#757575"
        }).show();

        $(".stat.ok .bar-chart").peity("bar", {
            height: 20,
            width: 50,
            colour: "#37b7f3"
        }).show();

        $(".stat.good .line-chart").peity("line", {
            height: 20,
            width: 50,
            colour: "#52e136"
        }).show();

        $(".stat.good .bar-chart").peity("bar", {
            height: 20,
            width: 50,
            colour: "#52e136"
        }).show();
        //

        $(".stat.bad.huge .line-chart").peity("line", {
            height: 20,
            width: 40,
            colour: "#d12610",
            strokeColour: "#666"
        }).show();

        $(".stat.bad.huge .bar-chart").peity("bar", {
            height: 20,
            width: 40,
            colour: "#d12610",
            strokeColour: "#666"
        }).show();

        $(".stat.ok.huge .line-chart").peity("line", {
            height: 20,
            width: 40,
            colour: "#37b7f3",
            strokeColour: "#757575"
        }).show();

        $(".stat.ok.huge .bar-chart").peity("bar", {
            height: 20,
            width: 40,
            colour: "#37b7f3"
        }).show();

        $(".stat.good.huge .line-chart").peity("line", {
            height: 20,
            width: 40,
            colour: "#52e136"
        }).show();

        $(".stat.good.huge .bar-chart").peity("bar", {
            height: 20,
            width: 40,
            colour: "#52e136"
        }).show();
    }

    var handleDeviceWidth = function () {
        function fixWidth(e) {
            var winHeight = $(window).height();
            var winWidth = $(window).width();
            //alert(winWidth);
            //for tablet and small desktops
            if (winWidth < 1125 && winWidth > 767) {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forDesktop);
                        $(this).addClass(forTablet);
                    }

                });
            } else {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forTablet);
                        $(this).addClass(forDesktop);
                    }
                });
            }
        }

        fixWidth();

        running = false;
        jQuery(window).resize(function () {
            if (running == false) {
                running = true;
                setTimeout(function () {
                    // fix layout width
                    fixWidth();
                    // fix calendar width by just reinitializing
                    handleDashboardCalendar();
                    if (isMainPage) {
                        handleDashboardCalendar(); // handles full calendar for main page
                    } else {
                        handleCalendar(); // handles full calendars
                    }
                    // fix vector maps width
                    if (isMainPage) {
                        jQuery('.vmaps').each(function () {
                            var map = jQuery(this);
                            map.width(map.parent().parent().width());
                        });
                    }
                    if (isMapPage) {
                        jQuery('.vmaps').each(function () {
                            var map = jQuery(this);
                            map.width(map.parent().width());
                        });
                    }
                    // fix event form chosen dropdowns
                    $('#event_priority_chzn').width($('#event_title').width() + 15);
                    $('#event_priority_chzn .chzn-drop').width($('#event_title').width() + 13);

                    $(".chzn-select").val('').trigger("liszt:updated");
                    //finish
                    running = false;
                }, 200); // wait for 200ms on resize event
            }
        });
    }

    var handleGritterNotifications = function () {
        if (!jQuery.gritter) {
            return;
        }
        $('#gritter-sticky').click(function () {
            var unique_id = $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a sticky notice!',
                // (string | mandatory) the text inside the notification
                text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: 'img/avatar-mini.png',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: true,
                // (int | optional) the time you want it to be alive for before fading out
                time: '',
                // (string | optional) the class name you want to apply to that specific message
                class_name: 'my-sticky-class'
            });
            return false;
        });

        $('#gritter-regular').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a regular notice!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: 'img/avatar-mini.png',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: false,
                // (int | optional) the time you want it to be alive for before fading out
                time: ''
            });

            return false;

        });

        $('#gritter-max').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a notice with a max of 3 on screen at one time!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.',
                // (string | optional) the image to display on the left
                image: 'img/avatar-mini.png',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: false,
                // (function) before the gritter notice is opened
                before_open: function () {
                    if ($('.gritter-item-wrapper').length == 3) {
                        // Returning false prevents a new gritter from opening
                        return false;
                    }
                }
            });
            return false;
        });

        $('#gritter-without-image').click(function () {
            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a notice without an image!',
                // (string | mandatory) the text inside the notification
                text: 'This will fade out after a certain amount of time. Vivamus eget tincidunt velit. Cum sociis natoque penatibus et <a href="#" style="color:#ccc">magnis dis parturient</a> montes, nascetur ridiculus mus.'
            });

            return false;
        });

        $('#gritter-light').click(function () {

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'This is a light notification',
                // (string | mandatory) the text inside the notification
                text: 'Just add a "gritter-light" class_name to your $.gritter.add or globally to $.gritter.options.class_name',
                class_name: 'gritter-light'
            });

            return false;
        });

        $("#gritter-remove-all").click(function () {

            $.gritter.removeAll();
            return false;

        });
    }

    var handleTooltip = function () {
        jQuery('.tooltips').tooltip();
    }

    var handlePopover = function () {
        jQuery('.popovers').popover();
    }

    var handleChoosenSelect = function () {
        if (!jQuery().chosen) {
            return;
        }
        $(".chosen").chosen();
        $(".chosen-with-diselect").chosen({
            allow_single_deselect: true
        });
    }

    var handleUniform = function () {
        if (!jQuery().uniform) {
            return;
        }
        if (test = $("input[type=checkbox]:not(.toggle), input[type=radio]:not(.toggle)")) {
            test.uniform();
        }
    }

    var handleWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5();
        }
    }

    var handleToggleButtons = function () {
        if (!jQuery().toggleButtons) {
            return;
        }
        $('.basic-toggle-button').toggleButtons();
        $('.text-toggle-button').toggleButtons({
            width: 200,
            label: {
                enabled: "Lorem Ipsum",
                disabled: "Dolor Sit"
            }
        });
        $('.danger-toggle-button').toggleButtons({
            style: {
                // Accepted values ["primary", "danger", "info", "success", "warning"] or nothing
                enabled: "danger",
                disabled: "info"
            }
        });
        $('.info-toggle-button').toggleButtons({
            style: {
                enabled: "info",
                disabled: ""
            }
        });
        $('.success-toggle-button').toggleButtons({
            style: {
                enabled: "success",
                disabled: "danger"
            }
        });
        $('.warning-toggle-button').toggleButtons({
            style: {
                enabled: "warning",
                disabled: "success"
            }
        });

        $('.height-toggle-button').toggleButtons({
            height: 100,
            font: {
                'line-height': '100px',
                'font-size': '20px',
                'font-style': 'italic'
            }
        });

        $('.not-animated-toggle-button').toggleButtons({
            animated: false
        });

        $('.transition-value-toggle-button').toggleButtons({
            transitionspeed: 1 // default value: 0.05
        });

    }

    var handleTables = function () {
        if (!jQuery().dataTable) {
            return;
        }

        // begin first table
        $('#sample_1').dataTable({
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_1 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_1_wrapper .dataTables_filter input').addClass("input-medium"); // modify table search input
        jQuery('#sample_1_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown

        // begin second table
        $('#sample_2').dataTable({
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_2 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_2_wrapper .dataTables_filter input').addClass("input-small"); // modify table search input
        jQuery('#sample_2_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown

        // begin: third table
        $('#sample_3').dataTable({
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_3 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_3_wrapper .dataTables_filter input').addClass("input-small"); // modify table search input
        jQuery('#sample_3_wrapper .dataTables_length select').addClass("input-mini"); // modify table per page dropdown
    }

    var handleDateTimePickers = function () {

        if (!jQuery().daterangepicker) {
            return;
        }

        $('.date-range').daterangepicker();

        $('#dashboard-report-range').daterangepicker({
            ranges: {
                'Today': ['today', 'today'],
                'Yesterday': ['yesterday', 'yesterday'],
                'Last 7 Days': [Date.today().add({
                    days: -6
                }), 'today'],
                'Last 30 Days': [Date.today().add({
                    days: -29
                }), 'today'],
                'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                'Last Month': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: 'left',
            format: 'MM/dd/yyyy',
            separator: ' to ',
            startDate: Date.today().add({
                days: -29
            }),
            endDate: Date.today(),
            minDate: '01/01/2012',
            maxDate: '12/31/2014',
            locale: {
                applyLabel: 'Submit',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom Range',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        },

        function (start, end) {
            App.blockUI(jQuery("#page"));
            setTimeout(function () {
                App.unblockUI(jQuery("#page"));
                $.gritter.add({
                    title: 'Dashboard',
                    text: 'Dashboard date range updated.'
                });
                App.scrollTo();
            }, 1000);
            $('#dashboard-report-range span').html(start.toString('d MMMM , yyyy') + ' - ' + end.toString('d MMMM, yyyy'));

        });

        $('#dashboard-report-range span').html(Date.today().add({
            days: -29
        }).toString('d MMMM, yyyy') + ' - ' + Date.today().toString('d MMMM, yyyy'));

        $('#form-date-range').daterangepicker({
            ranges: {
                'Today': ['today', 'today'],
                'Yesterday': ['yesterday', 'yesterday'],
                'Last 7 Days': [Date.today().add({
                    days: -6
                }), 'today'],
                'Last 30 Days': [Date.today().add({
                    days: -29
                }), 'today'],
                'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                'Last Month': [Date.today().moveToFirstDayOfMonth().add({
                    months: -1
                }), Date.today().moveToFirstDayOfMonth().add({
                    days: -1
                })]
            },
            opens: 'right',
            format: 'dd/MM/yyyy',
            separator: ' to ',
            startDate: Date.today().add({
                days: -29
            }),
            endDate: Date.today(),
            minDate: '01/01/2012',
            maxDate: '12/31/2014',
            locale: {
                applyLabel: 'Submit',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom Range',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            },
            showWeekNumbers: true,
            buttonClasses: ['btn-danger']
        },

        function (start, end) {
            $('#form-date-range span').html(start.toString('d MMMM, yyyy') + ' - ' + end.toString('d MMMM, yyyy'));
        });

        $('#form-date-range span').html(Date.today().add({
            days: -29
        }).toString('d MMMM, yyyy') + ' - ' + Date.today().toString('d MMMM , yyyy'));


        if (!jQuery().datepicker || !jQuery().timepicker) {
            return;
        }
        $('.date-picker').datepicker({ dateFormat: 'dd/mm/yyyy' });

        $('.timepicker-default').timepicker();

        $('.timepicker-24').timepicker({
            minuteStep: 1,
            showSeconds: true,
            showMeridian: false
        });

    }

    var handleColorPicker = function () {
        if (!jQuery().colorpicker) {
            return;
        }
        $('.colorpicker-default').colorpicker({
            format: 'hex'
        });
        $('.colorpicker-rgba').colorpicker();
    }

    var handleAccordions = function () {
        $(".accordion").collapse().height('auto');
    }

    var handleScrollers = function () {
        if (!jQuery().slimScroll) {
            return;
        }

        $('.scroller').each(function () {
            $(this).slimScroll({
                //start: $('.blah:eq(1)'),
                height: $(this).attr("data-height"),
                alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
                railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
                disableFadeOut: true
            });
        });
    }

    // alert messages ::
    var alertmsg = function(msg){
    $(".alert").fadeOut('slow');
    $(".alert").fadeIn('slow');$(".alert").html(msg);
    scrollers();

    }
    var alertmsgs = function(){
        $(".alert").fadeOut('slow');
    }


     //scroll to top ::
     var scrollers = function(){
    $('html, body').animate({scrollTop : 0},800);
     }

//validate phone number
    var validatephone = function(inputtxt){
    var testPhoneNumber = inputtxt; // Typical Ugandan Mobile Minus phone number
    var phn = testPhoneNumber.replace(/\s/g,'');
    var testPattern = /[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]/;
    if(testPhoneNumber.length == 10){
    return( testPattern.test(phn) );
    }
    else
    {
        return false;
    }
    }
    //validate email address
    var validateemail = function(inputtxt){
    var str= inputtxt;
    var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    if (filter.test(str))
    return true
    else{
    return false
    }
    }
    // VALIDATE WEB URL
    var validateweb = function(inputtxt){
    var message;
    var myRegExp =/^(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;
    var urlToValidate = inputtxt;
    if (!myRegExp.test(urlToValidate)){
    return false;
    }else{
    return true;
    }

    }

  function moversvalidator(e)
    {
        var form = e;
        var formid = form.id;
        var datatype = $("#"+formid).attr('data-type');
         //action url to commit your data
        var dataaction =   $("#"+formid).attr('data-action');
        // alert(dataaction);
        // return false;
        var dataelements =   $("#"+formid).attr('data-elements');
        console.log(dataelements);
       // alert(dataelements);
          //server side checks ::--
         var serversidecheckss =  $("#"+formid).attr('data-cheks');
         //the url where you are going to use for server side checks on data
         var datacheckaction =  $("#"+formid).attr('data-check-action');

             url = dataaction;

            if(dataelements.length > 0)
            {
                var fieldNameArr=dataelements.split("<>");
            }
            else
            {
                fieldNameArr = Array();
            }
             var elementfield  ='';
             var formdata = {};
             var commitcheckdata = {};

            if((dataelements!= ' ') &&(dataelements.length > 0))
            {
                //ITERATE THROUGH DATA ELEMENTS
                 for(var i=0;i<fieldNameArr.length;i++)
                 {
                    //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED
                     var lke = fieldNameArr[i].split("*");
                      elementfield = lke[1];
                      formvalue = $("#"+elementfield).val();
                         if(fieldNameArr[i].charAt(0)=="*"){
                          if(formvalue.length <= 0)
                          {
                          alertmsg('Fill Blanks'); return false;
                          }
                      }

                        else
                        {
                            elementfield = fieldNameArr[i];
                            formvalue = $("#"+elementfield).val();
                        }

                   var datatype =  $("#"+elementfield).attr('datatype');


                    switch(datatype)
                    {
                        case 'text':
                        break;
                        case 'tel':

                        if(formvalue.length > 0)
                        {
                        var valu = validatephone(formvalue);
                        if(valu == false)
                        {
                            alertmsg('Invalid Phone, Digits are allowed, Not More than 10 digits'); return false;
                        }


                        }
                        break;
                        case 'web':

                        if(formvalue.length > 0)
                        {
                        var valu = validateweb(formvalue);
                        if(valu == false)
                        {
                             alertmsg('Invalid Web Url '); return false;
                        }

                        }
                        break;
                        case 'email':

                        if(formvalue.length > 0)
                        {
                            var valu = validateemail(formvalue);
                            if(valu == false)
                            {

                                alertmsg('Ivalid Email Address'); return false;
                            }


                        }
                        break;
                        case 'phone':
                        if(formvalue.length > 0)
                        {
                        var valu = validatephone(formvalue);
                        if(valu == false)
                        {
                            alertmsg('Invalid Phone, Digits are allowed, Not More than 10 digits'); return false;
                        }

                        }
                        break;
                        default:
                        break;
                    }

                    // ADDING ELEMETNTS TO THE FORM
                    formdata[elementfield] =formvalue;
                     }
                    console.log(formdata);

                 //CHECK FORM ONLINE IF POSSIBLE
                 if(serversidecheckss.length > 0)
                    {
                       //  var fieldchekArr=serversidecheckss.split("<>");
                       //  for(var i=0;i<fieldchekArr.length;i++){
                       //          elementfield = fieldchekArr[i];
                       //          formvalue = $("#"+elementfield).val();
                       //          commitcheckdata[elementfield] =formvalue;
                       //  }
                       //  console.log(commitcheckdata);
                       // var stats =  serversidechek(commitcheckdata,datacheckaction);
                       // if(stats == 1) {
                       //  return true
                       // }
                       // else {
                       //      return false;
                       //  }
                    }


if(statusquo == 1)
{
   submitform(formdata,url);
  console.log(formdata);
}
else
{
    alertmsg('Check PDE Name, Sever Error'); return false;
}
                    //POST DATA TO THE SERVER
                    //SUBMIT FORM  TO THE SERVER


            }

        return true;
    }


//
//data to be checked on the server ajaxcally
function serversidechek(formdata,url)
{
   // alert(url);
    $.ajax({
        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){
            return data;
        //     console.log(data);
        //     if(data == 0)
        //     {
        //         submitform(formdata,url);
        //     }
        // else{alertmsg(data);}


        },
        error:function(data , textStatus, jqXHR)
        {
        console.log('Ajax Error');
        alert('Ajax Error');
           // alert(data);


        }
    });
}
function exit()
{

}
  mkx = 0;
//SERVER SIDE CHECK LEVEL 2
 function serversidechek2(formdata,url)
{
    $.ajax({
        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){
            var datareceived = data.split(':');

            if(datareceived[0] == 0)
            {
                alert('User Exists '+ datareceived[2]);
                mkx = 0;


            }
            else
            {
                alert('Clean Data');
                  mkx = 1;

            }

        },
        error:function(data , textStatus, jqXHR)
        {
        console.log('Ajax Error');
        alert('Ajax Error');
           // alert(data);


        }
    });
}


//submit form to the server
var submitform = function(formdata,url){

    $.ajax({
        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){
//alert(data);
            console.log(data);
            if(data == 1) {
            console.log('Record Saved Succesfully');
           // alertmsg('Record Saved Succesfully');  \
                   switch(indexed)
                   {
                    case 1:
                    switchtabs();
                    break;
                    case 2:
                    alert('Record Saved');
                    // SHIFT TO THE OTHER SIDE ::
                    break;

                    }
            }
            else{
                 console.log('Something Went wrong, Contact Site Administrator')
                 alertmsg('Something Went wrong, Contact Site Administrator');
            }
            console.log(data+textStatus+jqXHR);
        },
        error:function(data , textStatus, jqXHR)
        {
            console.log('Data Error'+data+textStatus+jqXHR);
            alert(data);
        }
    });
}

 //SWITCH TABS
var switchtabs = function(){

                var tab = tabd;
                var navigation = navtion;
                var index = indexed;
                var total = navigation.find('li').length;
                var current = index + 1;

                /* end of getting form imformation */
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
        }

//FORM VALIDATION
function formvalidation(currenttab){
    switch(currenttab){
    case 1:
     var forms = document.forms['pderegistration'];
    var vform = moversvalidator(forms);
   //alert(vform);
    return vform;
    //alert(forms);
    break;

    case 2:

    var forms = document.forms['pderegistration2'];
    return forms;
    // var vform = moversvalidator(forms);
    // alert(vform);
    // return vform;
    break;
    }

    }

var handleFormWizards = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }

        $('#form_wizard_1').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index) {
                alert('on tab click disabled');
                return false;
            },
    onNext: function (tab, navigation, index) {

             // HANDLE THE ON NEXT FUNCTION
             tabd = tab;
             navtion  = navigation;
             indexed = index;
// switchtabs();
            // \\ // \\ // \\ // \\
             ist = formvalidation(index);
             if(ist == false){ return false;
             }
             else{
            switchtabs();
       }

            /* get form information  */
            // validate form information :
           // var istrue = formvalidation(index);

            },
    onPrevious: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }

                App.scrollTo($('.page-title'));
            },
    onTabShow: function (tab, navigation, index) {

                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#form_wizard_1').find('.bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#form_wizard_1').find('.button-previous').hide();
        $('#form_wizard_1 .button-submit').click(function () {
            //handle the last Record and submit :: //


            //ist = formvalidation(2);
             handleform2();
            // formvalidation(2);
        }).hide();
    }

var handleform2 = function(){
    var form = document.forms['pderegistration2'];

     var formid = form.id;
        var datatype = $("#"+formid).attr('data-type');
         //action url to commit your data
        var dataaction =   $("#"+formid).attr('data-action');
        // alert(dataaction);
        // return false;
        var dataelements =   $("#"+formid).attr('data-elements');
       // console.log(dataelements);
       // alert(dataelements);
          //server side checks ::--
         var serversidecheckss =  $("#"+formid).attr('data-cheks');
         //the url where you are going to use for server side checks on data
         var datacheckaction =  $("#"+formid).attr('data-check-action');


             url = dataaction;
             var elementfield  ='';
             var formdata = usergt;
             var commitcheckdata = {};


            //SERVER SIDE CHECKS
             if(serversidecheckss.length > 0)
                    {

        $.ajax({
        type: "POST",
        url:  datacheckaction,
        data: formdata,
        success: function(data, textStatus, jqXHR){
            console.log(data);
            alertmsg(data);
            //return false;
            var datareceived = data.split(':');

            if(datareceived[0] == 0)
            {
                alertmsg('User Exists '+ datareceived[2]);
                mkx = 0;


            }
            else if(datareceived[0] == 2)
            {
                alertmsg('User : '+ datareceived[2]+' Belongs to A Different PDE');
                mkx = 0;


            }
            else
            {
                console.log('Clean Data');
                  mkx = 1;

                        console.log(formdata);

                  //  submitform(formdata,url);
                     $.ajax({
        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){

            console.log(data);
            if(data == 1) {
            console.log('Record Saved Succesfully');
            alertmsg('Record Saved Succesfully');

            //reload url to the level 2 issues
            location.href = baseurl()+'admin/manage_pdes';
            }
            else{
                 console.log('Something Went wrong, Contact Site Administrator')
                 alertmsg('Something Went wrong, Contact Site Administrator');
            }
            console.log(data+textStatus+jqXHR);
        },
        error:function(data , textStatus, jqXHR)
        {
            console.log('Data Error'+data+textStatus+jqXHR);
            alert(data);
        }
    });

            }

        },
        error:function(data , textStatus, jqXHR)
        {
        console.log('Ajax Error');
        alert('Ajax Error');
           // alert(data);


        }
    });

                       // alert('momere');
                       // if(mkx == 0) {
                       //  return false;
                       // }
                       // else
                       // {

                       // }

                    }
                    //submit form to the server ::



}

var handleTagsInput = function () {
        if (!jQuery().tagsInput) {
            return;
        }
        $('#tags_1').tagsInput({
            width: 'auto'
        });
        $('#tags_2').tagsInput({
            width: 240
        });
    }

var handleGoTop = function () {
        /* set variables locally for increased performance */
        jQuery('#footer .go-top').click(function () {
            App.scrollTo();
        });

    }

/*
 $('#main-content').animate({
                    'margin-left': '210px'
                });

                $('#sidebar').animate({
                    'margin-left': '-200px'
                }, {
                    complete: function () {
                        $('#sidebar > ul').hide();
                        $("#container").addClass("sidebar-closed");
                    }
                });  */

// this is optional to use if you want animated show/hide. But plot charts can make the animation slow.
var handleSidebarTogglerAnimated = function () {
 
        $('.sidebar-toggler').click(function () {

 
            if ($('#sidebar > ul').is(":visible") === true) {
                $('#main-content').animate({
                    'margin-left': '25px'
                });

                $('#sidebar').animate({
                    'margin-left': '-190px'
                }, {
                    complete: function () {
                        $('#sidebar > ul').hide();
                        $("#container").addClass("sidebar-closed");
                    }
                });
            } else {
                $('#main-content').animate({
                    'margin-left': '215px'
                });
                $('#sidebar > ul').show();
                $('#sidebar').animate({
                    'margin-left': '0'
                }, {
                    complete: function () {
                        $("#container").removeClass("sidebar-closed");
                    }
                });
            }
        })  
    }

    // by default used simple show/hide without animation due to the issue with handleSidebarTogglerAnimated.
var handleSidebarToggler = function () {

 
 
        $('.sidebar-toggler').click(function () {
            if ($('#sidebar > ul').is(":visible") === true) {
                $('#main-content').css({
                    'margin-left': '25px'
                });
                $('#sidebar').css({
                    'margin-left': '-190px'
                });
                $('#sidebar > ul').hide();
                $("#container").addClass("sidebar-closed");






                 ;
            } else {
               $('#main-content').css({
                    'margin-left': '215px'
                });
                $('#sidebar > ul').show();
                $('#sidebar').css({
                    'margin-left': '0'
                });
              $("#container").removeClass("sidebar-closed");
 
               
            }
        })
    }

    return {

        //main function to initiate template pages
        init: function () {
            if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) == 8) {
                isIE8 = true; // checkes for IE8 browser version
                $('.visible-ie8').show();
            }

            handleDeviceWidth(); // handles proper responsive features of the page
            handleChoosenSelect(); // handles bootstrap chosen dropdowns

            if (isMainPage) {
                handleDashboardCharts(); // handles plot charts for main page
                handleDashboardCalendar(); // handles full calendar for main page
                handleChat() // handles dashboard chat
            } else {
                handleCalendar(); // handles full calendars
                handlePortletSortable(); // handles portlet draggable sorting
            }

            if (isMapPage) {
                handleAllJQVMAP(); // handles vector maps for interactive map page
            }

            handleScrollers(); // handles slim scrolling contents
            handleUniform(); // handles uniform elements
            handleClockfaceTimePickers(); //handles form clockface timepickers
            handleTagsInput() // handles tag input elements
            handleTables(); // handles data tables
            handleCharts(); // handles plot charts
            handleWidgetTools(); // handles portlet action bar functionality(refresh, configure, toggle, remove)
            handlePulsate(); // handles pulsate functionality on page elements
            handlePeity(); // handles pierty bar and line charts
            handleGritterNotifications(); // handles gritter notifications
            handleTooltip(); // handles bootstrap tooltips
            handlePopover(); // handles bootstrap popovers
            handleToggleButtons(); // handles form toogle buttons
            handleWysihtml5(); //handles WYSIWYG Editor
            handleDateTimePickers(); //handles form timepickers
            handleColorPicker(); // handles form color pickers
            handleFancyBox(); // handles fancy box image previews
            handleStyler(); // handles style customer tool
            handleMainMenu(); // handles main menu
            handleFixInputPlaceholderForIE(); // fixes/enables html5 placeholder attribute for IE9, IE8
            handleGoTop(); //handles scroll to top functionality in the footer
            handleAccordions();
            handleFormWizards();
            handleSidebarToggler();

        },

        // login page setup
    initLogin: function () {
            handleLoginForm();
            handleFixInputPlaceholderForIE();
        },

        // wrapper function for page element pulsate
    pulsate: function (el, options) {
            var opt = jQuery.extend(options, {
                color: '#d12610', // set the color of the pulse
                reach: 15, // how far the pulse goes in px
                speed: 300, // how long one pulse takes in ms
                pause: 0, // how long the pause between pulses is in ms
                glow: false, // if the glow should be shown too
                repeat: 1, // will repeat forever if true, if given a number will repeat for that many times
                onHover: false // if true only pulsate if user hovers over the element
            });

            jQuery(el).pulsate(opt);
        },

        // wrapper function to scroll to an element
    scrollTo: function (el) {
            pos = el ? el.offset().top : 0;
            jQuery('html,body').animate({
                scrollTop: pos
            }, 'slow');
        },

        // wrapper function to  block element(indicate loading)
    blockUI: function (el, loaderOnTop) {
            lastBlockedUI = el;
            jQuery(el).block({
                message: '<img src="img/loading.gif" align="absmiddle">',
                css: {
                    border: 'none',
                    padding: '2px',
                    backgroundColor: 'none'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.05,
                    cursor: 'wait'
                }
            });
        },

        // wrapper function to  un-block element(finish loading)
    unblockUI: function (el) {
            jQuery(el).unblock({
                onUnblock: function () {
                    jQuery(el).removeAttr("style");
                }
            });
        },

        // set main page
    setMainPage: function (flag) {
            isMainPage = flag;
        },

        // set map page
    setMapPage: function (flag) {
            isMapPage = flag;
        }

    };

    //input mask

    $('.inputmask').inputmask();

}();

//tooltips

$('.element').tooltip();


// Slider input js
try{
    jQuery("#Slider1").slider({ from: 5, to: 50, step: 2.5, round: 1, dimension: '&nbsp;$', skin: "round_plastic" });
    jQuery("#Slider2").slider({ from: 5000, to: 150000, heterogeneity: ['50/50000'], step: 1000, dimension: '&nbsp;$', skin: "round_plastic" });
    jQuery("#Slider3").slider({ from: 1, to: 30, heterogeneity: ['50/5', '75/15'], scale: [1, '|', 3, '|', '5', '|', 15, '|', 30], limits: false, step: 1, dimension: '', skin: "round_plastic" });
    jQuery("#Slider4").slider({ from: 480, to: 1020, step: 15, dimension: '', scale: ['8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'], limits: false, skin: "round_plastic", calculate: function( value ){
        var hours = Math.floor( value / 60 );
        var mins = ( value - hours*60 );
        return (hours < 10 ? "0"+hours : hours) + ":" + ( mins == 0 ? "00" : mins );
    }});
} catch (e){
    errorMessage(e);
}


//knob

$(".knob").knob();
