<?php
ob_start();
?>
<?php



    $role_str = '';
    $pde_str = '';
    
    $my_user_group = $this->db->get_where('usergroups', array('isactive'=>'Y', 'id'=>$this->session->userdata('usergroup')))->result_array();                                   
    
    if(!empty($my_user_group))
    {
        $role_str = $my_user_group[0]['groupname'];
    
        if($role_str == 'Administrator')
        {
            $role_str = 'System Administrator';
        }
        else
        {                               
            #also get the user's PDE
            $myshop_details = $this->db->get_where('shop', array('isactive'=>'Y', 'id'=>$this->session->userdata('shopid')))->result_array();
            
            if(!empty($myshop_details))
            {
               //  $pde_str = $myshop_details[0]['pdename'];
               if(strlen($myshop_details[0]['shopname']) > 14)
               {
                     $pde_str = $myshop_details[0]['shopname'];
               }
               else
               {
                 $pde_str = $myshop_details[0]['shopname'];

               }
                    
                
            }            
        }
    }

$userid = $this->session->userdata('userid');
$usergroup = $this->session->userdata('usergroup');
#print_r($userid); exit();
#weeklyreport();

$notification = 0 ;
# count_new_notifications($userid);
$view_notifications = 0;
#notification_list($userid, 5);

