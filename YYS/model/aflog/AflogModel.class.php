<?php


namespace model\aflog;

use model\Model;

class AflogModel extends Model
{
    #region 清空redis数据
    public function DelByRedis($clear="yes")
    {
        $rediskey="af_setting_reids";
        $redis = Model::iniRedis($type = 'redis', $dbname = 'db',array('db_id'=>0));
        if (isset($clear) && $clear=="yes") {
            $redis->del($rediskey);
            $afsettingData=Model::iniPdo()->getAll('select * from xy_sdk_overseas.af_setting');
            $redis->set($rediskey,json_encode($afsettingData));
        }
        return json_decode("删除成功");
    }
 #endregion

    #region 初始化redis数据
    public function GetSettingByRedis()
    {

        $rediskey="af_setting_reids";
        $redis = Model::iniRedis($type = 'redis', $dbname = 'db',array('db_id'=>0));
        $afsettingData=$redis->get($rediskey);
        if (empty($afsettingData)) {
            $afsettingData=Model::iniPdo()->getAll('select * from xy_sdk_overseas.af_setting');
            $redis->set($rediskey,json_encode($afsettingData));
        }
        return json_decode($afsettingData);
    }
    #endregion


    #region 记录信息类日志 InfoLog/ErrorLog

    public function InfoLog($log)
    {
        $file_name = 'infoLog_' . date("Y-m-d");
        $log = date("Y-m-d H:i:s") . '  ' . '收到请求的参数:  ' . $log . "\r\n";
        $filepath = dirname(dirname(dirname(__FILE__))) . '/logs/' . $file_name . '.log';
        file_put_contents($filepath, $log, FILE_APPEND);
    }

    /*
   * 记录异常信息类日志
   */
    public function ErrorLog($log)
    {
        $file_name = 'errorLog_' . date("Y-m-d");
        $log = date("Y-m-d H:i:s") . '  ' . '异常信息:  ' . $log . "\r\n";
        $filepath = dirname(dirname(dirname(__FILE__))) . '/logs/' . $file_name . '.log';
        file_put_contents($filepath, $log, FILE_APPEND);
    }
#endregion

    #region //写入redis日志
    public  function  PostRedisData($postdata)
    {
        $appsflyer_device_id = $postdata['appsflyer_device_id'];  //af用户id
        $event_type = $postdata['event_type']; //install
        $app_id = $postdata['app_id']; //af app_id
        $account_id = $postdata['account_id'] ? $postdata['account_id']:'';
        $distinct_id = $postdata['distinct_id'] ?$postdata['distinct_id']:'' ;
        if (isset($postdata['gameid'])) {
            $gameid = $postdata['gameid'];
        }else
        {
            //读取配置表中的对照表
           $rdata=$this->GetSettingByRedis();
            if (isset($rdata) && isset($app_id)) {
                foreach ($rdata as $key => $row) {
                    if (isset($row->ta_appid) && $row->ta_appid==$app_id) {
                        $gameid=$row->gameid;
                    }
                }
            }
        }

        $postdata['gameid'] = $gameid;
        //event_type
        if (!empty($event_type) && $event_type == "install") { //安装事件
            $data_track = array(
                "#account_id" => isset($account_id) ? $account_id : "", //用户角色id
                "#distinct_id" => isset($distinct_id) ? $distinct_id : $appsflyer_device_id, //咸鱼用户id
                "#type" => "track",
                "#ip" => $postdata['ip'],
                "#time" => date('Y-m-d H:i:s'),// "2017-12-18 14:37:28.527",
                "#event_name" => "install",
                "properties" => $postdata,
            );
        } else {

            //事件数据
            $data_track = array(
                "#account_id" => isset($account_id) ? $account_id : "", //用户角色id
                "#distinct_id" => isset($distinct_id) ? $distinct_id : $appsflyer_device_id, //咸鱼用户id
                "#type" => "track",
                "#ip" => $postdata['ip'],
                "#time" => date('Y-m-d H:i:s'),// "2017-12-18 14:37:28.527",
                "#event_name" => "activity",
                "properties" => $postdata,
            );
            //用户数据
            $data_user = array(
                "#account_id" => isset($account_id) ? $account_id : "", //用户角色id
                "#distinct_id" => isset($distinct_id) ? $distinct_id : $appsflyer_device_id, //咸鱼用户id
                "#type" => "user_set",
                "#ip" => $postdata['ip'],
                "#time" => date('Y-m-d H:i:s:s'),
                "#event_name" => "html",
                "properties" => $postdata,
            );

            // 写入redis
            //将日志写入redis队列的尾部
            try {

                $redis = Model::iniRedis($type = 'redis', $dbname = 'db',array('db_id'=>1));
                $redis->rPush(uniqid().time(), json_encode($data_track));
                if (isset($data_user)) {
                    $redis->rPush(uniqid().time(), json_encode($data_user));
                }
            } catch (Exception $e) {
                $this->ErrorLog($e);
                $this->InfoLog($data_track);
                if (isset($data_user)) {
                    $this->InfoLog($data_user);
                }
            }
            //如果写入redis失败，则直接生成log文件
            //  WriteLog($log);
        }
    }

    #endregion
}