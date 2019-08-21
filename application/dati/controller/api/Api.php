<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/8
 * Time: 11:11
 */

namespace app\dati\controller\api;

use think\Controller;
use think\Db;
use think\Exception;

class Api extends Controller
{

    public function questionList() {

        try {
            $chapter = Db::table('mp_chapter')->field('id,title,q_num')->select();
            $questionlist = Db::table('mp_question')
                ->field('id,c_id,num,title,option_a AS a,option_b AS b,option_c AS c,option_d AS d,key,excerpt')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        foreach ($chapter as &$v) {
            $v['list'] = [];
            $data = [];
            foreach ($questionlist as $vv) {
                if($vv['c_id'] == $v['id']) {
                    $data['id'] = $vv['id'];
                    $data['title'] = $vv['title'];
                    switch ($vv['num']) {
                        case 2:
                            $data['options'] = [
                                'a' => $vv['a'],
                                'b' => $vv['b']
                            ];break;
                        case 3:
                            $data['options'] = [
                            'a' => $vv['a'],
                            'b' => $vv['b'],
                            'c' => $vv['c']
                            ];break;
                        case 4:
                            $data['options'] = [
                                'a' => $vv['a'],
                                'b' => $vv['b'],
                                'c' => $vv['c'],
                                'd' => $vv['d']
                            ];break;
                        default:;
                    }
                    $data['key'] = $vv['key'];
                    $data['excerpt'] = $vv['excerpt'];
                    $v['list'][] = $data;
                }
            }
        }
        return ajax($chapter);

    }


}