<?php

class Sys_email extends CI_Model {
	
	#Constructor
	function Sys_email()
	{
		parent::__construct();
		$config_email = array();
		$config_email['mailtype'] = 'html';
		$config_email['protocol'] = 'mail';
		$config_email['charset']   = 'utf-8';
		$config['send_multipart'] = FALSE;
		$this->load->library('email', $config_email);
	}
	
	#Fucntion to email data from a form
	function email_form_data($urldata, $formdata, $emailtype='')
	{
		#use defaults if there are no emails given
		if(!empty($urldata['fromemail']))
		{
			if(empty($urldata['fromname'])){
				$fromname = $urldata['fromemail'];
			} else {
				$fromname = $urldata['fromname'];
			}
			$this->email->from($urldata['fromemail'], $fromname);
			$this->email->reply_to($urldata['fromemail'], $fromname);
		}
		else
		{
			$this->email->from($this->session->userdata('emailaddress'), $this->session->userdata('names'));
			$this->email->reply_to($this->session->userdata('emailaddress'), $this->session->userdata('names'));
		}
		
		$this->email->to($formdata['emailto']);
		if(!empty($formdata['emailcc']) && trim($formdata['emailcc']) != '')
		{
			$this->email->cc($formdata['emailcc']);
		}
		if(!empty($formdata['emailbcc']) && trim($formdata['emailbcc']) != '')
		{
			$this->email->bcc($formdata['emailbcc']);
		}
		
		$this->email->subject($formdata['subject']);
		$this->email->message($formdata['message']);
		
		if(isset($formdata['fileurl']) && trim($formdata['fileurl']) != '')
		{
			$this->email->attach($formdata['fileurl']);
		}
		
		$from = 'From: info@itp.nwtdemos.com'; 
		$to = 'votim@newwavetech.co.ug'; 
		$subject = 'Mail test'; 
		$body = 'Hello, this is a test'; 

		if (mail ($to, $subject, $body, $from)) { 
			echo 'MAIL - OK'; } else { 
		echo 'MAIL FAILED'; 
		} 
		    				
		return $this->email->send();
		
		#echo $this->email->print_debugger() . $formdata['emailto'];
		
		#exit();
	}
	
	
	
	
	#function to get the recipients of a given message
	function get_msg_recipientids($msgid)
	{
		$recipients = array();
		$received_by = $this->db->query($this->Query_reader->get_query_by_code('get_recipients_for_msg', array('messageid'=>$msgid )));
		$recipients_rows = $received_by->result_array();
		foreach($recipients_rows AS $row)
		{
			array_push($recipients, $row['userid']);
		}
			
		return $recipients;
	}
}

?>