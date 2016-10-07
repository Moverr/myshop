	<style type="text/css">
	  .ui-menu
	  {
	    background: #eee; z-index: 999; height:100px; overflow-y: scroll; width:150px;
	  }

	  .ui-menu .ui-menu-item a{
	      height:14px;
	      font-size:13px;
	  }

	  .prviders{padding:10px; display:none;}

	  .stx{
	    margin:5px; font-size:14px; padding:5px; border-bottom:1px solid #000;    height:30px; min-width:10px; float:left;}
	    .delme{color:#fff; text-decoration:none;}
	    .delme,.badge{cursor:pointer;}


	  </style>

	<script>
	//$(function(){ $("select").chosen({ width: '350px' });}) */
	function removeata(st){
	  // alert(st);

	 delete fomdata[st];
	 console.log(fomdata);
	 xx = 0;
	 xc =0;
	     $.each(fomdata,function(key,value){
	       xc ++;
	       if(value == st)
	       {
		 delete fomdata[key];
		 xc  = xc -1;
	       }

	       });

	      if(xc >0)
	      {
		st = "<h4> JOINT VENTURE  </h4> ";
		st +=  " Provider Lead ";
		st += "&nbsp; &nbsp; <select class\"provider_lead\" onChange=\"javascript:providerlead(this.value);\"> <option value='0'> select provider </option>";
		xx = 0;
		$.each(fomdata,function(value,key){
		  xx ++;
		  if(xx % 2 !=0)
		   {
		     st += "<option value='"+value+"'>"+key+"</option>";
		    }
		   else
		   {
		     st += "<option value='"+value+"'>"+key+"</option>";
		   }

		  });



		st +=  "</select><hr/>";

	     $.each(fomdata,function(value,key){
	       xx ++;
	       if(xx % 2 !=0)
		{ st += "<div class=\"stx\">"+key+"   &nbsp;<span id=\""+key+"\" class=\"badge badge badge-important\" onClick='javascript:removeata(this.id)'><a class=\"delme\" >X </a></span></div> &nbsp; "; }
		else
		{st +="<div class=\"stx\">"+key+" &nbsp;<span class=\"badge badge badge-important\" id=\""+key+"\" onClick='javascript:removeata(this.id)'><a class=\"delme\">X </a></span></div> &nbsp; ";}

	     console.log("_____"+key);
	       // st+="<tr><td>"+;
	       });


	      st +="";
	      $(".prviders").html(st).fadeIn('slow');
	       console.log(st);
		 data_v ++;


	      }
	      else
	      {
		  $(".prviders").html("").fadeOut('slow');
	      }
	  
	  }


	//Get the Provider Lead 
	  var pr = 0;
	  function providerlead(st){
	    pr = st;
	  }

	   
	  $(function(){

	  	// Delete Readout Price 
	    delet_readout  = function(st)
	    {         
	      var cnfm = confirm("Confirm Delete");
	      if(cnfm ==  false)
		return;

	      var currency = readdata["currency_"+st];
	      var echangerate = readdata["readoutprice_"+st];
	      var readoutprice = readdata["exchangerate_"+st];

		for(var key in readdata)
		{
		    if((readdata[key]==currency) || (readdata[key]==echangerate) ||(readdata[key]==readoutprice) )
		    {
		       delete readdata[key];
		    }
		        
		}
		var item = currency;
		  while (currencies_added.indexOf(item) > -1) {
		   currencies_added.splice(currencies_added.indexOf(item), 1);
		  }

	      console.log("RESULTS ::::: <br/> ");
	      console.log(readdata);
	      console.log("RESULTS ::::: <br/> ");
	      console.log(currencies_added); 

	      $("#xx_"+st).fadeOut('slow');

	    }


	    lots = 0;
		inxt = 0;
		$(document).on('change','.currency',function(){
		   inxt = $(this).prop("selectedIndex");
		  if(inxt > 0)
		  {
		     $("#exchangerate").fadeIn('fast');
		  }
		  else
		  {
		    $("#exchangerate").fadeOut('fast');
		  }

		});


	    // determine on whether or not to show lots
	    $(document).on('click','.showhidelots',function(){

	    if($(this).is(':checked')){
	    //  alert('Checked');
	    $(".multiplelots").removeClass('hidden');
	    lots = 1;
	    }
	    else
	    {
	    // alert('Unchecked');
	    $(".multiplelots").addClass('hidden');
	    lots = 0;
	    }
	    })


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

//	$( ".date-picker" ).datepicker();

	  $('.date-picker').datepicker({ dateFormat: 'dd/mm/yyyy' });
	   // alert('MOE');
	    // $( ".datepicker" ).datepicker();
	   // MANAGE DELETE RESORE AND UPDATE FUC
	$('.savedelreceipt').on('click', function(){


	    var decider = this.id;
	    var idq =  decider.split('_');

	     switch(idq[0])
	     {
		case 'savedelreceipt':
		url = baseurl()+'receipts/delreceipts_ajax/del/'+idq[1];
		var b = confirm('You Are About to Delete a Record')
		if(b == true){
		 var rslt = ajdelete(url,decider);
		}
		break;
		case 'restore':
		url = baseurl()+'receipts/delreceipts_ajax/archive/'+idq[1];
		var b = confirm('You Are About to Restore a Record')
		if(b == true){
		 var rslt = ajdelete(url,decider);
		}
		break;
		case 'del':
		url = baseurl()+'receipts/delreceipts_ajax/del/'+idq[1];
		var b = confirm('You Are About to Paramanently Delete a Record')
		if(b == true){
		 var rslt = ajdelete(url,decider);
		}
		break;
		default:

		break;
	     }

	});

	ajdelete = function(url,ids){
	    console.log(url);
	     $.ajax({
		type: "GET",
		url:  url,
		success: function(data, textStatus, jqXHR){
	  // alert(data);
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



	   $(".addjv").fadeOut('fast');
	    checker = 0;
	    var data_v = 0;
	      fomdata = {};
	      jv =  Array();
	   $(".checker").click(function(){

	     fomdata = {};
	     if($(this).is(":checked"))
	     {
	       checker = 1;
	    //   $(".prviders").fadeIn('fast');
	       $(".addjv").fadeIn('fast');

	     }
	     else
	     {
	       checker = 0;
		$(".addjv").fadeOut('fast');
		$(".prviders").html('');
		$(".prviders").fadeOut('fast');
	     }
	     });

	$(".addjv").click(function(){
	      // alert('insider');

		if(xs == 0){
		alertmsg("The Service Provider Was Suspended ");
		return;
		}


		var dataid = this.id;
		var dataelements =   $("#"+dataid).attr('data-elements');

		 if(dataelements.length > 0)
		  {
		        var fieldNameArr=dataelements.split("<>");
		  }
		   else
		  {
		        fieldNameArr = Array();
		  }

		console.log(dataelements);
		if((dataelements!= ' ') &&(dataelements.length > 0))
		    {
		        for(var i=0;i<fieldNameArr.length;i++)
		         {
		            //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED
		             var lke = fieldNameArr[i].split("*");
		              elementfield = lke[1];
		              formvalue = $("#"+elementfield).val();
		                 if(fieldNameArr[i].charAt(0)=="*"){
		                  if(formvalue.length <= 0)
		                  {
		                  alertmsg('Fill Blanks');
		                   return false;
		                  }
		              }

		                else
		                {
		                    elementfield = fieldNameArr[i];
		                    formvalue = $("#"+elementfield).val();
		                }

		               fomdata[elementfield+'_'+data_v] =formvalue;
		               $("#"+elementfield).val('');

		 }

	      }
	   
	    xx = 0;
	
	    st = "<h4> JOINT VENTURE  </h4>  ";
	    st +=  " Provider Lead";
	    st += "&nbsp; &nbsp; <select clas=\"provider_lead\" onChange=\"javascript:providerlead(this.value);\"><option value='0'> select provider </option>";
	    xx = 0;
	    $.each(fomdata,function(value,key){
	      xx ++;
	      if(xx % 2 !=0)
	       {
		 st += "<option value='"+key+"'>"+key+"</option>";
		}
	       else
	       {
		 st += "<option value='"+key+"'>"+key+"</option>";
	       }

	      });



	    st +=  "</select><hr/>";
	    xx = 0;
	     $.each(fomdata,function(value,key){
	       xx ++;
	       if(xx % 2 !=0)
		{ st += "<div class=\"stx\">"+key+"        &nbsp;<span id=\""+key+"\" class=\"badge badge badge-important\" onClick='javascript:removeata(this.id)'><a class=\"delme\" >X </a></span></div> &nbsp; "; }
		else
		{st +="<div class=\"stx\">"+key+"     &nbsp;<span class=\"badge badge badge-important\" id=\""+key+"\" onClick='javascript:removeata(this.id)'><a class=\"delme\">X </a></span></div> &nbsp; ";}

	     console.log("_____"+key);
	       // st+="<tr><td>"+;
	       });


	      st +="";
	      $(".prviders").html(st).fadeIn('slow');
	       console.log(st);
		 data_v ++;
	       return ;

	    });


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




	  //  });
	    checkser = 0;
	    var datas_v = 0;
	      readdata = {};
	      redit_jv =  Array();
	      currencies_added = Array();
	      
	    $("#readoutprice_add").click(function(){
		count_me = 0;
	//alert("eeekareaea");
	      checkser  = 1;
	      var dataid = this.id;
	      var dataelements =   $("#"+dataid).attr('data-elements');

	      if(dataelements.length > 0)
	      {
	       var fieldNameArr=dataelements.split("<>");
	      }
	      else
	      {
	       fieldNameArr = Array();
	      }

	       console.log(dataelements);
	       if((dataelements!= ' ') &&(dataelements.length > 0))
		    {
		        for(var i=0;i<fieldNameArr.length;i++)
		         {
		            //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED
		             var lke = fieldNameArr[i].split("*");
		              elementfield = lke[1];
		             // alert(elementfield);

		                 formvalue = elementfield =='readoutprice'?  $("#"+elementfield).val()  : $("#"+elementfield).val();
		                 if((elementfield == "readoutprice")){
		                   if(formvalue.length > 3)
		                   {
		                      formvalue = numonly(formvalue);
		                      //alert('Inputt');
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
		                     console.log("else bit "+elementfield);

		                    formvalue = $("#"+elementfield).val();


			             if((elementfield == "exchangerate")){
				                    if(formvalue.length > 3)
				                  {
				                    formvalue = numonly(formvalue);
				                  //  alert('Inputt');
				                  }
			                }



		                   
		                }

	     readdata[elementfield+'_'+datas_v] =formvalue;

	      $("#"+elementfield).val('');

		 }



	      }



	    //redit_jv.push(readdata);
	     console.log(readdata);
	    xx = 0;


	      st = " <table class='table table-striped searchable '><tr><th></th><th> Currency </th><th> Amount </th> <th> Exchange Rate </th> </tr>";
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
	       console.log(st);
	       data_v= 0;
		 $("#exchangerate").fadeOut('fast');
	       return ;

	       });

	 //bid receipt
	 function isNumber(input){
	    var RE = /^-{0,1}\d*\.{0,1}\d+$/;
	    return (RE.test(input));
	}

	 $( "#bidreceipt" ).submit(function(e) {
	       e.preventDefault();

		if(xs == 0){
		alertmsg("The Service Provider Was Suspended ");
		return;
		}


	       var form = this;
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
	       console.log(dataelements);
		 // alert(dataelements);
		 // return false;


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
		        for(var i=0;i<fieldNameArr.length;i++)
		         {

		            //CHECK TO SEE IF ELEMEMENTS ARE REQUIRED
		             var lke = fieldNameArr[i].split("*");

			 
		 
				elementfield = lke[1];
				
			//	if(elementfield == "undefined" || elementfield == undefined  || elementfield === undefined )
				//continue;
				
				console.log("ELEMENT : "+elementfield+"<br/> checker "+checkser);
				
			   if((checker == 1) && (elementfield === "serviceprovider") )
			   continue;
				if((checkser == 1) && (elementfield === "readoutprice") )
			   continue;
			   if((checkser == 1) && (elementfield === "currency") )
			   continue;
			   if((inxt == 0) && (elementfield === "exchangerate") )
			   continue; 
	  
		  
		   if(elementfield == 'ifbslot')
		   {
		     if($("#"+elementfield).val() <= 0 ){
		       alertmsg("Select Lot");
		       $("#"+elementfield).css('border', 'solid 3px #FFE79B');
		       return false;
		     }else
		     {
		       lots = 1;
		       $("#"+elementfield).css('border', 'solid 1px #eee');
		     }
		   }


		  formvalue = $("#"+elementfield).val();
		  if(fieldNameArr[i].charAt(0)=="*"){
		  if(formvalue.length <= 0)
		   {
			   if((checker == 1) && (elementfield === "serviceprovider") )
			   continue;
				if((checkser == 1) && (elementfield === "readoutprice") )
			   continue;
			   if((checkser == 1) && (elementfield === "currency") )
			   continue;
			   if((inxt == 0) && (elementfield === "exchangerate") )
			   continue; 
			   
		   alertmsg('Fill Blanks  &nbsp; '+elementfield); return false;
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
		    alertmsg('Invalid Web Url ');
		    return false;
		                }

		                }
		                break;
		                case 'email':

		                if(formvalue.length > 0)
		                {
		                    var valu = validateemail(formvalue);
		                    if(valu == false)
		                    {

		      alertmsg('Ivalid Email Address');
		      return false;
		                    }


		                }
		                break;
		                case 'phone':
		                if(formvalue.length > 0)
		                {
		                var valu = validatephone(formvalue);
		                if(valu == false)
		                {
		      alertmsg('Invalid Phone, Digits are allowed, Not More than 10 digits');
		      return false;
		                }

		                }
		                break;
		     case 'money':
		      if(formvalue.length > 0)
		                {
		                //alert(fieldNameArr[i]);
		                var nm =  formvalue;
		                if(nm.length >3)
		                {
		                 nm  = numonly(formvalue);
		                }
		                var valu = isNumber(nm);
		               //alert('mover');
		                if(valu == false)
		                {
		      alertmsg('Invalid Entry, Enter Digits');
		      $("#"+elementfield).css('border', 'solid 3px #FFE79B');
		      return false;
		                }
		                formvalue = numonly(formvalue);

		    }

		    break;

		    break;
		  default:
		  break;
		    }

		   // ADDING ELEMETNTS TO THE FORM
		    formdata[elementfield] =formvalue;
		    }
		    console.log(formdata);
		    }


		    if((checker == 1))
		    {
		      formdata['jv'] = fomdata;
		      //adding provider lead
		      formdata['pr'] = pr;
		      console.log(formdata);
		    }

		    if((checkser == 1))
		    {
		      formdata['pricing'] = readdata;
		      console.log(formdata);
		    }

	    /*
	    check to see if there are lots
	    */
	    formdata['lots'] = lots;
	    /*
	    end check of lots
	    */
		
		

	  //  alert(lots);
	  //  return false;


		   // alert('Pass');
		  // return false;
	console.log(url);
	     //return;
	    //return false;
	   //send to server
	    alertmsg('Proccessing ... ');
	    $.ajax({
		type: "POST",
		url:  url,
		data: formdata,
		success: function(data, textStatus, jqXHR){
		  //alert(data);

		  console.log("response");
		  
		  console.log(data);

	      if(data == 1)
	      {
	      alertmsg('Record Saved Succesfully');
	      url = baseurl()+'receipts/add_receipt';
	      $.ajax({
		type: "POST",
		url:  url,
		data: formdata,
		success: function(data, textStatus, jqXHR){
		     $(".level_fetcher > .receiptsp").html(data);

		},
		error:function(data , textStatus, jqXHR)
		{
		    console.log('Data Error'+data+textStatus+jqXHR);
		    alert(data);
		}
	     });
	     //location.href=baseurl()+"receipts/manage_receipts";
	    }

	    else{
		    var dct = data.split(":");

		    if(dct[0] == 3)
		    {
		      alertmsg(dct[1]);
		    }else
		    alertmsg('Something Went Wrong Contact Site Administrator ');
		  }

		    console.log(data);   return false;


		},
		error:function(data , textStatus, jqXHR)
		{
		    console.log('Data Error'+data+textStatus+jqXHR);
		    alert(data);
		}
	    });


	});


	function isNumber(input){
	    var RE = /^-{0,1}\d*\.{0,1}\d+$/;
	    return (RE.test(input));
	}

	    });

	       function kep(st) {

		    var rex = new RegExp(st, 'i');
		    $('.searchable   .stx').hide();
		    $('.searchable   .stx').filter(function () {
		        return rex.test($(this).text());
		    }).show();

		}



	  </script>

	  <script type="text/javascript">
	  $(function(){
	       var prlist = [];
	     var availableTags = [
	      "ActionScript",
	      "AppleScript",
	      "Asp",
	      "BASIC",
	      "C",
	      "C++",
	      "Clojure",
	      "COBOL",
	      "ColdFusion",
	      "Erlang",
	      "Fortran",
	      "Groovy",
	      "Haskell",
	      "Java",
	      "JavaScript",
	      "Lisp",
	      "Perl",
	      "PHP",
	      "Python",
	      "Ruby",
	      "Scala",
	      "Scheme"
	    ];
	    $( "#serviceprovider" ).autocomplete({
		    source: availableTags
		 });

		$("#serviceprovider").on('change',function(){
		var provder =this.value;
		var idd = this.id;

		//check ROP for the servce provider  ::
	    var dataurl = $("#"+idd).attr('data-url');
	   var datachecks = $("#"+idd).attr('data-cheks');

	  formdata = {};
	  formdata[datachecks] = provder;
	  console.log(formdata);
	  console.log(dataurl);
	   $.ajax({
		            type: "POST",
		            url:  baseurl()+'receipts/fetchproviders',

		            success: function(data, textStatus, jqXHR){
		             var fetched = data.split("<>");

		             for (var i = 0; i < fetched.length; i++) {
		            prlist[i] = fetched[i];
		             };

		              $( "#serviceprovider" ).autocomplete({
		                  source: prlist
		                });
		            },
		            error:function(data , textStatus, jqXHR)
		            {
		                console.log("Error Loading the Pre List");
		            }
		       });

	  });

	      //ropproviders
	   var prlist = [];
	     var availableTags = [
	      "ActionScript",
	      "AppleScript",
	      "Asp",
	      "BASIC",
	      "C",
	      "C++",
	      "Clojure",
	      "COBOL",
	      "ColdFusion",
	      "Erlang",
	      "Fortran",
	      "Groovy",
	      "Haskell",
	      "Java",
	      "JavaScript",
	      "Lisp",
	      "Perl",
	      "PHP",
	      "Python",
	      "Ruby",
	      "Scala",
	      "Scheme"
	    ];
	    $( "#serviceprovider" ).autocomplete({
		    source: availableTags
		 });
	   function fetchproviders(fetched)
	   {

	    console.log(fetched);
	   idx = 0;
	  for(var index in fetched) {
	    //alert( index + " : " + fetched[index] + "<br />"); break;
	     prlist[idx] = fetched[index];
	     idx ++;
	    }
	    console.log(prlist);

	       $( "#serviceprovider" ).autocomplete({
		    source: prlist
		 });
	   }
	  fetchproviders(<?=$ropproviders; ?>);

	  });
	  </script>



	   <?php
	  # created by  mover
	  #included into the play : so it would be nice if its plugged in Ajaxly
	  /*
	  Check if Pde name Exists in the Db..   server side checks
	  */



	     $ref_no =  '';
	       $serviceprovider ='';
		$pdecategory = '';
		 $nationality =  '';
		  $datesubmitted =  '';
		   $receivedby =  '';
		    $approved = 'Y';

		          $receiptid = 0;


		          #exit();
	  if((!empty($receiptinf2o)) && (count($receiptin2o) > 0) )
	  {
	       #  print_r($receiptinfo);
	     $ref_no =  '';
	     $serviceprovider = $receiptinfo['providernames'];

		$pdecategory = '';
		 $nationality =  $receiptinfo['nationality'];
		  $datesubmitted =  '';
		   $receivedby =  $receiptinfo['received_by'];
		    $approved =  $receiptinfo['approved'];

		      $receiptid = $receiptinfo['receiptid'];


	  }

	  $i = 'insert';
	  if(!empty($formtype))
	  {

	     switch($formtype)
	     {
		case 'edit':
		$i  = 'update/'.$receiptid;
		break;
	     }

	  }


	  ?>



	  <div class="widget-body">


	    <!-- start -->

	  <div class=" row-fluid span12">
	    <?php
	    $varible = (!empty($lots)) ? '<>*ifbslot' : '' ;

		#print_r($rowa[0]['invitation_to_bid_date']);
		
		//dealing with framework Entries 
		if(isset($rowa[0]['framework']) && $rowa[0]['framework'] == 'Y')
		{
 	      $varible .= '<>readoutprice<>exchangerate<>currency';
		}
		else
		{
 		$varible .= '<>*readoutprice<>exchangerate<>*currency';
		}

		
		//adding bidding period dates
		$varible .= '<>invitation_to_bid_date<>bid_submission_deadline';

	      ?>
	  <form action="#" class="form-horizontal" id="bidreceipt" name="bidreceipt"  data-type="newrecord" 
	       data-cheks="pdename<>pdecode" 
		   data-check-action="<?=base_url();?>receipts/save_bidreceipt<?='/'.$i; ?>"
		   data-action="<?=base_url();?>receipts/save_bidreceipt<?= '/'.$i; ?>" 
		   data-elements="*datesubmitted<>*receivedby<>*serviceprovider<>*nationality<>*procurementrefno<>*bidid<?=$varible; ?>" >
		     <div class="span12">

			     <div class="row-fluid">

				     <div class="control-group">
							       <label class=" control-label"> Procurement Ref No </label>
							       <div class="controls">  <label class="span8" >  <?=$procurement_ref_no; ?>
							       <input type="hidden" value="<?=base64_encode($procurement_ref_no); ?>" class="procrefno" id="procrefno" name="procrefno" />
							       <input type="hidden" value="<?=$procurement_ref_no; ?>" class="procurementrefno" id="procurementrefno" name="procurementrefno" />
							       <input type="hidden" value="<?=$bidid; ?>" class="bidid" id="bidid" name="bidid" />
							       </label>
				                   </div>
			        </div>
		        </div>

		    <!-- DOES IT HAVE LOTS -->
		    <?php
		#    print_r($lots);

		    if(!empty($lots))
		    {
		    ?>
		    <!-- Lot Iinformation -->
		    <div class="row-fluid">

			    <div class="control-group">
					      <label class=" control-label" >
					      Select Lot
					      </label>
					      <div class="controls">
					       
							        <select class="span4 ifbslot" id="ifbslot" name="ifbslot" dataref="selecc">
									        <option value="0"> Select  </option>
									        <?php
									        foreach ($lots as   $record) {
									        ?>
									        <option value="<?php  print_r($record['id']); ?>" >

									          <?=$record['lot_title']; ?> </option>
									        <?php
									        }
									        ?>

							        </select>
					      </div>
			   </div>

		 </div>
		  <?php } ?>


 
		    <!-- Joint Venture -->
	    <div class="control-group">
	    	 <label class=" control-label">is it a Joint Venture  ? </label>  
			    <div class="controls"> 
			    <label><input type="checkbox" class="checker" value="jv" />
			    </label>
			    </div>
	    </div>


	    	<!-- Service Provider --> 
		     <div class="row-fluid">
				     <div class="control-group">
				     <label class=" control-label" >Add Service Provider</label>


				      <div class="controls ">
					        <div class="input-append span6">
					        <!-- onBlur="javascript:chckprovider(this.value);" -->
							     <input type='text' data-url="<?=base_url().'receipts/searchprovider/'.$i;?>" data-cheks="providernames" class="span8 serviceprovider" value="<?=$serviceprovider; ?>" name="serviceprovider" id="serviceprovider" onFocusOut="javascript:chckprovider(this.value,<?=$bidid; ?>);"  onBlur="javascript:chckprovider(this.value,<?=$bidid; ?>);"  />
							    
							     <button type="button" name="save" value="save"  id="addprovider" data-elements="*serviceprovider" class="btn blue addjv"  > Add Provider</button>		     
					     </div>
				      </div>

				      </div>
		    </div>
		     <br/>

		     <!-- Providers --> 
		    <div class="row-fluid">
			     <div class="control-group prviders span6" style="display:none;">
			     </div>
		  </div>

			<!-- Country of Registration --> 
		     <div class="row-fluid">
			      <div class="control-group">		      

			      <label class=" control-label">Country of Registration</label>
				       <div class="controls">
						     <select  class="span4 chosen nationality" data-placeholder="Nationality" tabindex="1" id="nationality" name="nationality">		    
								     <?php
								     foreach ($countrylist as $key => $value) {
								       # code...
								      ?>
								       <option <?php   if($nationality == $value['country_name']) {  echo  'selected'; } ?> > <?=$value['country_name'];  ?> </option>

								      <?php
								     }

								     ?> 

						     </select>
				     </div>

			   </div>
		  </div>




		      <div class="row-fluid">
		      <div class="control-group">
		      <label class="control-label">Readout Price</label>

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

		       <input type="text" id="readoutprice"  placeholder="Readout Price" style="margin-left:5px;" datatype="money" name="readoutprice" datatye="money"  class="readoutprice input-medium numbercommas"        />
		       <input type="text" id="exchangerate"  placeholder="Exchange Rate" style="margin-left:5px;display:none" datatype="money" name="exchangerate" datatye="money"  class="readoutprice input-medium numbercommas "  />

	 
	    
		      <button type="button" name="save" value="save"   id="readoutprice_add" data-elements="*readoutprice<>*currency<>exchangerate" class="btn blue readoutprice_add"  > Add </button> 
	     
		      </div>

				<br/>

				      <div class="controls price_Currency" style="width:50%;">
				       <div class="alert alert-info">
				       <button data-dismiss="alert" class="close">×</button>
				            No Readout prices entered yet.
				            To add  multiple readout price, select the appropriate currency, enter the amount and click 'Add amount' to add the contract price
				        </div>
				       </div>
		   </div>
		   </div>



		    
		    </div>



	  <br/>

	<!-- Date Submitted --> 
	       <div class="row-fluid">
		      <div class="control-group">
				    <input class=" invitation_to_bid_date"   type="hidden" value="<?=!empty($rowa[0]['invitation_to_bid_date']) ? custom_date_format('d-m-Y',$rowa[0]['invitation_to_bid_date']) : '' ; ?>" id="invitation_to_bid_date"  name="invitation_to_bid_date" />
	
				<input class="   bid_submission_deadline"   type="hidden" value="<?=!empty($rowa[0]['bid_submission_deadline']) ? custom_date_format('d-m-Y',$rowa[0]['bid_submission_deadline']) : '' ; ?>" id="bid_submission_deadline"  name="bid_submission_deadline" />
				
				  
		      <label class="control-label">Date Submitted</label>
		       <div class="controls">
		           <div class="input-append date date-picker span4" data-date="<?=Date('d/m/Y'); ?>" data-date-format="dd/mm/yyyy" data-date-viewmode="years">
		           <input  data-date-format="dd/mm/yyyy" data-date-viewmode="days"  class=" m-ctrl-medium date-picker  span10 datesubmitted"   type="text" value="<?=Date('d/m/Y') ?>" id="datesubmitted"  name="datesubmitted" /><span class="add-on"><i class="fa fa-calendar"></i></span>
				   </div>
				</div>
		    </div>

		</div>

	  <br/>


	  		<!-- Name of Receiver --> 
		    <div class="row-fluid">
				    <div class="control-group">
				        <label class="control-label">Name of Receiver</label>
				        <div class="controls">
				        <input type='text' class="span4 receivedby" id="receivedby" name="receivedby" value="<?=$receivedby; ?>" dataref="money"  />
				        </div>
				     </div>
		     </div>
		     

		     <!-- Buttons -->
		      <div class="row-fluid"> 
		       <div class="control-group">
		         <div class="controls">
				      <button type="submit" name="save" value="save" class="btn blue">Save</button>				        
				      <button type="reset" name="cancel" value="cancel" class="btn">Clear Form</button>
		      </div>
		      </div>
		     </div>


		     </div>
	    </form>
	    </div>


	  </div>

	  <?php
	  $data['feed'] = 'receipt';
	  $this->load->view('receipts/manage_receipts_v',$data);
	  ?>

	  <style>
	  .tbst{width:100%; }
	  .tbst tr{ padding}
	  </style>
