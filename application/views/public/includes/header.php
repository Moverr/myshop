<?php
ob_start();
?>
<?php if(empty($requiredfields)) $requiredfields = array();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?=isset($pagetitle)?$pagetitle : 'MY SHOP V1 '?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="My Shop Verson 1 ">
    <meta name="author" content="">

    <!--link rel="stylesheet/less" href="less/bootstrap.less" type="text/css" /-->
    <!--link rel="stylesheet/less" href="less/responsive.less" type="text/css" /-->
    <!--script src="js/less-1.3.3.min.js"></script-->
    <!--append '#!watch' to the browser URL, then refresh the page. -->

    <link href="<?=base_url()?>css/bootstrap.min.css" rel="stylesheet">
    <!-- font awesome -->
    <link href="<?=base_url()?>font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="<?=base_url()?>css/style_front.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300|Lato:400,900' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="<?=base_url()?>js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon">

    <script type="text/javascript" src="<?=base_url()?>js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>assets/bootstrap/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="<?=base_url()?>js/scripts_front.js"></script>
    <!-- chosen files -->
    <script src="http://harvesthq.github.io/chosen/chosen.jquery.js"></script>
    <link rel="stylesheet" href="http://harvesthq.github.io/chosen/chosen.css" />

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/tableExport.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jquery.base64.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/html2canvas.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/sprintf.js"></script>

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.js"></script>

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.standard_fonts_metrics.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.split_text_to_size.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.from_html.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/FileSaver.min.js"></script>

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.addhtml.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.addimage.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.annotations.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.autoprint.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.cell.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.context2d.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.javascript.js"></script>

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.outline.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.png_support.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.sillysvgrenderer.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.split_text_to_size.js"></script>

    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.plugin.total_pages.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.PLUGINTEMPLATE.js"></script>

    <script>

        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({ allow_single_deselect: true });
        });
    </script>
    <script>
        function demoFromHTML() {
            var doc = new jsPDF('p', 'in', 'letter');
            var source = $('#testcase').first();
            var specialElementHandlers = {
                '#bypassme': function(element, renderer) {
                    return true;
                }
            };

            doc.fromHTML(
                source, // HTML string or DOM elem ref.
                0.5,    // x coord
                0.5,    // y coord
                {
                    'width': 7.5, // max width of content on PDF
                    'elementHandlers': specialElementHandlers
                });

            doc.output('dataurl');
        }
    </script>
    <!-- end -->
    <!-- print area -->

    <script src="<?=base_url()?>js/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>

    <link type="text/css" rel="stylesheet" href="<?=base_url()?>js/PrintArea.css" />
    <!-- end printarea js -->
    <style type="text/css">
        .fixed {  position: fixed;    top: 0; width: 100%; z-index: 9;}
        .fixed2 { position:fixed; width:90%; z-index:9;top:40px; background:#fff; border-bottom: 1px solid #eee; padding-bottom: 10px; padding-top:10px;}
    </style>
    <script>
        $(function(){


            $(window).scroll(function() {

                if ($(window).scrollTop() >= 100) {

                    $('.nav').addClass('fixed');

                } else {

                    $('.nav').removeClass('fixed');
                    if($(window).scrollTop() < 200){
                        $('.titles_h').removeClass('fixed2');
                    }



                }
                if ($(window).scrollTop() >= 200) {
                    $('.titles_h').addClass('fixed2');
                }

            });
            $(document).on('click','.beb',function(){
                var formdata = {};

                var url = $(this).data("ref");
                var datavalue = $(this).data("value");
                var bid = $(this).data("bid");
                var haslots = $(this).data("lot");
		var framework = $(this).data("framework"); 
                formdata['bidid'] = bid;
                formdata['receiptid'] = datavalue;
                formdata['haslots'] = haslots;
		formdata['framework'] = framework;

//alert("mover");
                $(".modal-body").html("proccessing ...");
                console.log(formdata);

                $.ajax({
                    type: "POST",
                    url:  url,
                    data: formdata,
                    success: function(data, textStatus, jqXHR){
                        $(".modal-body").html(data);
                    },
                    error:function(data , textStatus, jqXHR) {
                        console.log(data);
                    }
                });




            });

        });
    </script>


    <link href="<?=base_url()?>css/my_public.css" rel="stylesheet">

</head>

<body>
<!-- Facebook Share -->

<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&appId=1430944160536983&version=v2.0";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<!-- end -->

<!-- Twitter -->
<style>
    .username a{text-decoration: none;}
    a{text-decoration: none;}
</style>

<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<!-- End -->
<div class="container">
    <div class="clearfix">
        <div class="column header">
            <div class="row">



                <div class="col-md-12 column clearfix" style="margin:15px auto 10px">

                    <div class="col-md-4 ">
                        <a href="<?= base_url() . 'page/home'; ?>" style="text-decoration:none;">
                           <!--  <div class="col-lg-2" style="padding-left:0">
                                
                            </div> -->
                            <div class="col-lg-10" style="padding-left:0">
                                <h3 style="margin-top:10px;color:#000; font-size:25px; "> <span style="color:orange; font-weight:bold; ">MY</span> SHOP <br/><div style="margin-top:-8px;"><small>MANAGEMENT SYSTEM v1.0 </small></div></h3>
                            </div>
                        </a>
                    </div>

                    <?php
                    $role_str = '';
                    $pde_str = '';
                    $userid = $this->session->userdata('usergroup');
                    if(!empty($userid) )
                    {
                       
                        
                        ?>
                        <form id="login-form" class="clearfix" name="form1" method="post" action="<?php echo base_url();?>admin/login">
                            <ul>
                                <li>
         <span class="username"><h5> You're logged in
                 as <?= ucwords(strtolower($this->session->userdata('firstname') . ' ' . $this->session->userdata('lastname'))); ?>
                 &nbsp; |&nbsp; <a href="<?= base_url(); ?>user/dashboard">Your Dashboard </a> &nbsp; | &nbsp; <a
                     href="<?= base_url() . 'admin/logout'; ?>"> LOGOUT </a>
             </h5>
         </span>
                                </li>


                            </ul>
                        </form>
                        <?php

                    }
                    else
                    {
                        ?>
                        

                       
                        <?php
                    }
                    ?>
                </div>

            </div>

 

        </div>
    </div>
