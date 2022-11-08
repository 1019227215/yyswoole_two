<?php

namespace controller\aflog;

use controller\Controller;
use model\aflog\AflogModel;

class IndexController extends Controller
{

    public function actionCheckpart()
    {
        $postdata = $this->request;
        $clear="no";
        if (isset($postdata["clear"]) && $postdata["clear"]=="yes") {
            $clear="yes";
        }
        $amodel=new  AflogModel();
        $rmodel=$amodel->DelByRedis($clear);
//        $rmodel=$amodel->GetSettingByRedis2();
        return json_encode("删除成功");
    }

    public function actionGetRedisModel()
    {
//        $amodel=new  AflogModel();
//        $rmodel=$amodel->GetSettingByRedis();
////        $rmodel=$amodel->GetSettingByRedis2();
//        return json_encode($rmodel);


        //读取配置表中的对照表
        $postdata = $this->request;
        $app_id = $postdata['app_id']; //af app_id
        $amodel=new  AflogModel();
        $rdata=$amodel->GetSettingByRedis();

        if (isset($rdata) && isset($app_id)) {

            foreach ($rdata as $key => $row) {
               // $row=json_decode($row,true);
              //  $gameid=$row->ta_appid;
                if (isset($row->ta_appid) && $row->ta_appid==$app_id) {
                    $gameid=$row->gameid;
                }
            }
        }
        return json_encode($gameid);
    }

    ///af上报接口
    public function actionPostdata()
    {
        $postdata = $this->request;
        if (empty($postdata)) {
            return json_encode(['c' => 1001, 'm' => '缺少参数']);
        }
        $amodel=new  AflogModel();
        $amodel->PostRedisData($postdata);
        return json_encode(['c' => 0, 'm' =>'成功']);
    }
}