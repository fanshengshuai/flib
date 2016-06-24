<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-27 21:26:16
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FUploader.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FUploader {
    public static function saveFile($field, $attach_type = 'image') {
        $uploader = new FUploader ();
        $photo_file = "{$attach_type}/" . date('Ymd') . '/' . date('His') . rand(1000, 9999) . ".attach";
        $upload_file = $uploader->saveAttach($field, $attach_type, $photo_file);
        return $upload_file ['file_path'];
    }

    public function saveAttach($field, $attach_type = 'images', $obj = null) {
        global $_F;

        if ($_FILES [$field] ['size'] < 1)
            return -1;
        if (!$obj) {
            $obj = $attach_type . '/' . date('Y-m') . '/' . date('d') . '/' . date('YmdHis') . '.attach';
        }

        // 处理扩展名
        if (strpos($obj, '.attach')) {
            $_file_path_info = pathinfo($_FILES [$field]['name']);
            $file_ext = strtolower($_file_path_info['extension']);

            if ($file_ext == 'jpeg') {
                $file_ext = 'jpg';
            } elseif ($file_ext == 'php') {
                $file_ext = 'txt';
            }

            $default_upload_ext = array('jpg', 'png', 'gif');
            $config_upload_ext = explode(',', str_replace(' ', '', FConfig::get('global.upload_file_ext')));

            if ($config_upload_ext) {
                $config_upload_ext = array_merge($config_upload_ext, $default_upload_ext);
            } else {
                $config_upload_ext = $default_upload_ext;
            }

            if (!in_array($file_ext, $config_upload_ext)) {
                if ($_F['debug']) {
                    throw new Exception('禁止上传此类型的文件。');
                }
                return false;
            }

            $obj = str_replace('.attach', '.' . $file_ext, $obj);
        }

        // echo $obj;

        $attach_url = $obj;

        if (strpos($obj, WEB_ROOT_DIR) === false) {
            $obj = "uploads/" . $obj;
        }

        $attach_dir = dirname($obj);

        if (!is_dir($attach_dir)) {
            mkdir($attach_dir, 0777, true);
        }

        if ($_FILES [$field]) {
//            $a = (is_uploaded_file($_FILES [$field] ['tmp_name']));
            if (!move_uploaded_file($_FILES [$field] ['tmp_name'], $obj)) {
                throw new Exception ('请检查public/uploads目录是否可写!');
            }

//			$attachDAO = new DAO_Attach ();
            $data = array(
                'file_name' => $_FILES [$field] ['name'],
                'file_type' => $_FILES [$field] ['type'],
                'file_size' => $_FILES [$field] ['size'],
                'file_path' => $attach_url
            );

            //$attachDAO->add ( $data );

            return $data;
        }

        return false;
    }

    public static function save($field, $attach_type = 'images', $obj = null) {
        $uploader = new FUploader;
        $upload_file = $uploader->saveAttach($field, $attach_type, $obj);
        return $upload_file;
    }

    public function setFileType($type) {
    }

    public function getFileType($file_name) {
        $ext_arr = array(
            'pic' => array(
                'gif',
                'jpg',
                'jpeg',
                'png',
                'bmp'
            ),
            'flash' => array(
                'swf',
                'flv'
            ),
            'media' => array(
                'swf',
                'flv',
                'mp3',
                'wav',
                'wma',
                'wmv',
                'mid',
                'avi',
                'mpg',
                'asf',
                'rm',
                'rmvb'
            ),
            'file' => array(
                'doc',
                'docx',
                'xls',
                'xlsx',
                'ppt',
                'htm',
                'html',
                'txt',
                'zip',
                'rar',
                'gz',
                'bz2'
            )
        );

        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);

        if (in_array($file_ext, $ext_arr ['pic'])) {
            return 'pic';
        }
    }
}
