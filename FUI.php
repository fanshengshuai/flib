<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2014-07-03 10:57:22
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id$
 */
class FUI {

    public static function getSelector($name, $items = array(), $defaultValue = null) {
        $retHtml = "<select id=\"form-select-{$name}\" name=\"{$name}\"  class=\"form-control\">";
        foreach ($items as $item_key => $item_value) {

            if (is_array($item_value)) {
                foreach ($item_value as $subItemKey => $subItemValue) {
                    $selected = ($subItemValue == $defaultValue) ? "selected='selected'" : '';
                    $retHtml .= "<option value='{$subItemValue}' {$selected}>{$subItemKey}</option>";
                }
            } else {
                $selected = ($item_value == $defaultValue) ? "selected='selected'" : '';
                $retHtml .= "<option value='{$item_value}' {$selected}>{$item_key}</option>";
            }

        }
        $retHtml .= "";

        return $retHtml;
    }

    public static function getLabel($labelContent, $labelFor) {
        $retHtml = "<label id=\"form-label-{$labelFor}\" for=\"{$labelFor}\"  class=\"sr-only\">{$labelContent}</label>";
        return $retHtml;
    }
}