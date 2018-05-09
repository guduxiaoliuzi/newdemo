<?php
class News_model extends MY_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getUser(){
        $sql = 'select * from user';
        $query = $this->db->query($sql);

        $data = $query->result_array();
        return $data;
    }
    /**
     *	数据的导入
     */
    public function processData()
    {

        $sql="SELECT UserID FROM tbPassportUserAuthenticate";
        $query=$this->db3->query($sql);
        $result=$query->result_array();
        foreach($result as $key=>$value)
        {
            $userID=$value['UserID'];
            $sql="SELECT UserID FROM tbPassportUserAuthenticate WHERE UserID=".$userID;
            $qry=$this->db2->query($sql);
            $rs=$qry->result_array();
            if(empty($rs))
            {
                $this->db3->query('set names latin1');
                $sql="select UserID,TrueName,IdentityNumber,IdentityPicFront,IdentityPicBack,Status,CheckTime,CheckType,
					StartValidDate,EndValidDate FROM tbPassportUserAuthenticate WHERE UserID=".$userID;
                $query=$this->db3->query($sql);
                $result=$query->result_array();
                $insert_data=array(
                    'UserID'=>$result[0]['UserID'],
                    'TrueName'=>$result[0]['TrueName'],
                    'IdentityNumber'=>$result[0]['IdentityNumber'],
                    'IdentityPicFront'=>$result[0]['IdentityPicFront'],
                    'IdentityPicBack'=>$result[0]['IdentityPicBack'],
                    'Status'=>$result[0]['Status'],
                    'CheckTime'=>$result[0]['CheckTime'],
                    'CheckType'=>$result[0]['CheckType'],
                    'StartValidDate'=>$result[0]['StartValidDate'],
                    'EndValidDate'=>$result[0]['EndValidDate']
                );

                $result=$this->db->insert('tbPassportUserAuthenticate',$insert_data);
                echo 'insert_userID:'.$userID.'--result--'.$result.'<br/>';
            }
        }

    }
    /**
     *	@礼物竞换功能
     */
    public function giftExchange($param)
    {

        $where =" ";
        $sql="select b.UserName,b.NickName,b.ExchangeMoney,b.DataTime,b.status FROM tbGiftExchangeRecord a,tbPassportUser base64_decode
			where a.UserID=b.UserID";

        $countsql="select count(*) as Total,Sum(b.ExchangeMoney/100) as totalMoney FROM tbGiftExchangeRecord a,tbPassportUser
			where a.UserID=b.UserID";
        if(!empty($param['username']))
        {
            $username=$param['username'];
            $where =" AND b.UserName='$username'";
            unset($username);
        }
        if(!empty($param['nickname']))
        {
            $nickname=$param['username'];
            $where =" AND b.NickName='$nickname'";
            unset($nickname);
        }
        if(!empty($param['starttime']))
        {
            $starttime=$param['starttime'];
            $where =" AND a.DataTime>='$starttime'";
            unset($starttime);
        }
        if(!empty($param['endtime']))
        {
            $endtime=$param['endtime'];
            $where =" AND a.DataTime<='$endtime'";
            unset($endtime);
        }
        if(!empty($param['status']))
        {
            if($param['status']==1) $param['status']=0;
            if($param['status']==3) $param['status']=2;
            $where .=" AND a.Status=".$param['status'];
        }
        $sql.=$where;
        $countsql.=$where;

        $offset=$data['pageOffset'];
        $pageSize=$data['pageSize'];

        $sql.=$where;


        $sql2.=$where;

        $sql.=" order by a.ID DESC LIMIT $offset,$pageSize ";
        $query = $this->db2->query($sql);
        $data = $query->result_array();



        $query1 = $this->db2->query($sql2);
        $data1 = $query1->result_array();
        unset($sql,$sql2);
        return array_merge_recursive($data,$data1);
    }
    /**
     *	@修改兑换状态
     */
    public function changeStatus($param)
    {
        $data = array(
            'Status' => $param['status'],
            'Remark' => $param['remark'],
            'CheckTime'=>date('Y-m-d H:i:s')
        );

        $this->db->where('ID', $param['id']);
        $result=$this->db->update('tbGiftExchangeRecord', $data);
        if(!$result)
        {
            $data=array('flag'=>1,'msg'=>'审核失败');
            return $data;
        }
        error_log(date('Y-m-d H:i:s').'-changeStatus-rs-'.$result.'-id-'.$param['id'].PHP_EOL,3,LOG_PATH.'changeLog'.date('Y-m-d').'.log');

        $sql="select DataTime,UserID, ExchangeMoney from tbGiftExchangeRecord where ID=".$param['id'];
        $query = $this->db2->query($sql);
        $rs=$query->result_array();
        $userID=$rs[0]['UserID'];
        $time=substr($rs[0]['DataTime'],0,7).'-01 00:00:00';



        //查询该用户在当月是否有收益记录，如果没有的话，暂时不将数据插入。
        $sql="SELECT UserID,ThirdReveID FROM tbThirdAgentReveFromOrderRecord WHERE UserID=$userID AND ReveStartTime='$time' ";
        $query1=$this->db2->query($sql);
        $rs1=$query->result_array();
        if(empty($rs[0]['UserID'])){
            $data=array('flag'=>1,'msg'=>'当月还没有生成收益打款记录');
            unset($rs,$rs1,$query,$query1);
        }else{
            $param=array(
                'GiftExchangeMoney'=>$rs[0]['ExchangeMoney']
            );
            $this->db->where('ThirdReveID',$rs1[0]['ThirdReveID']);
            $rs2=$this->db->update('tbThirdAgentReveFromOrderRecord', $data);
            unset($rs,$rs1,$query,$query);
            error_log(date('Y-m-d H:i:s').'-insertReveue-rs-'.$rs2.'-id-'.$rs1[0]['ThirdReveID'].PHP_EOL,3,LOG_PATH.'changeLog'.date('Y-m-d').'.log');
            $data=array('flag'=>1,'已经转到收益打款');
        }
        return $data;
    }
    public function list_news(){
        $this->$db = $this->load->database();
        $sql = 'select * from dr_member_scorelog limit 10;';
        echo $sql;exit;
        $query = $this->db->query($sql);
        $data = $query->result_array();
        return $data;
    }
}
?>