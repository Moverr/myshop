
<style>
.error{
    border:2px solid #ff9494;
}
</style>

<script type="text/javascript">




    $(document).on('click','.printer',function(){


        $(".table").printArea();

    })



    $(function(){

      
   
      
        $('.table').dataTable({
            "paging":   false,
            "ordering": true,
            "info":     false ,
            "processing": true,
            "serverSide": true,
            "ajax": "<?=base_url().$search_url; ?>",
            "dataSrc": "tableData"


        });

        var oTable = $('.table').dataTable();

        $("div.dataTables_filter input").unbind();

        $('#filter').click(function(e){
            oTable.fnFilter($("div.dataTables_filter input").val());
        });



    

            $(".view_lots").click(function(){
            $("#lightbox_wrapper").fadeIn('slow');
            $("#lightbox_heading").html(" ");
            $("#lightbox_body").html("Proccessing....");
            formdata = {};
            //url
            var idd = this.id;
            var url = $("#"+idd).attr('data-ref');
            contract = idd.split("_");
            contractid = contract[1];
            console.log(url);
            formdata['bidid'] = contractid;
            console.log(formdata);


            $.ajax({

                type: "POST",
                url:  url,
                data: formdata,
                success: function(data, textStatus, jqXHR){
                    $("#lightbox_body").html(data);
                },
                error:function(data , textStatus, jqXHR)
                {
                    console.log('Data Error'+data+textStatus+jqXHR);
                }

            });
        });

    })
 
 
contractid = 0;
var datesigned = '';

function selectionchange(st)
{
    //alert(st);
    if(st == 'UGX')
    {
        $("#exchange_rate").fadeOut('fast');
    }
    else
    {
        $("#exchange_rate").fadeIn('fast');
    }
}

//Terminate Contract "" 
function terminate_contract()
{

    var termination_reason =      $("#termination_reason").val();
    var date_contract_terminated = $("#date_contract_terminated").val();
    var formdata = {};
    //alert(date_contract_terminated);

    if((termination_reason.length > 0) && (date_contract_terminated.length > 0))
    {
        formdata['termination_reason'] = termination_reason;
        formdata['date_contract_terminated'] = date_contract_terminated;

        $("#lightbox_heading").html('PROCESSING ... ');
        // return false;
        url = this.geturl;

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,
            success: function(data, textStatus, jqXHR){
                if(data == 1)
                {
                    $("#termination_reason").val('');
                    $("#date_contract_terminated").val('');
                    $("#lightbox_heading").html('<label class="warning" style="                border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">CONTRACT TERMINATED SUCCESSFULLY</label>'); return false;
                    location.reload(0);
                }
                else
                {
                    $("#lightbox_heading").html('<label class="warning" style="                border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">SOMETHING WENT WRONG, CONTACT SITE ADMINISTRATOR</label>'); return false;

                }
                // $("#lightbox_body").html(data);
                //  alert("reached")
            },
            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

        });


    }
    else
    {
        $("#lightbox_heading").html('<label class="warning alert alert-important emailalert " style=" background-color: #fcf8e3;    border: 1px solid #fbeed5; padding:5px;  width:110%;">Fill Blanks</label>'); return false;

    }

}

var geturl  = '';
function terminateContract(url)
{
    geturl = url;

    //alert(url);
    var result = confirm("You about to Terminate this Contract \n Click Ok to Proceed ");
    if(result == false)
        return;
    $("#lightbox_wrapper").fadeIn('slow');
    $("#lightbox_heading").html(" ");
    $("#lightbox_body").html("Proccessing....");

    var strng = '<div style="width:80%;margin:auto; text-aligh:center;" >'+
        ' <div class="control-group"><h2>TERMINATE CONTRACT</h2></div>'+

        '<div class="container-fluid">'+
        '<div class="row">'+
        '<div class="col-md-12">'+
        '<form role="form">'+
        '<div class="form-group">'+

        '<label for="termination_reason">'+
        ' ENTER REASON FOR TERMINATION '+
        '</label>'+
        '<textarea type="text" class="form-control termination_reason" style="width:100%" id="termination_reason" >'+
        '</textarea>'+
        '</div>'+

        ' <div class="form-group">'+
        '<label for="date_contract_terminated">'+
        ' Date Contract Terminated'+
        '</label>'+
        ' <input type="date" class="form-control date_contract_terminated " style="width:100%" id="date_contract_terminated" />'+
        '</div>'+

        '<button type="button" onClick="javascript:$(`#lightbox_wrapper`).fadeOut(`slow`);" class="btn btn-default" data-dismiss="modal">Close</button>'+
        ' &nbsp;'+
        '<button onClick="javascript:terminate_contract();" type="button" class="btn  add_callof_order">Submit</button> '

    '</form>'+
    '</div>'+
    '</div>'+
    '</div>'+ '</div>';


    $("#lightbox_body").html(strng);

}

