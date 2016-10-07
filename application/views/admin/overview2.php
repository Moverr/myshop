<div class="widget">
		<div class="widget-title">
			<h4><i class="icon-reorder"></i>&nbsp;Dashboard Overview </h4>
				<span class="tools">
					<a href="javascript:;" class="icon-chevron-down"></a>
					<a href="javascript:;" class="icon-remove"></a>
				</span>
		</div>
		<div class="widget-body" id="results">
		
		<style type="text/css">
.dashboard_overview div ul
{  
	margin-left:5px;  padding-bottom: 5px;
}
.dashboard_overview div ul li{ list-style: none;}
.dashboard_overview div ul li ul {}
.dashboard_overview div ul li ul li{}
.dashboard_overview div ul li a{}
.dashboard_overview  .has-sub a{font-size: 20px; text-decoration: none; font-weight: bold; letter-spacing: -1px;}
.dashboard_overview  .sub li{ list-style-type: square; margin-left:15px; padding:5px; border-bottom:1px solid #eee;}
.dashboard_overview  .sub li a{font-size: 12px; font-weight: normal; letter-spacing: 0px;}
 
		</style>
	<div class="container-fluid">
	<div class="row-fluid dashboard_overview">
	 <?php
	   $this->load->model('menu_engine_dashboard');  
	   //print_r($current_menu);
	//$this->load->model('menu_engine_dashboard');
	// $data['active'] = $this-> pde_m -> fetch_pdes('in');    
	// $this-> menu_engine_dashboard ->display_menu('');
	 ?>
		 
			 
		
	</div>
</div>
		</div>