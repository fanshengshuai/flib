<?php

class Controller_Admin_Block extends Controller_Admin_Abstract {

    public function beforeAction() {
        parent::beforeAction();
        $this->view->set('cur_nav', "block");
    }

    public function listAction() {

        $page = $_GET['page'] = $_GET['page'] ? $_GET['page'] : 'index';

        if ($page == 'index') {
            $page = 1;
        }
        elseif ($page == 'hotel') {
            $page = 2;
        }
        elseif ($page == 'list') {
            $page = 3;
        }

        $blockDAO = new DAO_Block;
        $whereSql = "page='{$page}'";
        $_blocks = $blockDAO->findAll($whereSql);
        foreach ($_blocks as $value) {
            $blocks[$value['area']][] = $value;
        }

        $this->view->set('blocks', $blocks);
        $this->view->disp('admin/block/list');
    }

    public function saveBlockInfo() {
        $bid = intval($_POST['bid']);
        $_POST['shownum'] = intval($_POST['shownum']);
        $_POST['shownum'] = $_POST['shownum'] > 0 ? $_POST['shownum'] : 10;
        $_POST['parameter']['items'] = $_POST['shownum'];
        $params = serialize($_POST['parameter']);
        $setarr = array(
            'name' => $_POST['name'],
            'title' => $_POST['title'],
            'shownum' => $_POST['shownum'],
            'page' => intval($_POST['page']),
            'area' => intval($_POST['area']),
            'blocktype' => intval($_POST['block_type']),
        );

        $blockDAO = new DAO_Block;
        if($bid) {
            $blockDAO->update($bid, $setarr);
        }
        else {
            $bid = db::insert('common_block', $setarr, true);
        }

        redirect("/admin/block/list?page={$setarr['page']}");
    }

    public function editAction() {

        $bid = intval($_GET['bid']);

        $success = $_GET['success'];
        $this->view->set('success', $success);

        $blockDAO = new DAO_Block;

        if($bid) {
            $block = $blockDAO->find("bid='{$bid}'");
            if(!$block) {
                showmessage('block_not_exist');
            }
            $this->view->set('block', $block);
            // var_dump($block);
            $_G['block'][$bid] = $block;
        }

        if($_POST) {
            $this->saveBlockInfo();
        }

        $block = self::block_checkdefault($block);
        $cachetimearr = array($block['cachetime'] =>' selected="selected"');
        $targetarr[$block['target']] = ' selected';

        if($bid) {
            $blockItemService = new Service_BlockItem;
            $block_items = $blockItemService->listByBid($bid);
            $this->view->set('block_items', $block_items);
        }

        $block['summary'] = htmlspecialchars($block['summary']);
        $block['param']['bannedids'] = !empty($block['param']['bannedids']) ? stripslashes($block['param']['bannedids']) : '';

        $this->view->set('bid', $bid);
        $this->view->disp('admin/block/edit');
    }


    public function blockItemEdit() {

        $itemId = intval($_GET['blockItemId']);

        $blockItemService = Factory::get('m.BlockItem');
        $blockItem = $blockItemService->get($itemId);

        $blockItemId = $blockItem['itemid'];
        $bid = $blockItem['bid'];

        $blockType = $blockItemService->getBlockType($blockItem['bid']);

        if ($blockItem['pic']) {
            $blockItem['pic_show'] = "/attachments/block/" . $blockItem['pic'];
        } else {
            $blockItem['pic_show'] = $blockItemService->getBlockPic($blockItem['id'], $blockType);
        }

        if (!file_exists(YP_ROOT . $blockItem['pic_show'])) {
            $blockItem['pic_show'] = '';
        }

        if ($_POST) {
            $data = array(
                'id' => $_POST['id'],
                'url' => $_POST['url'],
                'title' => $_POST['title'],
                'summary' => $_POST['summary'],
                'note' => $_POST['note'],
            );

            // 特惠酒店
            if ($_POST['stars']) {
                $data['fields']  = array(
                    'stars' => $_POST['stars'],
                    'room_type' => $_POST['room_type'],
                    'price' => $_POST['price'],
                );

                $data['fields'] = str_replace("\\", "\\\\", json_encode($data['fields']));
            }

            // 如果不写url，取id做url
            if (!$data['url']) {
                $data['url'] = $data['id'];
            } else {
                if (preg_match("/[a-zA-Z]/", $data['url']) && !strpos($data['url'], 'ttp://')) {
                    $data['url'] = 'http://' . $data['url'];
                }
            }

            if (!$_POST['pic'] || $_POST['pic'] == $blockItem['pic']) {
                if ($_FILES) {
                    $blockItemService->updatePic($blockItemId, $_FILES['attach']);
                }
            } else {
                $data['pic'] = $_POST['pic'];
            }

            $blockItemService->update($_POST['itemId'], $data);
            header("location: ?m=block&do=edit&bid=" . $_POST['bid']);
        }


        // 特惠酒店
        if ($blockItem['fields']) {
            $extFields = json_decode($blockItem['fields'], true);
            $blockItem['stars']  = $extFields['stars'];
            $blockItem['room_type']  = $extFields['room_type'];
            $blockItem['price']  = $extFields['price'];
        }

        require_once admin_tpl('Block_Item_Edit');
    }