//Change the Status of the form based on request
/*
 Awarded or Completed
 */
function statusofprocurement(st)
{

    // if awarded
    /*
     Date of Actual COmpletion is Optional
     */
    if(st == 'completed')
    {
        $("#actual_completion_date").removeAttr('readonly');
    }
    else
    {
        $("#actual_completion_date").attr('readonly','readonly');
    }

    /*
     Else its not optional
     */
}



$(function(){


    //financial Years Issue ::
    var url  = '<?=base_url()?>contracts/manage_contracts/';
    url += 'level/active';

    $(".financial_year_selection").change(function(){
        //alert($(this).val());
        var financial_year = $(this).val().trim();

        if(financial_year.length > 0)
        {
            url += '/financial_year/'+financial_year;
            location.href =url;
        }



    })


    $(".view_variations").click(function(){
        $("#lightbox_wrapper").fadeIn('slow');
        $("#lightbox_heading").html(" ");
        $("#lightbox_body").html("Proccessing....");
        formdata = {};
        //url
        var idd = this.id;
        var url = $("#"+idd).attr('data-ref');
        contract = idd.split("_");
        contractid = contract[1];
        console.log(url);
        formdata['contractid'] = contractid;

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,
            success: function(data, textStatus, jqXHR){
                $("#lightbox_body").html(data);
            },
            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

        });


        //end url fetch
        //alert(url);

    });



    xx = 0;
    bid_invitation_calloff = 0;
    $(".togglecalloforders").click(function(){


        datesigned = $(this).data('datesigned');
         bid_invitation_calloff =  $(this).data('bid');

        var strng = '<div style="width:80%;margin:auto; text-aligh:center;" >'+
            ' <div class="control-group"><h2>ADD CALLOFF ORDER </h2></div>'+

            '<div class="container-fluid">'+
            '<div class="row">'+
            '<div class="col-md-12">'+
            '<form role="form">'+
            '<div class="form-group">'+

            '<label for="call_off_order_no">'+
            ' Call off order No.'+
            '</label>'+
            '<input type="text"  class="form-control call_off_order_no" id="call_off_order_no" style="width:100%" />'+

             '<input type="hidden"  class="form-control call_off_id" id="call_off_id" style="width:100%" />'+
             
            '</div>'+

            '<div class="form-group">'+
            '<label for="call_off_order_no">'+
            ' Select Provider.'+
            '</label>'+
            '<select  style="width:100%" id="calloff_provider" name="calloff_provider"><option>Select</option></select>'+
            '</div>'+


            '<div class="form-group">'+
            '<label for="exampleInputPassword1">'+
            ' Subject of Procurement.'+
            '</label>'+
            '<textarea type="email" class="form-control subject_of_procurement" style="width:100%" id="subject_of_procurement" >'+
            '</textarea>'+
            '</div>'+

            '<div class="form-group">'+
            '<label for="exampleInputPassword1">'+
            ' Status of Procurement.'+
            '</label>'+
            '<select    class="form-control status_of_procurement" id="status_of_procurement" style="width:100%" onChange="javascript:statusofprocurement(this.value);" >'+
            ' <option value="awarded">Awarded </option>'+
            ' <option value="completed">Completed </option>'+
            '</select >'+

            '</div>'+


            ' <div class="form-group">'+
            '<label for="exampleInputPassword1">'+
            ' Date of Call off Order'+
            '</label>'+
            ' <input type="date" class="form-control date_of_calloff_orders" style="width:100%" id="date_of_calloff_orders" />'+
            '</div>'+

            '<div class="form-group">'+
            '<label for="user_department">'+
            'Name of User Department'+
            '</label>'+
            '<input type="text" class="form-control user_department"  style="width:100%" id="user_department" /> '+
            ' </div>'+


            '<div class="form-group">'+
            '<label for="contract_value">'+
            ' Contract Value (UGX)'+
            '</label>'+
            '<input type="amount"  onChange="javascript:$(this).val(addCommas($(this).val()))" class="form-control   contract_value" style="width:100%" id="contract_value" /> '+
            '</div>'+

            '<div class="form-group">'+
            '<label for="planned_completion_date">'+
            ' Planned Contract Completion Date'+
            ' </label>'+
            ' <input type="date" class="form-control planned_completion_date" style="width:100%"  id="planned_completion_date" /> '+
            '</div>'+

            '<div class="form-group">'+
            '<label for="actual_completion_date">'+
            'Date of Actual Contract Completion'+
            '</label>'+
            '<input readonly="readonly"  type="date" class="form-control actual_completion_date" style="width:100%"  id="actual_completion_date" />'+
            '</div>'+

            ' <div class="form-group">'+
            '<label for="total_actual_payments">'+
            ' Total Actual Payments Up to End of Reporting Period'+
            ' </label>'+
            '<input type="text"   onChange="javascript:$(this).val(addCommas($(this).val()))" class="form-control total_actual_payments" style="width:100%"  id="total_actual_payments"  />'+
            '</div>'+



            '<button type="button" onClick="javascript:$(`#lightbox_wrapper`).fadeOut(`slow`);" class="btn btn-default" data-dismiss="modal">Close</button>'+
            ' &nbsp;'+
            '<button onClick="javascript:add_callof_order();" type="button" class="btn  add_callof_order">Submit</button> '


        '</form>'+
        '</div>'+
        '</div>'+
        '</div>'+ '</div>';



//end
        contractid = this.id;
        $("#lightbox_wrapper").fadeIn('slow');
        $("#lightbox_heading").html(" ");
        $("#lightbox_body").html("Proccessing....");

        $("#lightbox_body").html(strng);

        fetch_contracted_framework_providers();


        // $("#myModal").modal('toggle');
        if(xx == 1)
        {

            // $("#tblform").fadeOut('slow');
            xx = 0;
        }
        else
        {
            // $("#tblform").fadeIn('slow');
            xx = 1;
        }

    });


