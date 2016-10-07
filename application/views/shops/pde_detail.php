
<?php
if((!empty($pdes))  && (count($pdes) > 0))
{


	foreach($pdes as $row)
	{
?>


 <div class="row clearfix">
		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Name : </label>
		  <span class="semi-bold"> <?=$row['pdename']; ?> </span>
		 </div>
		</div>
		<div class="col-md-4 column">
		 <div class="grid simple">
		 
		 <label>Abbreviation : </label>
		  <span class="semi-bold"> <?=$row['abbreviation']; ?>  </span>
		 </div>
		</div>
		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Category : </label>
		  <span class="semi-bold"> <?=$row['category']; ?>  </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Code : </label>
		  <span class="semi-bold"> <?=$row['code']; ?> </span>
		 </div>
		</div>


		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Status : </label>
		  <span class="semi-bold">  <?=($row['status']=='in')?'Active':'INACTIVE'; ?>  </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Date Created : </label>
		  <span class="semi-bold"> <?=$row['create_date']; ?> </span>
		 </div>
		</div>

<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Created By  : </label>
		  <span class="semi-bold"> <?=$row['created_by']; ?>[Pending Inner Join with Users table ] </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>PDE Roll Category : </label>
		  <span class="semi-bold"> <?=$row['pde_roll_cat']; ?> </span>
		 </div>
		</div>


		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Tel : </label>
		  <span class="semi-bold"> <?=$row['tel']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Fax : </label>
		  <span class="semi-bold"> <?=$row['fax']; ?> </span>
		 </div>
		</div>


		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Email : </label>
		  <span class="semi-bold"> <?=$row['email']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Website : </label>
		  <span class="semi-bold"> <?=$row['website']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>AO : </label>
		  <span class="semi-bold"> <?=$row['AO']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>AO Phone : </label>
		  <span class="semi-bold"> <?=$row['AO_phone']; ?> </span>
		 </div>
		</div>
		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>AO Email : </label>
		  <span class="semi-bold"> <?=$row['AO_email']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>CC : </label>
		  <span class="semi-bold"> <?=$row['CC']; ?> </span>
		 </div>
		</div>
		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>CC Phone : </label>
		  <span class="semi-bold"> <?=$row['CC_phone']; ?> </span>
		 </div>
		</div>

		<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>CC Email : </label>
		  <span class="semi-bold"> <?=$row['CC_email']; ?> </span>
		 </div>
		</div>


<div class="col-md-4 column">
		 <div class="grid simple ">
		 

		 <label>Head PDU : </label>
		  <span class="semi-bold">  <?=$row['head_PDU']; ?> </span>
		 </div>
		</div>

<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Head PDU Phone : </label>
		  <span class="semi-bold">  <?=$row['head_PDU_phone']; ?> </span>
		 </div>
		</div>


<div class="col-md-4 column">
		 <div class="grid simple">
		 

		 <label>Head PDU Email: </label>
		  <span class="semi-bold">  <?=$row['head_PDU_email']; ?> </span>
		 </div>
		</div>

	</div>
<?php 
		}
	}
 ?>