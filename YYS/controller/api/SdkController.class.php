<?php
namespace controller\api;

use controller\Controller;
use model\activate\ActivateModel;
use config\Redisdb;

class SdkController extends Controller
{
    #region SDK激活上报
    public function actionActivate()
    {

        $data = $this->request;

        //检查参数是否为空
        if (empty($data)) {
            return json_encode(['c' => 0, 'm' => '缺少参数']);
        }

        //先记录日志请求
        try {
            $af = new ActivateModel();
            $af->InfoLog(json_encode($data));
        } catch (Exception $e) {

        }

        if (!isset($data['version']) || !isset($data['type']) || !isset($data['times']) || !isset($data['version']) || !isset($data['uid'])
            || !isset($data['gameid']) || !isset($data['ip']) || !isset($data['terminal']) || !isset($data['timezone']) || !isset($data['servertime'])
        ) {
           //  return json_encode(['c' => 0, 'm' => '缺少参数']);
        }
        if (!isset($data['deviceid']) && !isset($data['idfa'])) {
            return json_encode(['c' => 0, 'm' => '缺少参数']);
        }

        $gameid = $data['gameid'];
        $result = $af->CreateActivateLog($gameid, 'track_activate', $data);

      // return json_encode(['c' => 1, 'm' => $result]);//测试用

        return json_encode(['c' => 1, 'm' =>'ok']);
    }
#endregion


}