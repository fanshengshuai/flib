<?php

class Service_Category {

    public static function get($pid) {
        $cateDAO = new DAO_Category;
        $cate_list1 = $cateDAO->findAll("pid=0 and status=1");

        if (!$cate_list1) {
            return false;
        }

        foreach ($cate_list1 as $item) {
            $pids_1[] = $item['cid'];
            $cate_list[$item['cid']] = $item;
        }

        $str_pids_1 = join(',', $pids_1);

        $cate_list2 = $cateDAO->findAll("pid in (" . $str_pids_1 . ")");

        foreach ($cate_list2 as $item) {
            $cate_list[$item['pid']]['sub'][$item['cid']] = $item;
        }

        return $cate_list;
    }
}
