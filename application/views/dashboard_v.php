<?php
ob_start();
?>
<!DOCTYPE html>
<!--
Template Name: Admin Lab Dashboard build with Bootstrap v2.3.1
Template Version: 1.2
Author: Mosaddek Hossain
Website: http://thevectorlab.net/
-->

<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8" />
    <title><?=SITE_TITLE . (!empty($page_title)? ": " . $page_title : "")?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <link href="<?=base_url()?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap-fileupload.css" rel="stylesheet"/>
    <link href="<?=base_url()?>assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
    <link href="<?=base_url()?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/gritter/css/jquery.gritter.css" />

    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/chosen-bootstrap/chosen/chosen.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/jquery-tags-input/jquery.tagsinput.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/clockface/css/clockface.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/bootstrap-wysihtml5/bootstrap-wysihtml5.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/bootstrap-datepicker/css/datepicker.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/bootstrap-timepicker/compiled/timepicker.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/bootstrap-colorpicker/css/colorpicker.css" />
    <link rel="stylesheet" href="<?=base_url()?>assets/bootstrap-toggle-buttons/static/stylesheets/bootstrap-toggle-buttons.css" />
    <link rel="stylesheet" href="<?=base_url()?>assets/data-tables/DT_bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/bootstrap-daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" media="screen" href="http://openfontlibrary.org/face/caladea" type="text/css"/>


    <link href="<?=base_url()?>css/style.css" rel="stylesheet" />
    <link href="<?=base_url()?>css/style_responsive.css" rel="stylesheet" />
    <link href="<?=base_url()?>css/style_default.css" rel="stylesheet" id="style_color" />
    <link href="<?=base_url()?>assets/fancybox/source/jquery.fancybox.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/uniform/css/uniform.default.css" />
    <link href="<?=base_url()?>assets/fullcalendar/fullcalendar/bootstrap-fullcalendar.css" rel="stylesheet" />

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300|Lato:400,900' rel='stylesheet' type='text/css'>
    <script src="<?= base_url() ?>js/jquery-1.8.3.min.js"></script>
    <script src="<?= base_url() ?>assets/select2/js/select2.full.min.js"></script>




    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/tableExport.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jquery.base64.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/html2canvas.js"></script>
    <script type="text/javascript"
            src="<?= base_url() ?>assets/tableexport/jspdf/libs/sprintf.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/tableexport/jspdf/jspdf.js"></script>
    <script type="text/javascript"
            src="<?= base_url() ?>assets/tableexport/jspdf/libs/base64.js"></script>




    <link rel="stylesheet" href="<?= base_url() ?>assets/DataTables/media/css/jquery.dataTables.min.css">

