<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 10/15/14
 * Time: 7:53 AM
 */
class Active_plans extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();


    }

    //admin home page
    function index()
    {
        //view to load

        $data['title']='National Tender Portal';

        //pass it all to the template
        $this->load->view('public/active_plans/active_plan_v',$data);

    }

}
