<?php
ob_start();

#**************************************************************************************
# All FAQ's are handled through this controller
#**************************************************************************************


class Faqs extends CI_Controller {
	
	# Constructor
	function Faqs() 
	{	
		parent::__construct();
		$this->load->model('users_m','user1');
		$this->load->helper(array('form', 'url'));
		
		access_control($this);
	}
	
	#load all FAQ/Help information
	function help()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
		
		$search_str = '';
		
		//$data = paginate_list($this, $data, 'get_active_faqs', array('orderby'=>'datecreated ASC', 'searchstring'=>' AND 								isactive="Y"' . $search_str));
                $data['help_menu'] = $this->db->query('SELECT * FROM system_faqs WHERE isactive="Y" ORDER BY datecreated ASC')->result_array();
		
		$data = handle_redirected_msgs($this, $data);
		$data = add_msg_if_any($this, $data);
		
		$data['page_title'] = 'Help Information';
		$data['current_menu'] = 'help-link';
		$data['view_to_load'] = 'faqs/list_help';
		$data['search_url'] = 'faqs/search_faqs';
		$data['form_title'] = $data['page_title'];
        
		$this->load->view('dashboard_v', $data);
	}
	
	#Search Help Section
	function search_menu()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
		
		$searchtext = '';
		if(!empty($_POST['searchtext']))
		$searchtext = mysql_real_escape_string($_POST['searchtext']);		
		$data['help_menu'] = $this->db->query('SELECT * FROM system_faqs WHERE isactive="Y" AND faq_topic like "%'.$searchtext.'%" ORDER BY faq_topic ASC')->result_array();
		#print_r($data['help_menu']);
		
		$data['area'] = 'helpsectionmenu';
        $this->load->view('includes/add_ons', $data);	 
	}
	
	#Add Help/FAQ 
	function add_help()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
			
		$data = add_msg_if_any($this, $data);
		
		if($this->input->post('save'))
		{
			$required_fields = array('topic', 'header', 'description');
			
			$data['formdata'] = $_POST;
			if(empty($_POST['topic']))
			{
				$validation_results['bool'] = FALSE;
				$validation_results['requiredfields'] = 'topic';
			}
			else
			{
				$_POST = clean_form_data($_POST);
				$validation_results = validate_form('', $_POST, $required_fields);
			}

			#Only proceed if the validation for required fields passes
			if($validation_results['bool'])
			{
				#Check for duplicates in the system
				$duplicate_help_section = $this->Query_reader->get_query_by_code('search_help_sections', array('table'=>'system_faqs', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' faq_topic="'. $_POST['topic'].'" AND isactive="Y"'));;
				$help_section_query_result = $this->db->query($duplicate_help_section);
								
				if($help_section_query_result->num_rows() < 1){
					
					//image manipulation
					$config['upload_path'] = './uploads/backgrounds/';
					$config['allowed_types'] = 'jpg|png';
					$config['max_size'] = 1024 * 8;
					$config['encrypt_name'] = TRUE;
					 
					$this->load->library('upload', $config);
		 
					if(!$this->upload->do_upload())
					{
						$result = $this->db->query("INSERT INTO system_faqs (faq_topic, faq_header, faq_description, author) VALUES('".$this->input->post('topic')."', '".$this->input->post('header')."', '".$this->input->post('description')."', '".$this->session->userdata('userid')."')");	
					}
					else
					{
						$data = $this->upload->data();
						$result = $this->db->query("INSERT INTO system_faqs (faq_topic, faq_header, faq_description, faq_image, author) VALUES('".$this->input->post('topic')."', '".$this->input->post('header')."', '".$this->input->post('description')."', '".$data['file_name']."', '".$this->session->userdata('userid')."')");	
					}
					
				}
				else
				{
					$data['msg'] = "ERROR: Help section with details already exists.";
				}

				#exit($this->db->_error_message());
				
				#Format and send the errors
				if(!empty($result) && $result){
					$this->session->set_userdata('usave', "Help section details have been successfully saved.");
					redirect("faqs/list_all_help/m/usave");
				}
				else if(empty($data['msg']))
				{
					$data['msg'] = "ERROR: Help section details could not be saved or were not saved correctly.";
				}

			}
			# End validation

			if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg']) )
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
            }
			
            $data['requiredfields'] = $validation_results['requiredfields'];
		}

		$data['page_title'] = (!empty($data['i'])? 'Addt Help Section' : 'Help Section');
		$data['current_menu'] = 'add-help-link';
		$data['view_to_load'] = 'faqs/add_help_form';
		$data['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}
	
	//Show selected help section
	function load_section()
	{
		if($this->input->post('section_id')){
			
			# Get the passed details into the url data array if any
			$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
			# Pick all assigned data
			$data = assign_to_data($urldata);
			
			$data = add_msg_if_any($this, $data);
			
			$data = handle_redirected_msgs($this, $data);
			
			$data = add_msg_if_any($this, $data);
		
			$data = handle_redirected_msgs($this, $data);
			
			//print_array($_POST);
			
			#assign sent data id
			$section_id = $this->input->post('section_id');
			$data = paginate_list($this, $data, 'get_section_details', array('orderby'=>'datecreated ASC', 'searchstring'=>' AND id="'.$section_id.'"'));
			
			//print_array($data);
			
			$data['area'] = 'help_section';
			$this->load->view('includes/add_ons', $data);
		}
	}
	
	//Show all available help sections
	function list_all_help()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
		
		$search_str = '';
		
		$data = paginate_list($this, $data, 'get_active_faqs', array('orderby'=>'datecreated ASC', 'searchstring'=>' AND 								isactive="Y"' . $search_str));
		
		$data = handle_redirected_msgs($this, $data);
		$data = add_msg_if_any($this, $data);
		
		$data['page_title'] = 'Help Information';
		$data['current_menu'] = 'help-link';
		$data['view_to_load'] = 'faqs/show_help';
		$data['search_url'] = 'faqs/search_faqs';
		$data['form_title'] = $data['page_title'];
        
		$this->load->view('dashboard_v', $data);
	}
	
	# Search FAQs
	function search_faqs()
	{
			    # Get the passed details into the url data array if any
				$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
				# Pick all assigned data
				$data = assign_to_data($urldata);		
					
				$search_str = $_GET['search']['value'];
				$search_string  = '';				 
		
		
		$searchme = ' AND (faq_topic like "%'.$search_str.'%"  OR  faq_header like "%'.$search_str.'%"  )';
	
		$data = paginate_list($this, $data, 'get_active_faqs', array('orderby'=>'datecreated ASC', 'searchstring'=>' and 1 = 1'.$searchme ),200);
			    
				
		$data['area'] = 'faqs_search';		
		$this->load->view('includes/add_ons', $data);
				
	}
	
	//Delete help section
	function delete_help()
	{
		#check user access
		//check_user_access($this, 'delete_help', 'redirect');
				
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
			
		if(!empty($data['i'])){
			$result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'system_faqs', 'id'=>decryptValue($data['i'])) ));
			
			$this->session->set_userdata('dbid', "The help section has been successfully deleted.");
		}
		else if(empty($data['msg']))
		{
			$this->session->set_userdata('dbid', "ERROR: The help section details could not be deleted or were not deleted correctly.");
		}
		
		redirect(base_url()."faqs/list_all_help/m/dbid/");
	}
	
	//Editing function
	function edit_help_section()
	{ 
        # Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if($this->input->post('cancel'))
		{		
			redirect("faqs/list_all_help");
		}
		else if($this->input->post('save'))
		{
			$data['helpdetails'] = $_POST;		
            $required_fields = array('topic', 'header', 'description');
			$_POST = clean_form_data($_POST);
			$validation_results = validate_form('', $_POST, $required_fields);
			
			#Only proceed if the validation for required fields passes
			if($validation_results['bool'])
			{
				if(!empty($data['i']))
                {
					$helpid = decryptValue($data['i']);
					
					$data['msg'] = '';
					
					if(empty($data['msg']))
					{
						//image manipulation
						$config['upload_path'] = './uploads/backgrounds/';
						$config['allowed_types'] = 'jpg|png';
						$config['max_size'] = 1024 * 8;
						$config['encrypt_name'] = TRUE;
						 
						$this->load->library('upload', $config);
			 
						if(!$this->upload->do_upload())
						{
							$result = $this->db->query("UPDATE system_faqs SET faq_topic = '".$this->input->post('topic')."', faq_header = '".$this->input->post('header')."', faq_description = '".$this->input->post('description')."' WHERE id = '".$helpid."'");	
						}
						else
						{
							$data = $this->upload->data();
							$result = $this->db->query("UPDATE system_faqs SET faq_topic = '".$this->input->post('topic')."', faq_header = '".$this->input->post('header')."', faq_description = '".$this->input->post('description')."', faq_image = '".$data['file_name']."' WHERE id = '".$helpid."'");	
						}
						
						$this->session->set_userdata('usave', "Help section details have successfully been edited.");
						redirect("faqs/list_all_help/m/usave");
					}
					
					if(empty($result))
					{
						$data['msg'] = "ERROR: There was an error editing the help section.";
					}
				}
				
			}
		}
		
		if(!empty($data['i']))
		{
			$help_id = decryptValue($data['i']);
			$data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'system_faqs', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $help_id .'" AND isactive="Y"'));
								
			#get help section details
			if(!empty($data['formdata']['id']))
			{                        
				$helpid = decryptValue($data['i']);
				$data['formdata'] = $this->Query_reader->get_row_as_array('get_help_by_id', array('id'=>$helpid ));
			}
			else
			{
				$data['msg'] = "ERROR: There was an error editing the help section.";
			}
		}
		
		#exit($this->db->last_query());
		$data['page_title'] = (!empty($data['i'])? 'Edit help section details' : 'Help Section');
		$data['current_menu'] = 'list-help-link';
		$data['view_to_load'] = 'faqs/edit_help_form';
		$data['form_title'] = $data['page_title'];

		$this->load->view('dashboard_v', $data);
	}
	
}

?>