// FETCH CALL OFF ORDERS PROVIDERS

    function  fetch_contracted_framework_providers(){
        if(contractid <= 0)
            return;

        formdata = {};
        formdata['contractid'] = contractid;
        formdata['bid_invitation_calloff'] = bid_invitation_calloff;
        
        // alert("Proccessing...");
        $("#calloff_provider").empty().append("<option>Proccessing ... </option>");
        //document.getElementById('call_off_order_no').readOnly = true;

        var url = baseurl()+"contracts/fetch_contracted_framework_providers";

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,

            success: function(data, textStatus, jqXHR){
                console.log(data);
                $("#calloff_provider").empty().append(data);


                proccess_call0ff_order_sequence();



            },

            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

        });


    }


    // Proccess Call off Order Sequence Number
    /*
     Use the Procurement Reference Number at IFB and
     get info out of it
     */

    function  proccess_call0ff_order_sequence()
    {
        if(contractid <= 0)
            return;

        formdata = {};
        formdata['contractid'] = contractid;
        formdata['bid_invitation_calloff'] = bid_invitation_calloff;
        // alert("Proccessing...");
        $("#call_off_order_no").val("Proccessing ... ");
        //document.getElementById('call_off_order_no').readOnly = true;

        var url = baseurl()+"contracts/proccess_calloff_order_sequence_number";

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,

            success: function(data, textStatus, jqXHR){
                console.log(data);
                $("#call_off_order_no").val(data);

            },

            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

        });

    }





    $(".viewlistcalloff").click(function(){
 
         bidinvitation_id =  $(this).data('bid');
         contractid = this.id;
     
        $("#lightbox_wrapper").fadeIn('slow');
        $("#lightbox_heading").html(" ");
        $("#lightbox_body").html("Proccessing....");




        var url = baseurl()+"contracts/viewcalloutcontracts";
        var formdata = {};
        if(bidinvitation_id <= 0)
            return;

        formdata['bidinvitation_id'] = bidinvitation_id;
        formdata['contractid'] = contractid;

        console.log(formdata);

        $.ajax({

            type: "POST",
            url:  url,
            data: formdata,

            success: function(data, textStatus, jqXHR){
                // $(".viewcalloff_body").html(data);
                $("#lightbox_heading").html(" ");
                $("#lightbox_body").html(data);
                console.log(data);
                //alert(data); exit();
            },

            error:function(data , textStatus, jqXHR)
            {
                console.log('Data Error'+data+textStatus+jqXHR);
                //alert(data);
            }

        });



        //proccessing...

    });






})



