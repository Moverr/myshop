<?php
ob_start();

#**************************************************************************************
# All normal website pages that do not require login are directed from this controller
#**************************************************************************************

class Page extends CI_Controller {

    # Constructor
    function Page()
    {

        parent::__construct();

        $this->load->model('users_m','users');
        $this->load->model('sys_email','sysemail');
        #date_default_timezone_set(SYS_TIMEZONE);
        $this->load->model('Remoteapi_m');
         $this->load->model('Receipts_m');
        $this->load->model('procurement_plan_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('disposal_m','disposal');
        #$this->load->model('schedule_m');
        $this->load->model('contracts_m');
        $this->load->model('query_reader','query_reader');

        $this->load->model('usergroups_m');
        $this->load->model('role_m');
        $this->load->model('bid_invitation_m');
        $this->load->model('procurement_method_m');

    }


    # Default to home
    function index()
    {



        #Update Query List
        if(!empty($_GET['reason'] ))
        {
            $this->query_reader->load_queries_into_cache();
            #Go home
            exit("Query CACHE UPDATED ");
        }
        else if($_GET['info'])
        {
            phpinfo();
            exit();

        }else{}

        #Go home
        redirect('page/home');

    }







    # The home page
    function home()
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $this->load->view('public/home_v', $data);
    }

    #login page
    function login()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $this->load->view('login_v', $data);
    }

   

    #Function to create the catpcha word
    function create_captcha()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $vals = array(
            'img_path'   => './images/captcha/',
            'img_url'    => IMAGE_URL.'captcha/',
            'img_width'  => 150,
            'img_height' => 50
        );

        $cap = create_captcha($vals);

        $data = array(
            'captcha_id'    => '',
            'captcha_time'  => $cap['time'],
            'ip_address'    => $this->input->ip_address(),
            'word'          => $cap['word']
        );


        $this->db->query($this->Query_reader->get_query_by_code('insert_captcha_record', array('captcha_time'=>$data['captcha_time'], 'ip_address'=>$data['ip_address'], 'word'=>$data['word'])));

        $data['capimage'] = $cap['image'];
        $data['area'] = 'catpcha_image_view';

        $data = add_msg_if_any($this, $data);
        $this->load->view('incl/addons', $data);
    }





    #Function to show the contact us page
    function contact_us()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/contact_us_view', $data);
    }



    #Function to show the about us page
    function about_us()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/about_view', $data);
    }

    #Function to show the privacy policy
    function privacy_policy()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/privacy_policy_view', $data);
    }

    #Function to show the terms and conditions
    function terms_and_conditions()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $this->load->view('incl/terms_and_conditions_view', $data);
    }
 







}
