<?php

    if(mysqli_num_rows($suspendedlist) > 0 ):
	 
	 	$server_time = substr(date('Y-m-d'), 0,10);
		
		while($row = mysqli_fetch_array($suspendedlist))
        {
			//Pick all providers whose suspension date is same as server date and are not suspended indefinately
			if($row['indefinite'] == 'N'):
				
				//Check date against current server time
				if(substr($row['endsuspension'], 0,10) == $server_time):
				
						//loop through users
						foreach($user_mails as $user_details)
						{
							//Compose email
							$to = $user_details['emailaddress'];
							$subject = 'PROVIDER END OF SUSPENSION';
							$content = 'This is to inform you that the following suspended provider(s): '.$row['orgname'].' date of suspension has ended on '.$row['endsuspension'];
							$from = 'noreply@gpp.ppda.go.ug';
							
							//send email to administrators
							$success = send_html_email_no_template($to, $subject, $content, $from, $cc = '');
							if($success):
								print '<h1>EMAIL SENT</h1>';
							endif;
							
						}
						
				endif;
				
			endif;
		}
	 else:
		echo 'No connection to suspended providers';
	 endif;