var compare_dates = (
    function(){

        var compare = {};
        var call_off_order_date = '';
        var contract_completion_date = '';
        var planned_completion_date = '';
        var status = 2;

        // Set Call Off Order Date
        compare.set_call_off_order_date = function(date_string){
            call_off_order_date = date_string;
        }

        // Get Call Off Order Date
        compare.get_call_off_order_date = function(){
            return call_off_order_date;
        }

        ///Set Planned Completion Date
        compare.set_planned_completion_date = function(date_string){
            planned_completion_date = date_string;
        }

        // Get Planned Completion Date
        compare.get_planned_completion_date = function(){
            return planned_completion_date;
        }

        // Get Planned Completion Date
        compare.getStatus = function(){
            return status;
        }
        //set status
        compare.setStatus = function(status){
            status = status;
        }



        ///Set Contract Completion Date
        compare.set_contract_completion_date = function(date_string){
            contract_completion_date = date_string;
        }

        // Get Contract Completion Date
        compare.get_contract_completion_date = function(){
            return contract_completion_date;
        }





        //compare 2
        compare.compare_call_off_order_planned_completion_date1 = function(){
            var planned_date_completion = compare.get_planned_completion_date().trim();
            var call_off_order_date = compare.get_call_off_order_date().trim();

            if((planned_date_completion.length > 2) && (call_off_order_date.length > 2) )
            {
                var dd1 = new Date(call_off_order_date);
                var dd2 = new Date(planned_date_completion);
                status = 1;
                if(dd1 > dd2)
                {
                    alert("Call Off Order Date Can not be Greater than  Planned Contract Completion Date ");
                    status = 0;
                }


            }
            else
            {
                status = 0;
            }



        }

        //compare 1
        compare.compare_call_off_order_planned_completion_date = function(){
            var planned_date_completion = compare.get_planned_completion_date().trim();
            var contract_completion_date = compare_dates.get_contract_completion_date();

            if((planned_date_completion.length > 2) && (contract_completion_date.length > 2) )
            {
                var ddd1 = new Date(planned_date_completion);
                var ddd2 = new Date(contract_completion_date);
                status = 1;
                if(ddd1 > ddd2)
                {
                    alert("Planned Date of Completion Date  Can not be Greater than   Contract Completion Date ");
                    status = 0;
                }

            }
            else
            {
                status = 0;
            }



        }


        return compare;
    }());

var formdata = {};

