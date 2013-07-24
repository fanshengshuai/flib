<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-09-18 10:11:29
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 */

define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('RUN_MODE', 'cli');

require_once "Flib.php";

$sql_file = APP_ROOT . "docs/db.sql";

$sql_content = file_get_contents($sql_file);

// 分解SQL升级文档
preg_match_all("/CREATE\s+TABLE.+?(.+?)\s*\((.+?)\)\s*.+?\;/is", $sql_content, $matches);
$tables = empty($matches[1])?array():$matches[1];
$sqls = empty($matches[0])?array():$matches[0];

foreach ($tables as $key => $t) {
    $t = str_replace('`', '', $t);
    $tables[$key] = $t;

    $sql = "SHOW CREATE TABLE `{$t}`";

    try {
        $old_table = DB::fetch($sql);
    } catch(Exception $e) {
        if (strpos($e->getMessage(), 'table or view not found')) {
            DB::query($sqls[$key]. ';');
            echo "creating {$tables[$key]}...\n";
            unset ($sqls[$key], $tables[$key]);
            continue;
        }
    }

    if ($old_table) {
        $create_sql = $old_table[0]['Create Table'];
        $old_tables[$t] = getcolumn($create_sql);
    }
}

foreach ($sqls as $key => $sql) {
    $table_cols[$tables[$key]] = getcolumn($sql);
}

foreach ($tables as $key => $t) {
    echo update_table($t);
}

echo "\n\n";

function update_table($table) {
    global $table_cols, $old_tables;

    $allfileds = array_keys($table_cols[$table]);
    $oldcols = $old_tables[$table];

    foreach ($table_cols[$table] as $key => $value) {
        if(strtoupper($key) == 'PRIMARY') {

            if($value != $oldcols[$key]) {
                if(!empty($oldcols[$key])) {
                    $usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
                    if(!DB::query($usql, 'SILENT')) {
                        show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
                    } else {
                        $msg = '表改名 '.DB::table($newtable).' 完成！';
                        show_msg($msg, $theurl.'?step=sql&i='.$_GET['i']);
                    }
                }
                $updates[] = "ADD PRIMARY KEY $value";
            }
        } elseif ($key == 'KEY') {
            foreach ($value as $subkey => $subvalue) {
                if(!empty($oldcols['KEY'][$subkey])) {
                    if($subvalue != $oldcols['KEY'][$subkey]) {
                        $updates[] = "DROP INDEX `$subkey`";
                        $updates[] = "ADD INDEX `$subkey` $subvalue";
                    }
                } else {
                    $updates[] = "ADD INDEX `$subkey` $subvalue";
                }
            }
        } elseif ($key == 'UNIQUE') {
            foreach ($value as $subkey => $subvalue) {
                if(!empty($oldcols['UNIQUE'][$subkey])) {
                    if($subvalue != $oldcols['UNIQUE'][$subkey]) {
                        $updates[] = "DROP INDEX `$subkey`";
                        $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                    }
                } else {
                    $usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
                    DB::query($usql, 'SILENT');
                    $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                }
            }
        } else {
            if(!empty($oldcols[$key])) {
                if(strtolower($value) != strtolower($oldcols[$key])) {
                    $updates[] = "CHANGE `$key` `$key` $value";
                }
            } else {
                $i = array_search($key, $allfileds);
                $fieldposition = $i > 0 ? 'AFTER `'.$allfileds[$i-1].'`' : 'FIRST';
                $updates[] = "ADD `$key` $value $fieldposition";
            }
        }
    }

    //echo "\n" .$table . "\n";
    //var_dump($updates);
    //return;


    if(!empty($updates)) {
        $usql = "ALTER TABLE ".$table." ".implode(', ', $updates);
        //echo $usql;
        DB::query($usql);
        $msg = '升级表 '.$table.' 完成！';
    } else {
        $msg = '检查表 '.$table.' 完成，不需升级，跳过';
    }

    echo "{$msg}\n";
}

function getcolumn($creatsql) {

    $creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
    $creatsql = preg_replace("/PRIMARY KEY/i", 'PRIMARY KEY', $creatsql);
    preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

    $cols = explode("\n", $matchs[1]);
    $newcols = array();
    foreach ($cols as $value) {
        $value = trim($value);
        if(empty($value)) continue;
        $value = remakesql($value);
        if(substr($value, -1) == ',') $value = substr($value, 0, -1);

        $vs = explode(' ', $value);
        $cname = $vs[0];

        if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

            $name_length = strlen($cname);
            if($cname == 'UNIQUE') $name_length = $name_length + 4;

            $subvalue = trim(substr($value, $name_length));
            $subvs = explode(' ', $subvalue);
            $subcname = $subvs[0];
            $newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

        }  elseif($cname == 'PRIMARY') {

            $newcols[$cname] = trim(substr($value, 11));

        }  else {

            $newcols[$cname] = trim(substr($value, strlen($cname)));
        }
    }

    //var_dump($newcols);

    return $newcols;
}

function remakesql($value) {
    $value = trim(preg_replace("/\s+/", ' ', $value));
    $value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
    return $value;
}
