<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 11:35
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class News extends MY_Controller{
    public function index()
    {
        $this->load->view('welcome_message');
    }
    public  function add(){
//        isset();
        $this->load->view('admin/login.html');
//        echo APPPATH;
    }
}