//callofforders
function add_callof_order(){

  
    
    var call_off_order_no = $("#call_off_order_no").val();
    var call_off_id = $("#call_off_id").val();
    var subject_of_procurement = $("#subject_of_procurement").val();
    var date_of_calloff_orders =  $("#date_of_calloff_orders").val();
    var user_department = $("#user_department").val();
    var contract_value = $("#contract_value").val();
    var planned_completion_date = $("#planned_completion_date").val();
    var actual_completion_date = $("#actual_completion_date").val();
    var total_actual_payments = $("#total_actual_payments").val();
    var status_of_procurement = $("#status_of_procurement").val();

    var calloff_provider = $("#calloff_provider").val();
    if(isNaN(calloff_provider) )
    {
        alert("Select Provider");
        return;
    }


    if(contractid <= 0)
        return;


    /*  $("#call_off_order_no").removeClass('error');
     $("#date_of_calloff_orders").removeClass('error');
     $("#user_department").removeClass('error');
     $("#contract_value").removeClass('error');
     $("#planned_completion_date").removeClass('error');  */


    //alert("almost Ready");

    if(status_of_procurement == 'awarded')
    {
        if((call_off_order_no.length <= 0 )|| (date_of_calloff_orders.length <= 0) || (user_department.length <= 0) || (contract_value.length <= 0)  || (planned_completion_date.length <= 0) )
        {
            $("#lightbox_heading").html('<label class="warning" style="  border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">Fill Blanks</label>');
            alert("Fill Blanks");
            return false;
        }
    }


    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();

    if(dd<10) {  dd='0'+dd    }

    if(mm<10) { mm='0'+mm     }
    today = mm+'/'+dd+'/'+yyyy;




    var date_contract_signed =  new Date(datesigned);

    dd = date_contract_signed.getDate();
    mm = date_contract_signed.getMonth()+1; //January is 0!
    yyyy = date_contract_signed.getFullYear();

    if(dd<10) {  dd='0'+dd    }

    if(mm<10) { mm='0'+mm     }
    date_contract_signed = mm+'/'+dd+'/'+yyyy;

    if(status_of_procurement == 'completed')
    {
        if((call_off_order_no.length <= 0 )|| (date_of_calloff_orders.length <= 0) || (user_department.length <= 0) || (contract_value.length <= 0)  || (planned_completion_date.length <= 0) ||(actual_completion_date.length <= 0) )
        {
            alert("Fill Blanks");
            $("#lightbox_heading").html('<label class="warning" style="  border: 1px solid #fbeed5;     background-color: #fcf8e3; padding:5px; margin-left:80px; width:100%; padding:5px; margin-left:80px; padding:5px;">Fill Blanks</label>'); return false;
        }


        var  actual_completiondate = new Date(actual_completion_date);
        dd = actual_completiondate.getDate();
        mm = actual_completiondate.getMonth()+1; //January is 0!
        yyyy = actual_completiondate.getFullYear();

        if(dd<10) {  dd='0'+dd    }

        if(mm<10) { mm='0'+mm     }
        actual_completiondate = mm+'/'+dd+'/'+yyyy;
        //Check Set Date if Greater Than Today
        if(Date.parse(actual_completiondate) > Date.parse(today))
        {
            alert("Date of Actual Completion Can not be Greater Than Today ");
            return;
        }

        //Convert Date Contract SIgned from Date Signed Data
        if(Date.parse(actual_completion_date) < Date.parse(date_contract_signed ))
        {
            alert("Date of Actual Completion can not be Less than Contract Signed Date "+date_contract_signed);
            return;
        }

    }





    // Compare Call Off Orders Date  Not Greater than Date Contract Signed
    var date_of_callofforders = new Date(date_of_calloff_orders);
    dd = date_of_callofforders.getDate();
    mm = date_of_callofforders.getMonth()+1; //January is 0!
    yyyy = date_of_callofforders.getFullYear();

    if(dd<10) {  dd='0'+dd    }

    if(mm<10) { mm='0'+mm     }
    date_of_callofforders = mm+'/'+dd+'/'+yyyy;

    if(Date.parse(date_of_callofforders) < Date.parse(date_contract_signed)){
        alert("Date of Call Off Orders can not be Less than Contract Signed Date "+date_contract_signed);
        return;
    }


    // Planned Date of Completiong Can  Not Greater than Date Contract Signed
    var planned_completiondate = new Date(planned_completion_date);
    dd = planned_completiondate.getDate();
    mm = planned_completiondate.getMonth()+1; //January is 0!
    yyyy = planned_completiondate.getFullYear();

    if(dd<10) {  dd='0'+dd    }

    if(mm<10) { mm='0'+mm     }
    planned_completiondate = mm+'/'+dd+'/'+yyyy;

    /*
     if(planned_completiondate < date_contract_signed){
     alert("Planned Date of Completion  can not be Less than Contract Signed Date "+date_contract_signed);
     return;
     }  */








    formdata['call_off_order_no'] = call_off_order_no;
    formdata['subject_of_procurement'] = subject_of_procurement;
    formdata['date_of_calloff_orders'] = date_of_calloff_orders;
    formdata['user_department'] = user_department;
    formdata['contract_value'] = contract_value;
    formdata['planned_completion_date'] = planned_completion_date;
    formdata['actual_completion_date'] = actual_completion_date;
    formdata['contractid'] = contractid;
    formdata['total_actual_payments'] = total_actual_payments;
    formdata['status_of_procurement'] = status_of_procurement;
    formdata['calloff_provider'] = calloff_provider;
    formdata['call_off_id'] = call_off_id;
    console.log(formdata);

    var status = 1;

    //set date of call off orders
    compare_dates.set_planned_completion_date(planned_completion_date);
    // set planned contract  completion date
    compare_dates.set_call_off_order_date(date_of_calloff_orders);
    //set date of Actual Completion
    compare_dates.set_contract_completion_date(actual_completion_date);


    //compare Date of call off orders and Planned Contract Completion Date
    compare_dates.compare_call_off_order_planned_completion_date1();
    status = compare_dates.getStatus();

    if(status == 0)
    {
        return;
    }

    if(status_of_procurement == 'completed')
    {
        compare_dates.compare_call_off_order_planned_completion_date();
    }
    status = compare_dates.getStatus();


    if(status == 0)
    {
        return;
    }


    console.log(formdata);
    //return;
    $("#lightbox_heading").html('Proccessing ... ');
    var url = baseurl()+"contracts/calloutcontracts";




    $.ajax({

        type: "POST",
        url:  url,
        data: formdata,
        success: function(data, textStatus, jqXHR){
            console.log(data);


            if(data == 1)
            {
                $("#lightbox_heading").html("<h4>Record Saved Successfully</h4>");
                $("#call_off_order_no").val('');
                $("#subject_of_procurement").val('');
                $("#date_of_calloff_orders").val('');
                $("#user_department").val('');
                $("#contract_value").val('');
                $("#planned_completion_date").val('');
                $("#actual_completion_date").val('');
                $("#total_actual_payments").val('');
                alert("Record Saved Successfully");
                $(".viewlistcalloff_"+contractid).trigger('click');

            }else
            {
                $("#lightbox_heading").html(data);


                // $("#myModalLabel").html(data);
                console.log(data);
            }

        },
        error:function(data , textStatus, jqXHR)
        {
            console.log('Data Error'+data+textStatus+jqXHR);
            alert(data);
        }
    });





}

