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
    'superman'  => 'root',
    'auth'  => [
        'auth_on' => true,
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_out'          => [
            'Index/index',
            'Index/uploadimage',
            'Login/personal',
            'Login/modifyInfo'
        ]
    ],
    'app_id' => 'wxcf0a69a03e0d6c0a',
    'app_secret' => 'a3a1402a7063c946ccf8ae7634bd010e',
    'mch_id'  => '1437814202',
    'mch_key' => 'xanaducn1234567890xanaducn123456',
    'cert_path' =>  '/var/www/cave.jianghairui.com/public/res/xiandu/cert/apiclient_cert.pem',
    'key_path' =>  '/var/www/cave.jianghairui.com/public/res/xiandu/cert/apiclient_key.pem'

);