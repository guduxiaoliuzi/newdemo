<?php
/**
 *	@基类控制器
 *	@AddBy:2018-05-09 Liaozz
 */
class MY_Controller extends CI_Controller
{
    public $userInfo=false;
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('User_Model','user');
        // $this->load->helper('url');
        // $this->userInfo=$this->user->getUserInfo();
    }
    /**
     *	@判断用户权限
     */
    public function checkPurview($accessUrl='', $type='json')
    {


        //登录云平台的用户ID
        $uid=$this->userInfo['uid'];
        //该用户的权限ID
        $rid=$this->userInfo['rid'];
        if($uid)
        {
            //是否为超管
            if(in_array($rid,$this->config->item('super_role_id')))
            {
                return true;
            }

            if($accessUrl)
            {
                $accessUrl  = explode('?', $accessUrl);
                $accessUrlLog=$accessUrl;
                $mod_fun_id = $this->config->item('mod_fun_id');
                $mod_fun_id = @$mod_fun_id[$accessUrl['0']];
                $result = $this->checkUserModFun($uid, $mod_fun_id,$accessUrlLog);

            }
            else
            {
                //未传入权限URL
                if($type=='json')
                {
                    echo json_encode(array('flag'=>'1','code'=>'99','info'=>'访问地址出错！'));
                    exit;
                }
                else
                {
                    echo '访问地址出错，请原路<a href="'.base_url().'">返回&gt;&gt;&gt;</a>';
                    exit;
                }
            }
        }
        else
        {
            //登录用户处理
            if($type=='json')
            {
                echo json_encode(array('flag'=>'1','code'=>'99','info'=>'不支持游客访问！'));
                exit;
            }
            else
            {
                echo '不支持游客访问，请原路<a href="'.base_url().'">返回&gt;&gt;&gt;</a>';
                exit;
            }
        }
    }

    /**
     * checkUserModFun 判断模块功能权限
     *
     * @access private
     * @param int $uid 用户ID
     * @param int $mod_fun_id 模块_功能ID
     * @return string
     */
    public function checkUserModFun($uid, $mod_fun_id,$accessUrlLog='')
    {

        $client=new SoapClient(WSDL_MOD_FUN);
        try
        {
            $result=$client->get_user($uid);

        }
        catch(Exception $e)
        {
            return '-1|云平台权限接口服务错误！';
        }
        if ($result == '1|用户正常访问')
        {
            if (@strpos($mod_fun_id, ','))
            {
                try
                {
                    $fun_id = explode(",", $mod_fun_id);
                    $result = $client->get_function_info($uid, $fun_id[0], $fun_id[1]);

                }
                catch(Exception $e)
                {
                    return '-1|云平台权限接口服务错误！';
                }

            }
            else
            {
                try
                {
                    $result = $client->get_module_info($uid, $mod_fun_id);

                }
                catch (Exception $e)
                {
                    return '-1|云平台权限接口服务错误！';
                }
            }
        }
    }//End checkUserModFun
    /**
     * @ send_mail 发送邮件
     *
     * @param array $mailinfo 邮件信息
     * @return mixed
     */
    public function send_mail($mailinfo) {
        $sendmail = &load_class('Sendomail');
        $email    = str_replace('，', ',', $mailinfo['mailto']);//全角转半角
        $email    = explode(',', $mailinfo['mailto']);
        $true     = true;

        if(count($email)>1) {
            foreach($email as $k=>$v) {
                if(!check_str($v, 'email')) {
                    unset($email[$k]);
                }
            }
            if(count($email)<1)
                $true = false;
        }
        else
        {
            if(!check_str($mailinfo['mailto'], 'email')) {
                $true = false;
            }
        }

        if(!$true) {
            return $true;
        }
        $mailinfo['mailto'] = join(',', $email);
        $true = $sendmail->send($mailinfo);
        error_log(date('H:i:s').'|'.$true.'|'.serialize($mailinfo).'|'.PHP_EOL, 3, LOG_PATH.'/send_mail_'.date('Ymd').'.log');
        return $true;
    }
    /**
     *	@根所登录用户返回登录部门的ID
     */
    public function getUserDepart($uid)
    {
        $client=new SoapClient(WSDL_MOD_FUN);
        return $client->get_userinfo($uid);
    }
    /**
     * checkUserModFun 获取部门的用户列表
     * $uid 当前登陆的用户userid
     * $departMentID 部门ID
     */
    public function getDepartUserList($uid,$departMentID)
    {

        $client=new SoapClient(WSDL_MOD_FUN);
        try
        {
            $result=$client->get_user($uid);

        }
        catch(Exception $e)
        {
            $rs = array('errno'=>100001,'data'=>'云平台权限接口服务错误！');
        }
        if ($result == '1|用户正常访问')
        {
            $rs = $client->get_department_user($departMentID);
            $rs = json_decode($rs,true);
            if(!is_array($rs)) $rs = array('errno'=>100002,'data'=>'调用接口错误');
            if(count($rs['data']) < 1) $rs = array('errno'=>100006,'data'=>'没有查到该部门用户信息');
        }else{
            $rs = array('errno'=>100003,'data'=>'未授权访问');
        }
        return $rs;
    }
}
?>