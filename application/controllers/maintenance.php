<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 10/15/14
 * Time: 7:53 AM
 */
class Maintenance extends CI_Controller
{


    //admin home page
    function index()
    {
        $data['title']='National Tender Portal';

        $this->load->view('public/maintenance_v', $data);

    }

}