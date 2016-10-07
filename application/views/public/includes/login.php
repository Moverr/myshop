<style type="text/css">
 
    .fmst  input[type="text"], .fmst  input[type="password"] {
        border:1px solid #ddd;
     /*   min-width: 350px; */
    }
</style>
<?php
# print_r($accountid); 
?>
<div class="container fmst">
    <div class="row clearfix">
        <div class="col-md-12 column">
         
            <div class="row clearfix">
             <h3> &nbsp; &nbsp; Login</h3>
                <div class="col-md-6 column" style="border-right:1px solid #ddd;">
                <?php 
                 if(!empty($accountid))
                 {

                    ?>
                     <form role="form"  id="contentlogin_form" name="form1" method="post"  action="<?php echo base_url();?>admin/renewpassword" >
                         <div class="form-group">
                         <input type="hidden" id="accid" value="<?=$accountid; ?>">
                           <div class="alert alert-important renewalert hidden">Enter Email Address</div>                           
            
                             <label for="exampleInputEmail1">New Password :</label>
                                <input type="password" class="form-control" name="newpassword" id="newpassword" size="45" value="<?php if(!empty($formdata['newpassword'])) echo $formdata['newpassword'];?>" required/>
                        </div>

                        <div class="form-group ">
                            <label for="exampleInputPassword1">Re Enter Password : </label>
                            <input type="password" class="form-control" name="reenternewpassword" id="reenternewpassword" size="45" value="<?php if(!empty($formdata['reenternewpassword'])) echo $formdata['reenternewpassword'];?>"  required/>                             
                        </div>
                          <input type="hidden" name="login" value="login" />
                          <button  class="btn btn-default lgnbtn" id="lgnbtn"   value="Login" name="login_btn" type="button" > <i class="fa fa-lock"> </i> RENEW PASSWORD </button>
                          <!-- <button type="submit" class="btn btn-default">Submit</button>  -->
                    </form>
                <?php
                  }
                 else
                 {
                 ?>
                    <form role="form"  id="content_login_form" name="form1" method="post"  action="<?php echo base_url();?>admin/login" >
                        <div class="form-group">
                             <label for="exampleInputEmail1">Username</label>
                                <input type="text" class="form-control" name="acadusername" id="acadusername" size="45" value="<?php if(!empty($formdata['acadusername'])) echo $formdata['acadusername'];?>" required/>
       
                             
                        </div>
                        <div class="form-group ">
                             <label for="exampleInputPassword1">Password</label>
                             <input type="password" class="form-control" name="acadpassword" id="acadpassword" size="45" value="" required/>
                             
                        </div>
                          <input type="hidden" name="login" value="login" />
                        <button  class="btn btn-default"   value="Login" name="login_btn" type="submit" > <i class="fa fa-lock"> </i> LOGIN </button>
                          <!-- <button type="submit" class="btn btn-default">Submit</button>  -->
                    </form>
                    <?php
                     }
                    ?>
                </div>
                <div class="col-md-6 column">
                  <div class="row col-md-12 ">
                 <b>To retrieve your lost/forgotten password, send us your email address</b>
                  </div>
                  
                   <div class="row"> 

                    
                    <form class="navbar-form navbar-left" role="search"> 
                    <div class="alert alert-important emailalert hidden">Enter Email Address</div>                           
                    <div class="form-group">
                            <input type="text" placeholder="Enter Email Address " class="form-control col-md-8 email_reminder"  id="email_reminder" />
                        </div> <button type="button" class="btn btn-default sendemail"><i class="fa fa-envelope"> </i> RETRIEVE PASSWORD </button>
                        
                        </form>

                    </div>

<script type="text/javascript">
    $(function(){
        $(".lgnbtn").click(function(){
            var nwpassword = $("#newpassword").val();
            var reenternewpassword = $("#reenternewpassword").val();
            var accid = $("#accid").val();
            $(".renewalert").addClass('hidden');

            if((nwpassword.length > 0) || (reenternewpassword.length > 0)){
               
               if(nwpassword != reenternewpassword)
               {
                  $(".renewalert").removeClass('hidden');
                  $(".renewalert").html("Paswords dont match "); return;
               }
             var  formdata = {};
            formdata['newpassword'] = nwpassword;
            formdata['accid'] = accid;
            var url = "<?=base_url().'admin/updatepassword'; ?>";
                console.log(formdata);
                 $(".renewalert").removeClass('hidden');
                 $(".renewalert").html("Proccessing .... ");
                    $.ajax({
                        type: "POST",
                        url:  url,
                        data:formdata,
                        success: function(data, textStatus, jqXHR){
                          console.log(data); 
                          if(data == 1)
                          {
                             $(".renewalert").removeClass('hidden');
                             $(".renewalert").html("Account Password has been reactivated, check email account for details ");
                             $("#newpassword").val('');
                             $("#reenternewpassword").val('');
                            // location.href="<?=base_url().'admin/login'; ?>"
                          }
                          else
                          {

                          }                         

                        },
                        error:function(data , textStatus, jqXHR)
                        {
                            console.log("NETWORK ERROR <br/>"+data);
                             $(".renewalert").removeClass('hidden');
                             $(".renewalert").html("Something Went Wrong Contact Administrator");
                        }
                    });

               
            }
            else
            {
                $(".renewalert").removeClass('hidden');
                $(".renewalert").html("Fill Blanks");
                return;
            }

 
        });

        $(".sendemail").click(function(){
            var email = $("#email_reminder").val();
             $(".emailalert").addClass('hidden');
            if(email.length > 1)
            {
                if( !validateEmail(email)) {
                     $(".emailalert").removeClass('hidden');
                     $(".emailalert").html("Please Check Your Email Address ");
                     return;
                }
                     $(".emailalert").removeClass('hidden');
                     $(".emailalert").html("Proccessing .... ");
                // starting sending email account
                url = "<?=base_url(); ?>admin/forgotpassword";
                var formdata = {};
                formdata['emailaddress'] = email;
                console.log(formdata);

                $.ajax({
                        type: "POST",
                        url:  url,
                        data:formdata,
                        success: function(data, textStatus, jqXHR){
                          console.log(data);
                          if(data == 1)
                          {
                              $(".emailalert").html("A PASSWORD RECOVERY EMAIL HAS BEEN SENT TO YOUR EMAIL ADDRESS");
                          }
                          else if(data == 0){
                             $(".emailalert").html("EMAIL ADDRESS DOES NOT EXIST ");
                          }
                          else
                          {
                            $(".emailalert").html("REPORT INCIDENT TO WEB ADMINISTRATOR");
                          }
                        

                        },
                        error:function(data , textStatus, jqXHR)
                        {
                            console.log("NETWORK ERROR <br/>"+data);
                        }
                    });


                // end of email account


            }
            else
            {
                $(".emailalert").removeClass('hidden');
                $(".emailalert").html("Enter Email Address ");
            }
          
        });

        function validateEmail($email) {
          var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
          return emailReg.test( $email );
        }

    })
</script>
                <!--
                    <button type="submit" class="btn btn-default">Submit</button>
                    -->
                     
                </div>
            </div>
        </div>
    </div>
</div>