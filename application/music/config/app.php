<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/25
 * Time: 13:57
 */

return array(
    'app_trace' => false,
    'trace'     =>  [
        //支持Html,Console
        'type'  =>  'html',
    ],
    'layout_on'     =>  true,
    'layout_name'   =>  'layout',
    'login_key' => 'jiang',
    'superman'  => 'jianghairui',
    'auth'  => [
        'auth_on' => true,
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_out'          => [
            'Index/index'
        ]
    ],
    'mch_key' => 'TIANJINTAOCIYUAN20190111SHWHCOPY',
    'app_id' => 'wx0c41f710915cf5b8',
    'secret' => '0aa0bd60351b1c928cb311fa99299d88',
    'mch_id'             => '1490402642',
    // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
    'cert_path'          =>  '/var/www/cave.jianghairui.com/public/cert/apiclient_cert.pem',
    'key_path'           =>  '/var/www/cave.jianghairui.com/public/cert/apiclient_key.pem',


);