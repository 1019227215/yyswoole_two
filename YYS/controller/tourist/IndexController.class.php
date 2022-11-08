<?php

namespace YYs\controller\tourist;

use components\Controller;

class IndexController extends Controller
{

    /**
     * 视图内容
     * @return false|string
     */
    public function actionList()
    {
        $id = self::$server->get["TouristId"] ?? "";
        $id = self::GetPublicId($id);
        $url = Puc . "/template/tourist" . '/index_' . $id . '.html';
        $html = "";
        if (!empty($id)){
            if (file_exists($url)) {
                $html = file_get_contents($url);
            }else{
                self::$response->header('Content-Type', 'text/html; charset=utf-8', true);
                $html = "<div style='width:100%;height:200px;padding-top: 200px;text-align: center;vertical-align: middle;'>未找到相关数据...</div>";
            }
        }
        return $html;
    }

}