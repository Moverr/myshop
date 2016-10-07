<style>
			
 
</style>
   <script type="text/javascript">
   $(function(){

    //print functionality in the header
    $('.print').click(function(){
        //alert('Ready to Print');
        w = window.outerWidth-10;
        $(".printarea").css("width", '100%');
        $(".printarea").printArea();
         });
})
    </script>
			<div class="modal modal-lg fade bs-example-modal-lg" id="modal-container-703202" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg " style="width:80%;">
					<div class="modal-content modal-lg">
						<div class="modal-header">
							 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="myModalLabel">
							<button class="print"> PRINT </button>

							</h4>
						</div>
						<div class="modal-body">
							...
						</div>
						<div class="modal-footer">
						

        <?php
         $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";

            print  ''.
                    '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.$url.'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.$url.'"></div>&nbsp;<div class="fb-share-button" data-href="'.$url.'" data-layout="button" data-size="medium"></div>'
         
         ?>
						</div>
					</div>
					
				</div>
				
			</div>