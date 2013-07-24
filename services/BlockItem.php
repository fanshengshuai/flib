<?php
/**
 *
 *      作者:范圣帅(fanshengshuai@comsenz.com)
 *  创建时间:2011-03-12 08:53:16
 *  修改记录:
 *  $Id$
 */

class Service_BlockItem {

    public function get($blockItemId) {

        $item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE itemid = '$blockItemId'");

        return $item;
    }

    public function update($blockItemId, $data) {

        if (!$data['update_time']) {
            $data['update_time'] = date('Y-m-d H:i:s');
        }

        DB::update('common_block_item', $data, 'itemid = ' . $blockItemId);
    }

    public function remove($blockItemId) {

        DB::delete('common_block_item', 'itemid = ' . $blockItemId);
    }

    public function add($data) {

        $data['create_time'] = date('Y-m-d H:i:s');
        return DB::insert('common_block_item', $data, true);
    }

    public function listByBid($bid) {

        $blockType = $this->getBlockType($bid);

        $blockItemDAO = new DAO_BlockItem;
        $block_items = $blockItemDAO->findAll("bid = '$bid' order by displayorder desc");
        
        foreach ($block_items as $item) {
            if ($item['pic']) {
                $pic = "/attachs/block/" . $item['pic'];
            } else {
                //$pic = $this->getBlockPic($item['id'], $blockType);
            }

            // if (file_exists(YP_ROOT . $pic)) {
            //     $item['pic'] = $pic;
            // } else {
            //     $item['pic'] = '';
            // }

            if (strpos($item['url'], 'http://') !== 0) { $item['url'] = "/" . $item['url']; }

            $blockItems[$item['item_id']] = $item;
        }

        // if ($blockItems) {
            // krsort($blockItems);
        // }

        return $blockItems;
    }


    public function updatePic($blockItemId, &$attach) {

        if (!$blockItemId) {
            throw new Exception('blockItemId is null in updatePic');
        }

        $blockItem = $this->get($blockItemId);

        // 取扩展名
        $attachInfo = pathinfo($attach['name']);
        $ext = strtolower($attachInfo["extension"]);

        // 附件文件散列路径
        $fileDir = getPicPathById($blockItemId);

        // 根据模块类型判断
        $blockType = $this->getBlockType($blockItem['bid']);
        if ($blockType == 'enterprise') {
            // $attachPath = "/attachments/logos";
            // $srcFileName = "logo_src.{$ext}";

            $needThumb = true; $w = 100; $h = 40;
        } elseif ($blockType == 'goods') {
            // $attachPath = "/attachments/goods";
            // $srcFileName = "goods_pic_src.{$ext}";

            $needThumb = true; $w = 100; $h = 100;
        } else {

            // $imgInfo = getimagesize($source);
            // $w = $imgInfo[0]; $h = $imgInfo[1];
        }

        $attachPath = "/attachments/block";
        $srcFileName = "src.png";

        // 目的原图
        $dstFile = YP_ROOT . "{$attachPath}/{$fileDir}/{$srcFileName}";

        // 准备好目录
        dmkdir(dirname($dstFile));

        // 如果存在，备份一下
        if (file_exists($dstFile)) {
            $bakFile = dirname($dstFile) . "/bak." . date('Y-m-d H_i_s') . ".{$ext}";
            copy($dstFile, $bakFile);
        }

        // copy 过去
        copy($attach['tmp_name'], $dstFile);

        // 生成缩图
        if ($needThumb) {
            require_once YP_ROOT . "./source/class/class_image.php";

            $thumbPic = dirname($dstFile) . "/{$w}x{$h}.png";

            $image = new image;
            $thumResult = $image->Thumb($dstFile, $thumbPic, $w, $h);
        }

        // 更新数据库
        $data = array('pic' => "{$fileDir}/{$srcFileName}");
        $this->update($blockItemId, $data);
    }

    public function getBlockType($bid) {
        $blockDAO = new DAO_Block;

        $block = $blockDAO->get($bid);
        $type = '';
        if ($block['blocktype'] == '1') {
            $type = 'enterprise';
        } elseif ($block['blocktype'] == '2') {
            $type = 'goods';
        }

        return $type;
    }

    public function getBlockPic($blockItemId, $blockType) {

        if ($blockType == 'enterprise') {
            $pic = "/attachments/block/" . getPicById($blockItemId, 100, 40);
            if (!file_exists($pic)) {
                $pic = "/attachments/logos/" . getPicById($blockItemId, 100, 40);
            }

        } elseif ($blockType == 'goods') {
            $pic = "/attachments/block/" . getPicById($blockItemId, 100, 100);
            if (!file_exists($pic)) {
                $pic = "/attachments/goods/" . getPicById($blockItemId, 100, 40);
            }

        } else {
            $pic = "/attachments/block/" . getPicPathById($blockItemId) . "/src.png";
        }

        return $pic;
    }
}