<!-- 
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/DataTables/extensions/Responsive/css/dataTables.responsive.css">
    <script type="text/javascript" src="<?= base_url() ?>assets/DataTables/extensions/Responsive/js/dataTables.responsive.js"></script>-->

    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/DataTables/extensions/TableTools/css/dataTables.tableTools.css">

    <!--TinyMCE-->
    <script type="text/javascript" src="<?=base_url()?>assets/tinymce/tinymce.min.js"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "#mytextarea",
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
    </script>

    <!--End-->



    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/select2/css/select2.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/my_admin.css" />

    <!--    NOTE TO TEAM
    any rule added here overides all above rules. This should always be the last css file.
    No css file should be under this
    -->
    <link href="<?=base_url()?>css/customize_dashboard.css" rel="stylesheet" />
    <!-- css-->
    <style type="text/css">
        #cssmenu ul,
        #cssmenu li,
        #cssmenu span,
        #cssmenu a {
            margin: 0;
            padding: 0;
            position: relative; text-transform: uppercase;
        }
        #cssmenu {
            line-height: 1;
            /*border-radius: 5px 5px 0 0;
            -moz-border-radius: 5px 5px 0 0;
            -webkit-border-radius: 5px 5px 0 0;*/
            background: #141414;
           
            border-bottom: 2px solid #0fa1e0;
             border-top: 2px solid #0fa1e0;
            width: 100%;
        }
        #cssmenu:after,
        #cssmenu ul:after {
            content: '';
            display: block;
            clear: both; width: 100%;
        }
        #cssmenu a {
            background: #141414;
       
            color: #ffffff;
            display: block;

            padding: 19px 20px;
            text-decoration: none;
        }
        #cssmenu ul {
            list-style: none;
        }
        #cssmenu > ul > li {
            display: inline-block;
            float: left;
            margin: 0;
        }
        #cssmenu.align-center {
            text-align: center;
        }
        #cssmenu.align-center > ul > li {
            float: none;
        }
        #cssmenu.align-center ul ul {
            text-align: left;
        }
        #cssmenu.align-right > ul {
            float: right;
        }
        #cssmenu.align-right ul ul {
            text-align: right;
        }
        #cssmenu > ul > li > a {
            color: #ffffff;
            font-size: 73%;
        }
        #cssmenu > ul > li:hover:after {
            content: '';
            display: block;
            width: 0;
            height: 0;
            position: absolute;
            left: 50%;
            bottom: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 10px solid #0fa1e0;
            margin-left: -10px;
        }
        #cssmenu > ul > li:first-child > a {
            border-radius: 5px 0 0 0;
            -moz-border-radius: 5px 0 0 0;
            -webkit-border-radius: 5px 0 0 0;
        }
        #cssmenu.align-right > ul > li:first-child > a,
        #cssmenu.align-center > ul > li:first-child > a {
            border-radius: 0;
            -moz-border-radius: 0;
            -webkit-border-radius: 0;
        }
        #cssmenu.align-right > ul > li:last-child > a {
            border-radius: 0 5px 0 0;
            -moz-border-radius: 0 5px 0 0;
            -webkit-border-radius: 0 5px 0 0;
        }
        #cssmenu > ul > li.active > a,
        #cssmenu > ul > li:hover > a {
            color: #ffffff;
            box-shadow: inset 0 0 3px #000000;
             -moz-box-shadow: inset 0 0 3px #000000;
             -webkit-box-shadow: inset 0 0 3px #000000;
            background: #0fa1e0;
            background: -moz-linear-gradient(top, #262626 0%, #070707 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #262626), color-stop(100%, #070707));
            background: -webkit-linear-gradient(top, #262626 0%, #070707 100%);
            background: -o-linear-gradient(top, #262626 0%, #070707 100%);
            background: -ms-linear-gradient(top, #262626 0%, #070707 100%);
            background: linear-gradient(to bottom, #262626 0%, #070707 100%);
        }
        #cssmenu .has-sub {
            z-index: 1;
        }
        #cssmenu .has-sub:hover > ul {
            display: block;
        }
        #cssmenu .has-sub ul {
            display: none;
            position: absolute;
            width: 200px;
            top: 100%;
            left: 0;
        }
        #cssmenu.align-right .has-sub ul {
            left: auto;
            right: 0;
        }
        #cssmenu .has-sub ul li {
            *margin-bottom: -1px;
        }
        #cssmenu .has-sub ul li a {
            background: #0fa1e0;
            border-bottom: 1px dotted #31b7f1;
            font-size: 11px;
            filter: none;
            display: block;
            line-height: 120%;
            padding: 10px;
            color: #ffffff;
        }
        #cssmenu .has-sub ul li:hover a {
            background: #0c7fb0;
        }
        #cssmenu ul ul li:hover > a {
            color: #ffffff;
        }
        #cssmenu .has-sub .has-sub:hover > ul {
            display: block;
        }
        #cssmenu .has-sub .has-sub ul {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
        }
        #cssmenu.align-right .has-sub .has-sub ul,
        #cssmenu.align-right ul ul ul {
            left: auto;
            right: 100%;
        }
        #cssmenu .has-sub .has-sub ul li a {
            background: #0c7fb0;
            border-bottom: 1px dotted #31b7f1;
        }
        #cssmenu .has-sub .has-sub ul li a:hover {
            background: #0a6d98;
        }
        #cssmenu ul ul li.last > a,
        #cssmenu ul ul li:last-child > a,
        #cssmenu ul ul ul li.last > a,
        #cssmenu ul ul ul li:last-child > a,
        #cssmenu .has-sub ul li:last-child > a,
        #cssmenu .has-sub ul li.last > a {
            border-bottom: 0;
        }