    public function blockItemRemove() {
        $itemId = $_GET['blockItemId'];

        $blockItemService = Factory::get('m.BlockItem');
        $blockItem = $blockItemService->remove($itemId);

        header("location: ?m=block&do=edit&bid=" . $_GET['bid']);
    }

    public function blockItemAdd() {

        // 模板控制变量
        $act = 'add';

        $bid = intval($_REQUEST['bid']);
        if ($_POST || $_REQUEST['push_eid']) {

            $eid = intval($_GET['push_eid']);
            if ($eid) {

                if ($eid) {
                    $enterprise = DB::fetch_first('select * from ' . DB::table('enterprise') . ' where eid=' . $eid);

                    if ($enterprise) {
                        $data = array(
                            'bid' => $bid,
                            'id' => $eid,
                            'url' => $enterprise['eid'],
                            'title' => $enterprise['coName'],
                            'summary' => $enterprise['metaDescription']
                        );

                        if (!$data['summary']) {
                            $enterpriseMessage = DB::fetch_first('select * from ' . DB::table('enterprise_message') . ' where eid=' . $eid);
                            $metaDescription = trim(str_replace('&nbsp;', '', strip_tags($enterpriseMessage['message'])));
                            $metaDescription = cutstr($metaDescription, 180);
                            $data['summary'] = $metaDescription;
                        }

                        $import = true;
                    } else {
                        die('没有此企业');
                    }
                } else {
                    die('没有此企业');
                }
            } else {
                $data = array(
                    'bid' => $bid,
                    'url' => $_POST['url'],
                    'title' => $_POST['title'],
                    'summary' => $_POST['summary']
                );

            }

            // 特惠酒店
            if ($_POST['stars']) {
                $data['fields']  = array(
                    'stars' => $_POST['stars'],
                    'room_type' => $_POST['room_type'],
                    'price' => $_POST['price'],
                );

                $data['fields'] = addslashes(json_encode($data['fields']));
            }

            $blockItemService = Factory::get('m.BlockItem');
            if (!$data['displayorder']) {
                $maxId = DB::result_first('select max(displayorder) from ' . DB::table('common_block_item') . ' where bid=' . $bid);
                $maxId = intval($maxId);
                $maxId++;
                $data['displayorder'] = $maxId;
            }
            $blockItemId = $blockItemService->add($data);

            // 更新图片
            if ($_FILES) {
                $blockItemService->updatePic($blockItemId, $_FILES['attach']);
            }

            header("location: ?m=block&do=edit&bid=" . $bid);
        }

        require_once admin_tpl('Block_Item_Edit');
    }


    function block_checkdefault($block) {
        if(empty($block['shownum'])) {
            $block['shownum'] = 10;
        }
        if(!isset($block['cachetime'])) {
            $block['cachetime'] = '3600';
        }
        if(empty($block['picwidth'])) {
            $block['picwidth'] = "200";
        }
        if(empty($block['picheight'])) {
            $block['picheight'] = "200";
        }
        if(empty($block['target'])) {
            $block['target'] = "blank";
        }
        return $block;
    }
    public function updateDisplayorderAction() {
        $blockId = $_GET['bid'];
        $displayorders = $_POST['displayorder'];

        foreach ($displayorders as $k => $v) {
            $data = array('displayorder' => $v);

            DB::update('common_block_item', $data, "itemid={$k}");
        }

        header("location: ?m=block&do=edit&bid={$blockId}");
    }
}
