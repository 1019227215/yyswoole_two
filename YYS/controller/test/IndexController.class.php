<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/11/14
 * Time: 17:36
 */

namespace controller\test;

use controller\Controller;
use controller\Run;
use model\test\TestModel;

class IndexController extends Controller
{


    public function actionCheckpart()
    {

        return json_encode($this->request);
    }

    public function actionIndex()
    {
        return 'AAA';
        //return self::renderView("test/index.php", ['data' => [1, 2, 3, 4], 'test' => 'adqwasd']);
        //return json_encode(self::$server);
    }

    public function actionSql()
    {

        $test = new TestModel();
        return $test->Index();
    }

    public function actionRedis()
    {

        $test = new TestModel();
        return $test->getRedis();
    }

    public function actionIp()
    {

        return json_encode(swoole_get_local_ip());
    }

}