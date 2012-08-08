/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-08-08 10:16:23
 * vim: set expandtab sw=4 ts=4 sts=4 * 
 *
 * $Id$
 */

$(document).ready(function() {
        apply_ajax_form();
});

function apply_ajax_form() {
    $("form").each(function() {
            if ($(this).attr('ajax') == 'true') {

                //if (!$('input[name=in_ajax]')[0]) {
                $("<input type='hidden' name='in_ajax' value='1' />").appendTo($(this));
                //}

                $(this).ajaxForm({
                        type:'post',
                        dataType:'json',
                        success: function(data) {
                            //alert(data);

                            if (data.result == 'redirect') {
                                location.href = data.url;
                            } else if (data.result == 'success') {

                                if (data.message != '') {
                                    $.fancybox(['' + data.message + ''], {
                                            'onClosed':function() {
                                                if (data.url != undefined && data.url != '') {
                                                    location.href = data.url;
                                                }
                                            }
                                    });
                                } else {
                                    if (data.url != '') {
                                        location.href = data.url;
                                    }
                                }


                            } else if (data.result == 'failed') {

                                lost_items_text = '';
                                for(var key in data.items) {

                                    if ($('input[name=' + key + ']')[0] != undefined) {
                                        element = $('input[name=' + key + ']');
                                    } else if ($('select[name=' + key + ']')[0] != undefined) {
                                        element = $('select[name=' + key + ']');
                                    } else {
                                        if ($('input[name=' + key + ']')[0] == undefined) {
                                            lost_items_text += '[ ' + key + ' ] &nbsp;';
                                        } else {
                                            if ($('select[name=' + key + ']')[0] == undefined) {
                                                lost_items_text += '[ ' + key + ' ] &nbsp;';
                                            }
                                        }
                                    }

                                    if($('#lbl_'+key).html()==null)
                                    {
                                        element
                                        .css({ 'background':'red' })
                                        .after('<span style="color:#f00;" id="lbl_' + key + '">&nbsp;' + data.items[key] + '</span>')
                                        .click(function() { $(this).css({ 'background':'#FFFDDD' }); $('#lbl_' + $(this).attr('name')).remove(); });
                                    }
                                    else{
                                        element
                                        .css({ 'background':'red' })
                                        .click(function() { $(this).css({ 'background':'#FFFDDD' }); $('#lbl_' + $(this).attr('name')).remove(); });
                                        $('#lbl_'+key).html("&nbsp;"+data.items[key]);
                                    }

                                }

                                if (lost_items_text != '') {
                                    $.fancybox('表单丢失了以下项目：' + lost_items_text);
                                }

                                if (data.message != undefined && data.message != '') {
                                    $.fancybox('<span style="color:#f00;">' + data.message + '</span>');
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {

                            if (textStatus == 'parsererror') {
                                $.fancybox(jqXHR['responseText']);
                            } else {

                                if (jqXHR['status'] != '200') {
                                    $.fancybox('<p style="padding:10px;">发生错误，描述如下：<br /><h1>' + jqXHR['status'] + ' &nbsp; ' + jqXHR['statusText'] + '</h1>' + jqXHR['responseText'] + '</p>');
                                }
                            }
                        },
                        complete: function(jqXHR, textStatus) {
                            /*
                             for (var key in jqXHR) {
                                 alert(key + jqXHR[key]);
                             }
                             * statusCode
                             */
                        }
                }) ;
            }
    });
}
