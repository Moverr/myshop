<div class="widget">
    <div class="widget-title">
    <?php
	$userid = $this->session->userdata('userid');
	 
	$encoded = base64_encode($userid);
	?>
        <h4><i class="icon-reorder"></i>&nbsp;Notifications </h4>  <h4> <?php   echo "<a href='".base_url()."notifications/view_all_notifications/member/".$encoded."/level/all' >View Notifications </a>" ?>   </h4> 
        
          
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body">

<style type="text/css">
.OLD{   color: RED; display: block; font-size: 16px;}
.NEW{   color: ORANGE; display: block; font-size: 16px;}
.notifictns  > .accordion{ border: none;}
.notifictns  > .accordion > .accordion-group{ border: none;}
	.notification_lists
	{
		list-style: none; margin-left: 0px;

	}
	.notification_lists a{ border-bottom: 1px solid #ddd; text-decoration: none; padding: 5px;  color: #000; width:100%; display: block;}
.starred{color:red;}
</style>

    <!-- start -->
<?php
 $entity =  $this->session->userdata('pdeid');
				  $query = $this->db->query("SELECT * FROM pdes WHERE pdeid=".$entity." limit 1")-> result_array();
				
				#print_r($query[0]['pdename']);
?>
<div class="row-fluid">

<!-- canvas start -->
<div class="span2 notifictns" >
			<div class="accordion" id="accordion-879581">
				<div class="accordion-group">
				 
					<div class="accordion-heading">
						 <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-879581" href="#accordion-element-974744">
						 Information </a>
					</div>
					<div id="accordion-element-974744" class="accordion-body ">
						<div class="accordion-inner">
						<?php
							$newnotifications = count_new_notifications($userid);
							$read = count_other_notifications($userid,'R');
							$read =  $read > 20 ?'20+':$read;
							$starred = count_other_notifications($userid,'S');
							$starred =  $starred > 20 ?'20+':$starred;

							$trash = count_other_notifications($userid,'T');
							$trash =  $trash > 20 ?'20+':$trash;

						?>
							<ul class="notification_lists">							
	<!-- 						<li><a href="<?=base_url().'notifications/view_all_notifications/member/'.$encoded.'/level/unread'; ?>">View Notifications </a></li> -->
							<li><a href="<?=base_url().'notifications/view_all_notifications/member/'.$encoded.'/level/unread'; ?>"> <i class="fa fa-inbox"> </i>Unread  <span class="badge badge"><?=$newnotifications; ?></span></a></li>
							<li><a href="<?=base_url().'notifications/view_all_notifications/member/'.$encoded.'/level/read'; ?>"><i class="fa fa-list-alt"> </i> Read  <span class="badge badge"><?=$read; ?> </span></a></li>
							<li><a href="<?=base_url().'notifications/view_all_notifications/member/'.$encoded.'/level/starred'; ?>"><i class="fa fa-star"> </i> Starred  <span class="badge badge"><?=$starred; ?></span> </a></li>		
							<li><a href="<?=base_url().'notifications/view_all_notifications/member/'.$encoded.'/level/trash'; ?>"><i class="fa fa-trash"> </i> Trash  <span class="badge badge"><?=$trash; ?></span>   </a></li>							
							</ul>

						</div>
					</div>
				</div>
			 
			</div>
		</div>
<div class="span10">
			<div class="tabbable" id="tabs-987724">
				 
				<div class="tab-content">
					<div class="tab-pane active" id="panel-757130">
						<ul class="nav nav-tabs">
					<li class="active">
						<a href="#panel-757130" data-toggle="tab"><?=$tabtitle; ?> <i class="fa fa-bell"></i></a>
					</li>
					<li>
						<a href="#panel-250888" data-toggle="tab"></a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="panel-757130">
					     <?php
	 
	 switch($switch){
		 case 'single_notification':
		# print_r($rslt[0]['id']); exit();
		 $userid =  $this->session->userdata('userid'); 
		 $notification =  $rslt[0]['id'];
		 $status = "R";
		 update_status($userid,$notification,$status);
		 ?><div class="accordion in collapse" >
         <div class="accordion-group">
						<div class="accordion-heading">
							 <a class="accordion-toggle" href="#procurement_record" data-toggle="collapse" data-parent="#accordion-562508"><?= $rslt[0]['title']; ?> </a>
						</div>
						<div id="procurement_record" class="accordion-body   in">
							<div class="accordion-inner">
								 
								 <div class="row-fluid">
									<?= $rslt[0]['body']; ?>
										 	
								 </div>
								 
							</div>
						</div>
					</div>
                    </div>
         <?php
		 break;
		 case 'notifications':
		 ?>
		 
		 <table class="table  table-striped datatable">
				<thead>		<tr> <th>  </th>  <th>Title </th>  <th>DateAdded	</th></tr></thead>
				<tbody>
				 <?php 
				# print_r($page_list);
					 foreach ($page_list as $key => $value) { 
			 	    #  print_r($value);   
			 	  ?>
    <tr>	
    <td>  
    <script type="text/javascript">

    $(function(){

   


    	$(".delete_notification").click(function(){
    	  var idx =this.id;
    	  url = "<?=base_url().'notifications/del_archive'; ?>";
    	  formdata = {};
    	  formdata['id'] = idx; 
    	  formdata['type'] = 'archive'; 
    	  //console.log(formdata);
    	  var cfm = confirm("Your Are About to delete a notification");
    	  if(cfm == true)
    	   delete_url(formdata,url);

    	});
    	$(".star_notification").click(function(){
    	  var idx =this.id;
    	  url = "<?=base_url().'notifications/del_archive'; ?>";
    	  formdata = {};
    	  formdata['id'] = idx; 
    	  formdata['type'] = 'star'; 
    	  //console.log(formdata);
    	  delete_url(formdata,url);

    	});




    	function delete_url(formdata,url)
    	{
    		$('.alert').fadeIn('slow').html("Proccessing ...");
    		console.log(formdata);

		      $.ajax({
		        type: "POST",
		        url:  url,
		        data:formdata,
		        success: function(data, textStatus, jqXHR){
		          console.log(data);
		          if(data == 1)
		           location.reload(0);
		        },
		        error:function(data , textStatus, jqXHR)
		        {
		            alert(data);
		        }
   			 	});
    	}

    	 	$('.datatable').dataTable({
     "paging":   true,
        "ordering": true,
        "info":     true });
    	 	
    });
    </script>
     &nbsp;
     <?php
    // print_r($level);
      if($level == "trash")
      {}
  else
  {
  	#print_r($level)
?>
     <a href="#"  class="delete_notification" style="padding:5px; font-size:13px;" id="<?=$value['id']; ?>"><i class="fa fa-trash"> </i></a>
      <a href="#"  class="star_notification <?php if($level == 'starred') echo "starred"; ?>" style="padding:5px; font-size:13px;" id="<?=$value['id']; ?>"><i class="fa fa-star"> </i></a>
<?php }

?>
     
     &nbsp; 
     <a  title="<?=$value['statuss']; ?> Notification"class="<?=$value['statuss']; ?>"href="<?=base_url() . 'notifications/view_notification/notification/'.base64_encode(($value['id'])); ?>" style="float:left; ">  <i class='fa fa-envelope'> </i> 	 </a>
 	
						 
	</td><td>	<?=$value['title']; ?></td> 
		<td> <?= custom_date_format('D -d -m', $value['dateadded']); ?>	</td> <td></td></tr>
    <?php } ?>		</tbody>
    </table>
   <?php    break;  ?>
					 
		
	 
	 <?php } ?>
	 </div>
	 </div>
	 </div>
	 </div>
	 </div>
	 </div>
	 </div>