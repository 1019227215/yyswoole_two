<?php
/**
 * Created by PhpStorm.
 * User: yyswoole
 * Date: 2018/11/19
 * Time: 18:34
 */

return array(

    '211.149.163.122' => [
        'yyshou.com' => [
            'mysql' => 'db',
            'redis' => 'db',
            'itemdir' => S_ROOT .OBJ.'/',
            'filing' => '渝ICP备18012118号-2',
        ],
        'bwbj.net' => [
            'mysql' => 'db',
            'redis' => 'db',
            'itemdir' => S_ROOT .OBJ.'/',
            'filing' => '渝ICP备18012118号-1',
        ],
        'lajinni.com' => [
            'mysql' => 'db',
            'redis' => 'db',
            'itemdir' => S_ROOT .OBJ.'/',
            'filing' => '渝ICP备18012118号-3',
        ],
        'weitaogy.com' => [
            'mysql' => 'db',
            'redis' => 'db',
            'itemdir' => S_ROOT .OBJ.'/',
            'filing' => '渝ICP备18012118号-4',
        ],
        'yyswoole.com' => [
            'mysql' => 'db',
            'redis' => 'db',
            'itemdir' => S_ROOT .OBJ.'/',
            'filing' => '渝ICP备18012118号-5',
        ],
    ],

    'render' => [

        //静态文件目录名，位于public下
        'static_url' => 'html',

        //静态文件格式
        'static' => ['html', 'txt', 'ico', 'js', 'map', 'css', 'png', 'jpg', 'gif', 'otf', 'fon', 'font', 'ttc', 'eot', 'svg', 'ttf', 'woff', 'woff2'],

        //动态文件格式
        'dynamic' => ['html', 'php', 'htm'],

        //目录权限配置
        'safety' => ['chroot' => S_ROOT, 'group' => 'www', 'user' => 'www',],

    ],

);