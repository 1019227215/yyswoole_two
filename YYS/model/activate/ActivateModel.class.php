<?php

/**
 * Created by PhpStorm.
 * User: chriswang
 * Date: 2018/11/20
 * Time: 11:09
 */
namespace model\activate;

use model\Model;

class ActivateModel extends Model
{
    public function Index()
    {
        return Model::iniPdo()->getAll('select * from xy_sdk_overseas.af_setting');
    }

    public function CreateActivateLog($gameid, $type, $data)
    {


        //从redis内取出对应AF配置表内的param
        try {
            if (Model::iniRedis()->exists($gameid . '_' . $type)) {
                $config = Model::iniRedis()->get($gameid . '_' . $type);

                if (!empty($config)) {
                    $model = json_decode($config, true);
                    $params = $model[0]['param'];
                    $ta_appid = $model[0]['ta_appid'];
                }
            }


        } catch (Exception $e) {
            $this->ErrorLog($e);
        }

        /* $params = array(
             'gameid' => 'gameid',
             'idfa' => 'idfa',
             'uid' => 'uid',
             'sn' => 's1'
         );*/

        //redis内取出为空，则从数据库内取
        if (!isset($params)) {

            $model = Model::iniPdo()->getAll(" select * from  xy_sdk_overseas.af_setting  where gameid={$gameid} and event_type='" . $type . "' and stype='SDK' and status=1 ");

            if (isset($model)) {
                $params = $model[0]['param'];
                $ta_appid = $model[0]['ta_appid'];

                Model::iniRedis()->set($gameid . '_' . $type, json_encode($model));
            }
        }


        //获取需要生成日志的对应字段值
        $entry = array_intersect_key($data, $params);

        if (empty($entry)) {
            return;
        }

        //生成Logbus的日志规则
        $log['#account_id'] = $entry['roleid'];
        $log['#distinct_id'] = $entry['uid'];
        $log['#type'] = 'track';
        $log['#ip']=$entry['ip'];
        $log['#time'] = date("Y-m-d H:i:s", $entry['servertime']);
        $log['#event_name'] = 'sdk_avtivate';
        //自定义属性
        $log['properties'] = $entry;
        //添加该LOG对应的新经分的appid(用来区分对应的新经分的appid)，请在redis队列取出后 删除该属性，保证上报数据规则的正确
        $log['ta_appid'] = $ta_appid;

        //将日志写入redis队列的尾部
        try {
            Model::iniRedis()->rPush('activate_log', json_encode($log));
        } catch (Exception $e) {
            $this->ErrorLog($e);

            //如果写入redis失败，则直接生成log文件
            WriteLog($log);
        }


        // return Model::iniRedis()->lLen('activate_log');//测试用


    }

    /*
     * 记录信息类日志
     */
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

    /*
     * 直接生成LOG文件
     */
    public function WriteLog($log)
    {

    }
}