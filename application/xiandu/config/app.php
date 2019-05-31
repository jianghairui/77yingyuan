<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/25
 * Time: 13:57
 */

return array(
    'layout_on'     =>  true,
    'layout_name'   =>  'layout',
    'page'   =>  1,
    'perpage'   =>  5,
    'login_key' => 'jiang',
    'app_trace' => false,

    'trace'     =>  [
        //支持Html,Console
        'type'  =>  'html',
    ],
    'superman'  => 'jianghairui',
    'auth'  => [
        'auth_on' => true,
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_out'          => [
            'Index/index'
        ]
    ],
    'app_id' => 'wx60823ccbac8c4e09',
    'app_secret' => 'e101ef1f8d8dc2d7b97d3d394d8769b0',
    'mch_id'  => '1490402642',
    'mch_key' => 'TIANJINTAOCIYUAN20190111SHWHCOPY',
    'cert_path' =>  '/var/www/cave.jianghairui.com/public/cert/apiclient_cert.pem',
    'key_path' =>  '/var/www/cave.jianghairui.com/public/cert/apiclient_key.pem'

);