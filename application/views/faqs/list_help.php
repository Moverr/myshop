<script type="text/javascript">
	$(document).on('click','.printer',function(){
    	$(".table").printArea();
  	})
  
	$(document).ready(function() {
  		$('.table').dataTable({
     		"paging":   false,
        	"ordering": true,
        	"info":     false });
			
		$('li.dynamic_menu').click(function() {
        	var id = $(this).data('details');
			$('#info').html('GENERATING CONTENT FOR SECTION');
			
			var form_data =
            {
                section_id: id
            };
			
			$.ajax({
                url: "<?=base_url().'faqs/load_section' ?>",
                type: 'POST',
                data: form_data,
                success: function(msg) {
                    $('#info').html(msg);

                }
            });
            return false;
		
    	}); 	
		
	});
</script>

<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;<a href="<?php echo base_url()?>downloads/ITPSystemUserGuide.pdf" target="new"><i class="fa fa-file-pdf-o"></i>&nbsp;Download System User Guide</a></h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <?php
		if(!empty($help_menu)):
		?>
        <div class="widget-body" id="results">
    		<div class="widget-body">
            	<div class="control-group">
                	<div class="controls">
                    	<input placeholder="Search help topics" style="float:right" type="text" name="search" class="input-xlarge" id="search"/>
                    </div>
                </div>
        		<div class="row-fluid">
            		<div class="span3">
                		<h4 class="title grey">Help Topics</h4>
                    		<div class="clearfix">
                        		<ul class="nav nav-list faq-list" id="finalResult">
                            		<?php
										foreach($help_menu as $topic)
										{
											print '<li data-details="'.$topic['id'].'" class="dynamic_menu"><a href="javascript:void(0)"><i class=" icon-signin"></i>'.$topic['faq_topic'].'</a></li>';
										}
                                	?>
                            	</ul>
                        	</div>
                		</div>
                    	<!-- Dynamic loading of content -->
                        <div class="span9" id="info">
                			<h4>System How to's</h4>
                    		<p>
                             The help section is part of our continued effort to serve system users better. It is of the purpose to make the Government Procurement Portal (GPP) more user-friendly, quicker and easier when accessed via www.gpp.ppda.go.ug online.</p>
    						<p>
                            The help section is divided into sub sections of user scenarios based on the possible user needs. The user is advised to follow the steps below.</p>
                            
                            <p>
                            In case of any inquiries while in this section, you are urged to contact the GPP system administrators at:
                            </p><br />
                            <p>
                            UEDCL Towers 5th Floor, <br />
                            Plot 37, Nakasero Road<br /> 
                            P.O. Box 3925,<br /> 
                            KAMPALA, Uganda<br />
                            Tel: (+256) 414 311 100 <br />
                            Fax: (+256) 414 344 858 <br />
                            Web: www.ppda.go.ug<br />

                            </p>
                		</div>
                    	<!-- End -->
            	</div>
        	</div>
    	</div>
        <?php
		endif;
    ?>
</div>
<script>
	$(function()
	{
	  $("#search").keyup(function()
	  {
	  
	  var formdata = {};
	  
	  var searchtext = $("#search").val();
	  formdata['searchtext'] = searchtext;
	  var url = "<?=base_url().'faqs/search_menu' ?>";
	  $(".alert").html("Proccessing....").fadeIn('slow');
	  console.log(url);
		 
			$.ajax({
				type: "post",
				url: url,				 			
				data:formdata,				 
				success: function(data){
				   $(".alert").html(" ").fadeOut('slow');
					$('#finalResult').html(data);
					
					
				},
				error: function(data){						
					console.log("CAN NOT CONTACT SERVER :: "+data)
				}
			});
		
		 
	  });
	});
</script>