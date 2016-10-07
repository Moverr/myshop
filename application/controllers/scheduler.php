<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 5/19/2015
 * Time: 2:43 PM
 */
class Scheduler extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
        //load Models
        $this->load->model('schedule_m');



    }
}