.x{
    padding-top: 10px;
}
    </style>
    <!-- end-->
    
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="fixed-top">

<!-- Lightbox -->
<style type="text/css">
    #lightbox_wrapper{
        position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; position: fixed;  z-index: 9999999999; overflow:scroll; display: none;
    }
    #lightbox{width:80%; border-top:2px solid #000;  position: relative;  min-height: 600px; overflow-x: visible; background: #fff; margin:auto; margin-top: 5%;}
    #lightbox_close{background: #eee; color: #000; padding: 15px;  float: right;  max-height:50px; max-width:50px;}
    #lightbox_close a{  color: #000; font-size: 16px; font-weight: bold;}
    #lightbox_heading{ color: #000; padding: 15px;  float: left;  max-height:50px;  width:80%;}
    #lightbox_body{clear: both; width: 95%; margin: auto;}
</style>
<script type="text/javascript">
    $(function(){

        //print functionality in the header
        $('.print').click(function(){
            //alert('Ready to Print');
            w = window.outerWidth-10;
            $(".printarea").css("width", '80%');
            $(".printarea").printArea();
        });


        $("#lightbox_close").click(function(){
            $("#lightbox_wrapper").fadeOut('slow');
        });
    })
</script>
<div id="lightbox_wrapper">
    <div id="lightbox">
        <div id="lightbox_close">
            <a href="#"> X </a>
        </div>

        <div   style="float:left; background: #eee;     color: #000;     padding: 15px;     float: right;     max-height: 50px;     max-width: 50px; ">
            <a class="print"  style="text-decoration:none; color:#00; font-weight:bold; cursor:pointer;"> PRINT  <i class="fa fa-print" ></i> </a>
        </div>

        <div id="lightbox_heading">
            <h2> LIGHT BOX NEW WAVE </h2>
        </div>
        <div id="lightbox_body">

        </div>
    </div>

</div>

<!-- end of lightbox -->
 
<!-- BEGIN HEADER -->
<?php $this->load->view('includes/header'); ?>
<!-- END HEADER -->
<!-- end -->
<!-- <div id="cssmenu" class="" style="height:58px;  margin-top:0px;  position:fixed; z-index:999; display:block;"> -->
    <?php # $this->menu_engine->display_menu((!empty($current_menu)? $current_menu : ''))?>

<!-- </div> -->
<!-- END HEADER -->
<!-- BEGIN CONTAINER -->
<div id="container" class="row-fluid">
    <!-- BEGIN SIDEBAR -->
    <div id="sidebar" style="position: fixed; background: #666; min-width: 100px; min-height: 1000px;" class="nav-collapse collapse">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler hidden-phone"></div>
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->

        <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
        <div class="navbar-inverse">
            <form class="navbar-search visible-phone">
                <input type="text" class="search-query" placeholder="Search"/>
            </form>
        </div>
        <!-- END RESPONSIVE QUICK SEARCH FORM -->

        <!-- BEGIN SIDEBAR MENU -->
        <?php  $this->menu_engine->display_menu((!empty($current_menu)? $current_menu : '')) ?>
     


    </div>
    <!-- END SIDEBAR -->
    <!-- BEGIN PAGE -->
    <div id="main-content"  >
        <!-- BEGIN PAGE CONTAINER-->
        <div class="container-fluid">
            <!-- BEGIN PAGE HEADER-->
            <div class="row-fluid">
                <div class="span12">
                    <!-- BEGIN PAGE TITLE & BREADCRUMB-->
                    <h3 class="page-title">

                        <?= (!empty($page_title) ? $page_title : '') ?>
                        <small><?=(!empty($page_description)? $page_description : '' )?></small>
                    </h3>
                    <ul class="breadcrumb">

                        <li class="pull-right search-wrap">
                            <form class="hidden-phone" id="search-form" method="post" action="<?=base_url() . (!empty($search_url)? $search_url : '')?>">
                                <div class="search-input-area">
                                    <input id="search-query" class="search-query" type="text" placeholder="Search">
                                    <i class="icon-search"></i>
                                </div>
                            </form>
                        </li>
                    </ul>
                    <!-- END PAGE TITLE & BREADCRUMB-->
                </div>
            </div>


            <?php
            # LOG ACCESS TRAIL




            ?>
            <!-- END PAGE HEADER-->
            <!-- BEGIN PAGE CONTENT-->
            <div id="page" class="dashboard">
                <div class="alert " style="display: none;">Record Saved Succesfully</div>
                <?php if (!empty($msg)) print format_notice($msg); ?>

                <?php if (!empty($current_menu) && $current_menu == 'dashboard') $this->load->view('admin/stats_summary'); ?>

