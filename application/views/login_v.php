<?php
if(empty($requiredfields)){
	$requiredfields = array();
}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<meta name="description" content="Uganda Tender Portal">
<meta name="keywords" content="Tenders, Portal">
<title><?=SITE_TITLE." : "?> - Login</title>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL;?>favicon.ico">
<?php
echo "<script src='".base_url()."js/jquery-1.10.2.min.js' type='text/javascript'></script>";
echo minimize_code($this, 'javascript');
 echo minimize_code($this, 'stylesheets');?>
</head>

<body>
	<div id="body-bag">
		<div id="site-header">
        	<div id="site-header-wrap">
            	<div class="grid clear">
                	<div class="logo">
                    	<a href="<?php echo base_url();?>">
                        	<img id="ppda-itp-logo" src="<?php echo base_url();?>images/ppda.jpg" alt="PPDA logo" />
                        </a>
                    </div>
                    <div class="slogan">
                    	<div class="slogan_text">
                    		<?php echo SITE_SLOGAN;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="site_section_divisor">
        	<div id="site_section_divisor_wrap">
            	<div class="grid">
                	<div class="divisor"></div>
                </div>
            </div>
        </div>
        
        <div id="site-body">
        	<div id="site-body-wrap">
            	<div class="grid clear">
                	<?php if(!empty($msg)) echo '<tr><td>'.format_notice($msg).'</td></tr>'; ?>
                	<div id="login-form">
                    	<div class="page_header">Login</div>
                    	<form id="form1" name="form1" method="post" action="<?php echo base_url();?>admin/login">
                        	<ul>
                            	<li>
                                	<label for="acadusername">Email Address:</label>
                                    <?php  echo get_required_field_wrap($requiredfields, 'acadusername');?>
                                    <input type="text" name="acadusername" id="acadusername" size="45" value="<?php if(!empty($formdata['acadusername'])) echo $formdata['acadusername'];?>" required/>
									<?php echo get_required_field_wrap($requiredfields, 'acadusername', 'end');?>
                                </li>
                                
                                <li>
                                    <label for="acadusername">Password:</label>
                                    <?php echo get_required_field_wrap($requiredfields, 'acadpassword');?>
                                    <input type="password" name="acadpassword" id="acadpassword" size="45" value="" required/>
                                    <?php echo get_required_field_wrap($requiredfields, 'acadpassword', 'end');?>
                                </li>	
                                
                                <li>
                                	<input type="hidden" name="login" value="login" />
                    				<input style="border:none" value="Submit" name="login_btn" type="submit" />
                                </li>
                            </ul>
      					</form>
                    </div>
                    <div id="home_page_graphic">
                    	<img id="the-tender-portal" src="<?php echo base_url();?>images/ring-cogs.jpg" alt="Uganda Integrated Tender Portal" />
                    </div>
                </div>
            </div>
        </div>
         	
    </div>
<table border="0" cellspacing="0" cellpadding="5" align="center">
  <tr>
    <td colspan="2" style="padding-top:20px;">&nbsp;</td>
  </tr>
  <tr>
    
    <td valign="top" align="center">
   
        
      </td>
  </tr>
  <tr>
    <td colspan="2" valign="top" align="center"><?php $this->load->view('includes/footer', array('ignore_resize'=>'Y'));?></td>
  </tr>
</table>
</body>
</html>
