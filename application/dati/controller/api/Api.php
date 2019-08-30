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
        $where = [
            ['status','=',1]
        ];
        try {
            $chapter = Db::table('mp_chapter')->field('id,title,q_num')->select();
            $questionlist = Db::table('mp_question')
                ->where($where)
                ->field('id,c_id,num,title,option_a AS A,option_b AS B,option_c AS C,option_d AS D,key,excerpt')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $chapterlist = [];
        foreach ($chapter as $v) {
            $qlist = [];
            foreach ($questionlist as $vv) {
                if($vv['c_id'] == $v['id']) {
                    $data['id'] = $vv['id'];
                    $data['title'] = $vv['title'];
                    switch ($vv['num']) {
                        case 2:
                            $data['options'] = [
                                'A' => $vv['A'],
                                'B' => $vv['B']
                            ];break;
                        case 3:
                            $data['options'] = [
                            'A' => $vv['A'],
                            'B' => $vv['B'],
                            'C' => $vv['C']
                            ];break;
                        case 4:
                            $data['options'] = [
                                'A' => $vv['A'],
                                'B' => $vv['B'],
                                'C' => $vv['C'],
                                'D' => $vv['D']
                            ];break;
                        default:;
                    }
                    $data['key'] = $vv['key'];
                    $data['excerpt'] = $vv['excerpt'];
                    $qlist[] = $data;
                }
            }
            if(!empty($qlist)) {
                $v['list'] = $qlist;
                $chapterlist[] = $v;
            }
        }
        return ajax($chapterlist);

    }

    public function remark() {
        try {
            $list = Db::table('mp_remark')->field('min_score as min,max_score as max,remark')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }


}