<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Public_holiday extends CI_Controller 
{
	#Constructor to set some default values at class load
	public function __construct()
    {
        parent::__construct();
        $this->load->model('_public_holiday');
	}
	
	# manage home page
	function lists ()
	{
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));
		
		# Pick all assigned data
        $data = assign_to_data($urldata);
		
		$data['current_list_page'] = 1;
		
		if(!empty($data['d'])) $data['current_list_page'] = $data['d'];
		
		$data['list'] = $this->_public_holiday->get_list(array('searchstring'=>'PH.isactive="Y"'));
				
		$data['page_title'] = 'Manage public holidays';
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'public_holiday/list_holidays';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
	}
	
	
	#Function to load public holiday form
    function load_form ()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #check user access
        #1: for editing
        if (!empty($data['i'])) {
            check_user_access($this, 'view_bid_invitations', 'redirect');
        } #2: for creating
        else {
            check_user_access($this, 'view_bid_invitations', 'redirect');
        }

        if ($this->session->userdata('isadmin') == 'N') $userdetails = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();

        #exit($this->db->last_query());

        #user is editing
        if (!empty($data['i'])) {
            $holiday_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table' => 'public_holidays', 'limittext' => '', 'orderby' => 'id', 'searchstring' => ' id="' . $holiday_id . '" AND isactive="Y"'));
        }

        $data['page_title'] = (!empty($data['i']) ? 'Edit public holiday' : 'Add public holiday');
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'public_holiday/form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }


    #Function to save a holiday
    function save_holiday()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #check user access
        #1: for editing
        if (!empty($data['i'])) {
            check_user_access($this, 'view_bid_invitations', 'redirect');
        } #2: for creating
        else {
            check_user_access($this, 'view_bid_invitations', 'redirect');
        }


        if (!empty($_POST['save_public_holiday'])) {
            $required_fields = array('title', 'holiday_date');

            $_POST = clean_form_data($_POST);

            $validation_results = validate_form('', $_POST, $required_fields);

            #Only proceed if the validation for required fields passes
            if ($validation_results['bool']) {
                
				#check if the public holiday exists
                $similar_holiday = $this->db->query($this->Query_reader->get_query_by_code('search_table', array('table'=>'public_holidays', 'orderby'=>'id', 'limittext'=>'', 'searchstring' => ' title = "'. $_POST['title'] .'" AND holiday_date ="'. $_POST['holiday_date'] .'" AND isactive="Y"')))->result_array();

                if (!empty($similar_holiday)) {
                    $data['msg'] = "WARNING: An similar public holiday already exists on the same date";
                
				} else {

                    if (!empty($data['i'])) {
						
						$_POST['editid'] = $data['i'];
                    	$result = $this->_public_holiday->update($_POST);

                    } else {
						$result = $this->_public_holiday->add($_POST);
                    }
                }

                #Holiday has been added successfully
                if (!empty($result['boolean']) && $result['boolean']) {
                   
                    $data['msg'] = "SUCCESS: The public holiday details have been saved.";
                    $this->session->set_userdata('sres', $data['msg']);


                    redirect('public_holiday/lists/m/sres' . ((!empty($data['b'])) ? "/b/" . $data['b'] : ''));

                } else if (empty($data['msg'])) {
                    $data['msg'] = "ERROR: The public holiday details could not be saved or were not saved correctly.";
                }
            }


            if ((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool']))
                && empty($data['msg'])
            ) {
                $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields'];

            }

        }

        $data['formdata'] = $_POST;

        #exit($this->db->last_query());

        $data['page_title'] = (!empty($data['i']) ? 'Edit public holiday' : 'Add public holiday');
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'public_holiday/form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }


    #Function to delete an addenda
    function delete()
    {
        #check user access
        check_user_access($this, 'view_bid_invitations', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if (!empty($data['i'])) {
            $result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'public_holidays', 'id'=> decryptValue($data['i']))));
        }

        if (!empty($result) && $result) {
            $this->session->set_userdata('dbid', "The public holiday details have been successfully deleted.");
        } else if (empty($data['msg'])) {
            $this->session->set_userdata('dbid', "ERROR: The public holiday details could not be deleted or were not deleted correctly.");
        }

        redirect(base_url() . "public_holiday/lists/m/dbid");
    }	
	
}

/* End of controller file */