</script>

<!-- end -->
<div class="widget">
    <!--  <div class="widget-title">
         <h4><i class="icon-reorder"></i>&nbsp;Awarded Contracts</h4>
             <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
             </span>
     </div> -->

    <div class="tabbable" style="padding-left:30px;" id="tabs-45158">
        <ul class="nav nav-tabs">
            <li class=" <?php if(!empty($level) && ($level == 'active'))  {  echo "active"; } ?> " onClick="javascript:location.href='<?=base_url().'contracts/manage_contracts/level/active/'.(!empty($current_financial_year) ?'financial_year/'.$current_financial_year :'' ); ?>'">
                <a href="<?=base_url().'contracts/manage_contracts/level/active/'.(!empty($current_financial_year) ? 'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class="fa fa-folder-open"> </i> Active    <span class="badge badge-info"><?=$count_contracts;?></span> </a>
          
            </li>
            <li onClick="javascript:location.href='<?=base_url().'contracts/terminated/level/terminated/'.(!empty($current_financial_year) ?'financial_year/'.$current_financial_year :'' ); ?>'"  class="<?php if(!empty($level) && ($level == 'terminated'))  {  echo "active"; } ?>">
                <a href="<?=base_url().'contracts/terminated/level/terminated/'.(!empty($current_financial_year) ?'financial_year/'.$current_financial_year :'' ); ?>" data-toggle="tab"> <i class=" fa fa-folder"> </i>  Terminated </a>
            </li>

            <li>
                <select class="chosen financial_year_selection">
                    <?=get_select_options($financial_years, 'fy', 'label', (!empty($current_financial_year)? $current_financial_year : '' ))?>

                </select>
            </li>

        </ul>
    </div>
    <!-- <div class="widget-title">
  
        <h4><i class="icon-reorder"></i>&nbsp;<a href="<?=base_url().'contracts/terminated'?>">View Terminated Contracts</a></h4>
    </div> -->
    <div class="widget-body" id="results">
        <?php

        if(!empty($contracts_page['page_list'])):

            print '<table class="table tablex  table-striped table-hover">'.
                '<thead>'.
                '<tr>'.
                '<th width="94px"></th>';
            if($this->session->userdata('isadmin') == 'Y')
            {
                print   '<th> Procuring And Diposing Entity </th>';
            }
            print   '<th> Provider </th>';
            print  '<th>Date signed</th>'.
                '<th>Procurement Ref #</th>'.
                '<th>Subject of procurement</th>'.
                '<th>Status</th>'.
                '<th style=" ">Contract Manager </th>'.
                '<th style="text-align:right">Contract amount (UGX)</th>'.

                '<th class="hidden-480">Date added</th>'.
                '</tr>'.
                '</thead>'.
                '</tbody>';
            $stack = array( );
            foreach($contracts_page['page_list'] as $row)
            {

                $haslots = !empty($row['haslots']) ? $row['haslots']  : 'N' ;
                $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON received_lots.receiptid = receipts.receiptid INNER JOIN contracts on contracts.lotid = lots.id   WHERE lots.bid_id = ".$row['bidinvitation_id']." AND receipts.beb='Y'   ")->result_array();

                $lotcounting = $lot_count[0]['nums'];
 


                $bidd = $row['bidinvitation_id'];
                if(!empty($haslots ) && $haslots =='Y' ){
                    if (in_array(  $bidd, $stack))
                        continue;
                    array_push( $stack, $bidd);
                }


                #    print_r($row);
                $edit_str = '';
                $delete_str = '';
                $termintate_str = '';

                if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
                {
                    $delete_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'contracts/delete_contract/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';


                }else
                {
                    if(  $lotcounting <= 0 && $haslots  =='N')
                    {

                        $termintate_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="terminateContract(\''.base_url().'contracts/contract_termination/i/'.encryptValue($row['id']).'\', \'Are you sure you want to Terminate  this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';

                        // $termintate_str = '<a href="'. base_url() .'contracts/contract_termination/i/'.encryptValue($row['id']) .'" title="Click to terminate contract"><i class="fa fa-times-circle"></i></a>';
                        $edit_str = '<a title="Edit contract details" href="'. base_url() .'contracts/contract_award_form/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';

                    }
                }

                $status_str = '';
                $completion_str = '';

                if(!empty($row['actual_completion_date']))
                {

                    // && str_replace('-', '', $row['actual_completion_date'])>0)
                    if(  $lotcounting <= 0 && $haslots  =='N')
                    {
                        $status_str = '<span class="label label-success label-mini">Completed</span>';
                        $completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="fa fa-eye"></i></a>';
                    }
                }
                else
                {

                     if(  $lotcounting <= 0 && $haslots  =='N')
                    {
                         $status_str = '<span class="label label-warning label-mini">Awarded</span>';
                  
                        $completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="fa fa-check"></i></a>';
                    }
                }
                $variations = '';


                if(  $lotcounting <= 0 && $haslots  =='N')
                {
                     //framework

                    if(trim($row['pmethod']) != "Micro Procurement" )
                    {
                         $variations = ' <a class="view_variations" id="view_'.$row['id'].'" data-ref="'. base_url() .'contracts/contract_variation_view/i/'.encryptValue($row['id']) .'" title="Click to view Variations "><i class="fa fa-bars"></i></a> &nbsp; &nbsp; ';
                  
                   
                            if(empty($row['actual_completion_date']) )
                            {
                                $variations .= '<a href="'. base_url() .'contracts/contract_variation_add/i/'.encryptValue($row['id']) .'" title="Click to Add Variations "><i class="fa fa-plus-circle "></i></a> &nbsp; &nbsp;';

                            }

                    }

                }


                $more_actions = '<div class="btn-group" style="font-size:10px">
                                     <a href="#" class="btn btn-primary">more</a><a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><span class="fa fa-caret-down"></span></a>
                                     <ul class="dropdown-menu">
                                         <li><a href="#"><i class="fa fa-times-circle"></i></a></li>
                                         <li class="divider"></li>
                                         <li>'. $completion_str .'</li>
                                     </ul>
                                  </div>';

                print '<tr>'.
                    '<td>';
                if(!empty($lotcounting) && ($lotcounting> 0))
                {
                    print  $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str;

                }else{

                    if($this->session->userdata('isadmin') == 'N')
                        print  $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str.'&nbsp;&nbsp;'.$variations;;
                }
                print ' </td>';

                 if(!empty($lotcounting) && ($lotcounting> 0))
                {
                   print   '<th> - </th>';
                }
                else
                {

                            if($this->session->userdata('isadmin') == 'Y')
                            {
                                print   '<th> '.$row['pdename'].' </th>';
                            }
                            print   '<th>' ;

                            if($row['receiptid'] > 0)
                            {
                                $query = mysql_query("SELECT GROUP_CONCAT(p.providernames) AS providers  FROM providers p  WHERE p.providerid IN    ( SELECT    IF(r.providerid > 0, r.providerid,jv.providers) AS PV   FROM receipts r   LEFT OUTER JOIN joint_venture jv   ON r.joint_venture = jv.jV WHERE r.receiptid = ".$row['receiptid']."  )") or die("MOVER".mysql_errno());
                                print_r(mysql_fetch_array($query)['providers']);
                            }
                            else
                            {
                                print '-';
                            }
                }

                if(!empty($lotcounting) && ($lotcounting> 0))
                {
                   print   '<td> - </td>';
                }
                else
                {
                    print  '<td>'. custom_date_format('d M, Y',$row['date_signed']) .'</td>';
                }

                
                  print   '<td>'. format_to_length($row['procurement_ref_no'], 30);
                # print_r($lot_count[0]['nums']);
                if( $haslots  =='Y')
                {

                    print '<br/> <a href="#" class="view_lots"  id="view_'.$row['bidinvitation_id'].'" data-ref="'. base_url() .'receipts/get_contracts_lots/ "  >View Lots Awarded</a>';
                }
                print '</td>'.
                    '<td>'. format_to_length($row['subject_of_procurement'], 30).'';
                if($row['framework'] == 'Y' )
                {
                    echo '<br/><a href="#" id="'.$row['id'].'"  data-bid="'.$row['bidinvitation_id'].'" data-datesigned="'.$row['date_signed'].'"  class="togglecalloforders"  > Add Call off Order </a> | <a href="#" data-procurement="'.$row['procurement_ref_no'].'"    id="'.$row['id'].'"  data-bid="'.$row['bidinvitation_id'].'"  class="viewlistcalloff viewlistcalloff_'.$row['id'].'" >View Call off Orders </a>  </br/>';
                }

                print '</td>'.
                    '<td>'. $status_str .'</td>'.
                    '<td>'. $row['contract_manager'] .'</td>'.
                    '<td style="text-align:right; font-family:Georgia; font-size:14px">';
               if($lotcounting <= 0 && $haslots  =='N')
                {
                     print_r(addCommas($row['total_price'], 0));               

                        if(!empty($row['final_contract_value']))
                        {                    
                           
                            print  "<br/><span  class='label label-defaultlabel-mini'  href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".number_format($row['final_contract_value'])."&nbsp; </span>";
                           
                        }
                        else
                        {}

             }



                print       '</td>'.
                    '<td>';

                print     custom_date_format('d M, Y', $row['dateadded']) .' by '. format_to_length($row['authorname'], 10) ;

                if(!empty($row['actual_completion_date']))
                {
                    if(  $lotcounting <= 0 && $haslots  =='N')
                    {
                    print  "<br/><span  class='label label-defaultlabel-mini'  href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".custom_date_format('d M, Y',$row['actual_completion_date'])."</span>";
                     }
                }
                else
                {}

                print '</td>'.


                    '</tr>';

            }

            print '</tbody></table>';


            print '<div class="pagination pagination-mini pagination-centered">'.
                pagination($this->session->userdata('search_total_results'), $contracts_page['rows_per_page'], $contracts_page['current_list_page'], base_url().
                    "contracts/manage_contracts/level/active/".(!empty($current_financial_year) ?'financial_year/'.$current_financial_year :'' )."/p/%d")
                .'</div>';
        else:
            print format_notice('WARNING: No contracts have been signed in the system');
        endif;
        ?>
    </div>
