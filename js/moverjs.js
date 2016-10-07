
//Get the Details about the Transac
$(".stock_information").on("change",function(){
     
     var item_id = $(this).val();
     console.log(item_id);
    
     var url = baseurl()+"stock/get_stock_ajax/item/"+item_id;

     console.log(url);
    /*
    // alert(url); return false;
    var formdata = {};
    formdata['receiptid'] = receiptid;
    formdata['bidid'] = bidi;
    formdata['lotid'] = lot_id;

    $("#unbidderlist").html("proccessing ...");
    $.ajax({
        type: "POST",
        url:  url,
        data:formdata,
        success: function(data, textStatus, jqXHR){
            console.log(data);
            $("#unbidderlist").html(data);

        },
        error:function(data , textStatus, jqXHR)
        {
            alert(data);
        }
    });*/



})