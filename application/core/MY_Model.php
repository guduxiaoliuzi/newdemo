<?php
class MY_Model extends CI_Model
{
    //定义缓存
    protected $cache;
    //定义Socket
    protected $socket;
    //定义重库
     public $db;	
    // public $db3;
    // public $db4;
    public function __construct()
    {
        parent::__construct();
        
        $this->db = $this->load->database('default', TRUE);									//载入数据库
//			$this->cache=&load_class('Cnfol_Cache');					//载入缓存
        // $this->socket=&load_class('Cnfol_Socket');					//载入SOCKET
//         $this->db2 = $this->load->database('additional', TRUE);		//财视后台读从库
        //$this->db3=$this->load->database('passport',TRUE);			//同步用户中心实名从库
        //$this->db4=$this->load->database('gift',TRUE);				//同步用户中心收益认证
    }
}
?>