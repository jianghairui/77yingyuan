<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/13
 * Time: 9:08
 */
namespace app\music\controller\api;

class Test {

    public function index() {
//        echo 'OOK';
        $data = [];
        halt($_SERVER);
        checkInput($data);
    }

}