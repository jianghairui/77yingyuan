<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/7
 * Time: 17:17
 */
namespace app\estate\model;
use think\Model;

class Teacher extends Model
{
    protected $pk = 'id';
    protected $table = 'mp_teacher';

    protected static function init()
    {

        self::afterDelete(function ($data) {
            //控制需要用destroy方法触发,不可用delete
            @unlink($data['pic']);
        });

    }



}