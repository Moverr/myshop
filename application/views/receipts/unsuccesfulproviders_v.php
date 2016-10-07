<?php


#print_r($unsuccesful_bidders);

			?>
					<table class="table table-striped" id="sample_1">
				<thead>
					<tr>
						<th>
							#
						</th>
						<th>
							Bidders Name
						</th>
						<th>
							Country of Origin 
						</th>
						<th>
							Reason
						</th>
						
						 


					</tr>
				</thead>
				<tbody>

					<?php
					$x = 0;
					foreach ($unsuccesful_bidders as $key => $value) {
						# code...
						$x ++;
						?>
						<tr id="<?=$value['receiptid'] ?>" dataid="<?=$value['receiptid'] ?>">
						<td>
						 <?= $x; ?> 
						</td>
						<td>
                        
                                        <?php 
                                       # print_r($value);
						
					//	print_r($value['providers']);
					//if (strpos($a,',') !== false)
	 	 
			$providers  = rtrim($value['providers'],",");
			#print_r($providers);
			#echo "<br/> :::: <br/>";
						//print_r($providers);
	 			$row = mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("".mysql_error());
	
		 
				$provider = "";
				$x = 0;
				while($record = mysql_fetch_array($row))
				{
					#	print_r($vaue);
					$provider  .= $record['providernames'].',';
					$x ++;
				}
				if($x > 1)
				{
				$prvider = rtrim($provider,' ,');
				print_r($prvider.'&nbsp; <span class="label label-info">Joint Venture</span>' );  
				}
				else
				{
				print_r(rtrim($provider,' ,'));					
				}
						 
					?>	
						
						</td>
						<td>
							 <?=$value['nationality'] ?> 
						</td>
						<td>
							<select class="span12 " data-placeholder="Nationality" tabindex="1" onChange="javascript:reason(this.value,<?=$value['receiptid'] ?>,'beb_<?=$x; ?>')">
							   <option value="0">Select Reason </option>
							   <option value="Administrative" <?=($value['reason'] =='Administrative') ?'selected' :'' ; ?> >Administrative</option>
                                 <option value="Technical" <?=($value['reason'] =='Technical') ?'selected' :'' ; ?> >Technical</option>
                                   <option value="Price" <?=($value['reason'] =='Price') ?'selected' :'' ; ?> >Price</option>											 
							 </select> 
							 <input type="text" value="<?=$value['reason_detail']; ?>" onChange="javascript:reasondetail(this.value,<?=$value['receiptid'] ?>);" class="span12" placeholder="Reason" id="beb_<?=$x; ?>">
						</td>
					</tr>
						<?php
					}

					?>
					


</table>