</div>




<div class="container-fluid">
    <div class="row">

        <!-- end of callout form -->
        <!-- View List -->
        <div class="modal fade" id="viewlist" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 >CALL OFF ORDERS </h4>


                    </div>
                    <div class=" viewcalloff_body">

                    </div>

                </div>
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="myModal"   role="dialog"  >
            <div class=" " role="">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title" id="myModalLabel" style="text-align:center;">NEW CALL OFF ORDER</h4>
                    </div>
                    <div class="">
                        <div id="tblform" style="display:none;">
                            <form id="call_of_orders"   class="form-horizontal"  style="padding:10px;" >




                                <div class="control-group">
                                    <label class="control-label">Amount :</label>
                                    <div class="controls">

                                        <input type="text" class=" numbercommas input-small" placeholder="Amount" id="callout_amount">
                                        <input type="text" class=" numbercommas input-small" placeholder="exchange rate" id="exchange_rate" style="display:none;">

                                        <select class="input-small  " data-placeholder="Currency " id="currency" name="currency" tabindex="1" onChange="javascript:selectionchange(this.value);">

                                            <?php
                                            $recod = mysql_query("select * from currencies");
                                            while($cur  =  mysql_fetch_array($recod)){
                                                ?>
                                                <option value="<?=$cur['title']; ?>"><?php print_r($cur['title']); ?> </option>
                                                <?php
                                            }
                                            ?>

                                        </select>

                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Detail :</label>
                                    <div class="controls">
                      <textarea class="callout_details" id="callout_details" >
                      </textarea>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Manager :</label>
                                    <div class="controls">
                                        <input type="text" class="manager" id="manager">
                                    </div>
                                </div>

                                <div class="control-group">
                                    <div class="controls">

                                    </div>
                                </div>





                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary add_callof_order">Submit</button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- end of modal -->

    </div>
</div>
