<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/13
 * Time: 9:08
 */
namespace app\estate\controller;

use think\Db;

class Test {

    public function index() {
        try {
            $info = Db::table('mp_company')->find();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        echo '<div style="width: 40%;">' . $info['intro'] . '</div>';
    }

}