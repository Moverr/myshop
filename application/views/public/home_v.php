<?=$this->load->view('public/includes/header')?>
    <div class="clearfix content-wrapper" style="padding-top:28px">
        <div class="col-md-12" style="margin:0 auto">
            <div class="clearfix">            
                <div class="col-md-13 column content-area">
                	<?php 
						if(!empty($msg)):
							print '<div class="row">'.format_notice($msg).'</div>';
						endif;
						
						$this->load->view((!empty($view_to_load)? $view_to_load : 'public/includes/login'));
					?>

                </div>
            </div>
        </div>
    	<?=$this->load->view('public/includes/footer')?>
    </div>	
	</div>
	</body>
</html>