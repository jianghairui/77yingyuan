<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/13
 * Time: 9:08
 */
namespace app\estate\controller\api;

class Test {

    public function index() {
//        echo 'OOK';
        $data = [];
        halt($_SERVER);
        checkInput($data);
    }

}