<div style="min-height: 600px;" >
                <?php if (!empty($view_to_load)) $this->load->view($view_to_load, (!empty($view_data) ? $view_data : array())); ?>
</div>
                <div style="width:100%; text-align:center; font-size:70%;  ">Developed at <a style="font-weight:bold; text-decoration:none;" href="#" target="_blank">codemovers.com.</a> </div>

 
                <?php

//message
                  echo  "<div id='dialog' title='Basic dialog' style='display:none;'>
                     <p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
                     </div>" ;

                ?>




            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
    <!-- END PAGE CONTAINER-->
</div>
<!-- END PAGE -->
</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->

<!-- BEGIN FOOTER -->
<div id="footer">

    <!-- <div class="span pull-right">
            <span class="go-top"><i class="icon-arrow-up"></i></span>
        </div> -->
</div>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS -->
<!-- Load javascripts at bottom, this will reduce page load time -->

<style>

    table th{text-transform:uppercase; font-size:85%; } .tabbable a {text-transform:uppercase; font-size:85%; font-weight:bold; }.alert{  text-transform: uppercase;
                                                                                                                                     font-size: 80%; color:#000;}
</style>

<script src="<?=base_url()?>assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?=base_url()?>assets/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?=base_url()?>assets/fullcalendar/fullcalendar/fullcalendar.min.js"></script>
<script src="<?=base_url()?>assets/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/bootstrap/js/bootstrap-fileupload.js"></script>
<script src="<?=base_url()?>assets/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script src="<?=base_url()?>js/jquery.blockui.js"></script>
<script src="<?=base_url()?>js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/bootstrap/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/bootstrap/js/bootstrap-timepicker.js"></script>
<!-- ie8 fixes -->
<!--[if lt IE 9]>
<script src="js/excanvas.js"></script>
<script src="js/respond.js"></script>
<![endif]-->
<script src="<?=base_url()?>assets/jquery-knob/js/jquery.knob.js"></script>
<script src="<?=base_url()?>js/jquery.peity.min.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/uniform/jquery.uniform.min.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/chosen-bootstrap/chosen/chosen.jquery.min.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/clockface/js/clockface.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/jquery-tags-input/jquery.tagsinput.min.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-toggle-buttons/static/js/jquery.toggle.buttons.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-daterangepicker/date.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-colorpicker/js/bootstrap-colorpicker.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
<script type="text/javascript" src="<?=base_url()?>assets/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>


<script src="<?= base_url() ?>js/moverjs.js"></script>
<script src="<?= base_url() ?>js/scripts.js"></script>


<script src="<?=base_url()?>assets/fancybox/source/jquery.fancybox.pack.js"></script>







<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/media/media/css/jquery.dataTables.css">
<script type="text/javascript" language="javascript" src="<?= base_url() ?>assets/media/js/jquery.dataTables.js"></script>


<script>
    jQuery(document).ready(function() {
        // initiate layout and plugins
        App.setMainPage(true);
        App.init();
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>