#print_r($view_notifications);  exit();
//exit();
//exit();
?>
<div id="header" class="navbar navbar-inverse navbar-fixed-top" style="border-bottom:2px solid orange; height:60px;">
    <!-- BEGIN TOP NAVIGATION BAR -->
    <div class="navbar-inner">
        <div class="container-fluid logo_container">
            <!-- BEGIN LOGO -->


                <div class="brand" >
                    <div class="pull-left col-lg-2 " style="font-size: 30px; font-weight: bold; margin-left: -10px; color:orange;" >
                      MY
                       
                    </div>

                    <div class="pull-right col-lg-9 logo_details" style="padding-top: 7px;">
                        <div class="dashboardtitle">SHOP</div>
                    </div>


                </div>

            <style type="text/css">
               .nav .top-menu .fa-home, .nav .top-menu .fa-bell, .nav .top-menu .fa-cog, .nav .top-menu .fa fa-envelope-alt, .username {
                color: #fff; }
 
                .username {
                font-weight: bold;   margin-left: 5px; font-size: 13px; }
                .username a{ color: #fff; text-decoration: none;}
            </style>
            <!-- END LOGO -->
            <!-- BEGIN RESPONSIVE MENU TOGGLER -->
            <a class="btn btn-navbar collapsed" id="main_menu_trigger" data-toggle="collapse" data-target=".nav-collapse">
                <span class="fa-home"></span>
            <span class="fa fa-bar"></span>
            <span class="fa fa-bar"></span>
            <span class="arrow"></span>
            </a>
            <!-- END RESPONSIVE MENU TOGGLER -->
            <div id="top_menu" class="nav notify-row">
                <!-- BEGIN NOTIFICATION -->
                <ul class="nav top-menu" style="margin-left:-55px;">
                    <!-- BEGIN SETTINGS -->
                    <li class="dropdown" onClick="javascript:location.href='<?= base_url().'user/profile_form';?>'">
                        <a class="dropdown-toggle element" data-placement="bottom" data-toggle="tooltip" href="#" data-original-title="My Settings">
                            <i class="fa fa-cog"></i>
                        </a>
                    </li>
                    <!-- END SETTINGS -->
                    <!-- BEGIN INBOX DROPDOWN -->
                    <li class="dropdown" id="header_inbox_bar">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa  fa-bell"></i>
                            <?php if ($notification > 0): ?>
                                <span class="badge badge-important"><?= $notification ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <li>
                                <p><?= "You have $notification new message" . (($notification > 1 || $notification == 0) ? 's' : '') . " " ?></p>
                            </li>
                            <?php  

                            if(!empty($view_notifications))
                            {

                            foreach ($view_notifications as $key => $value) {
                                # code...
                                ?>
                                <li>
                                    <a href="<?= base_url() . 'notifications/view_notification/notification/' . base64_encode(($value['id'])); ?>">
                                     
                                    <span class="subject">
                                    <span class="from"><?= $value['title']; ?></span>
                                    <span class="time">
                                    
                                     </span>
                                    </span>
                                  
                                    </a>
                                </li>
                            <?php
                            } 
                            } 

                            ?>



 <li>
                                <a href="<?= base_url() . 'notifications/view_all_notifications/member/' . base64_encode($userid); ?>/level/all">
                                    See all messages</a>
                            </li>
                        </ul>
                    </li>


                    <!-- END INBOX DROPDOWN -->
                    <!-- BEGIN NOTIFICATION DROPDOWN -->



                    <li class="dropdown" id="header_notification_bar">
                        <a class="dropdown-toggle element" data-placement="bottom" data-toggle="tooltip" href="#"
                           data-original-title="Go to public site">
                            <i class="fa fa-home" onClick="javascript:location.href='<?=base_url() . 'page/home'; ?>'; "></i>
                            <!--<span class="badge badge-warning">7</span>-->
                        </a>
                    </li>
               
                    <!-- END NOTIFICATION DROPDOWN -->
                    
                    <?php 
                        #Only system admins should proceed if they do no have a pde
                    if (!empty($my_user_group) && $my_user_group[0]['groupname'] != 'Administrator' && $my_user_group[0]['groupname'] != 'Administrator' && empty($myshop_details))
                        {
                            print '<li>'.
                                  '<div>'. 
                                  format_notice('ERROR: System could not identify your Procurement & Disposal Entity').
                                  '</div>'.
                                  '</li>';
                        }
                        else
                        {
                            print '<li>'.
                                  '<div class="user_pde_name"> This is the '.  $pde_str. ' dashboard. </div>'.
                                  '</li>';
                        }
                    ?>
                    <li> 
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <table>
                                <tr>
                                    <td>
                                      
                                    </td>
                                    <td>
                                        <span class="username">
                                            You're logged in as <?= ucwords(strtolower($this->session->userdata('firstname') . ' ' . $this->session->userdata('lastname'))); ?> &nbsp; | &nbsp;
                                             <a onClick="javascript:location.href='<?=base_url().'admin/logout'?>'; " href="<?=base_url().'admin/logout'?>"> LOGOUT </a>
                                        </span>

                                    </td>

                                </tr>
                            </table>
                        </a>
                    </li>
                </ul>
            </div>
                <!-- END  NOTIFICATION -->
            <div class="top-nav ">
                <ul class="nav pull-right top-menu span3">
                    <!-- END SUPPORT -->
                    <!-- BEGIN USER LOGIN DROPDOWN -->
                    <li class="dropdown user_profile_info">
                     <!--    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <table>
                                <tr>
                                    <td>
                                        <img class="profile_photo" src="<?=base_url() . 'images/users/' . user_photo_thumb($this->session->userdata('photo'))?>" alt="">
                                    </td>
                                    <td>
                                        <span class="username">
                                            You're logged in as <?= ucwords(strtolower($this->session->userdata('firstname') . ' ' . $this->session->userdata('lastname'))); ?>
                                        </span>

                                    </td>

                                </tr>
                            </table>
                        </a> -->
                        <!--    <ul class="dropdown-menu">
                            <li><a href="<?=base_url() . 'user/profile_form'?>"><i class="fa fa-user"></i> My Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="<?=base_url() . 'admin/logout'?>"><i class="fa fa-key"></i> Log Out</a></li>
                        </ul> -->
                    </li>
                    <!-- END USER LOGIN DROPDOWN -->
                </ul>
                <!-- END TOP NAVIGATION MENU -->
            </div>
        </div>
    </div>
    <!-- END TOP NAVIGATION BAR -->
</div>
<?php 
    #Only system admins should proceed if they do no have a pde
    if(!empty($my_user_group) && $my_user_group[0]['groupname'] != 'Administrator' && empty($myshop_details))
        exit();
?>