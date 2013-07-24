//var ajax_forms = new Array();
var G = {};

function apply_ajax() {

    $(document).ready(function() {

            var fancybox_ajax_url, content, tmp;

            $('a, button').each(function() {
                    if ($(this).attr('ajax') == 'true') {
                        $(this).attr('ajax', '');

                        $(this).click(function() {
                                var url_href = $(this).attr('href');
                                if (url_href.indexOf('?') != -1) {
                                    fancybox_ajax_url = url_href + '&in_ajax=1';
                                }
                                else {
                                    fancybox_ajax_url = url_href + '?in_ajax=1';
                                }

                                $.ajax({
                                        url:fancybox_ajax_url,
                                        type:'get',
                                        cache:false,
                                        //'dataType':'json',
                                        success:function(ret_result) {

                                            ajax_form_success(ret_result);
                                        },
                                        error:function(XMLHttpRequest, textStatus, errorThrown) {
                                            if (XMLHttpRequest.status == 200) {
                                            }
                                            else if (XMLHttpRequest.status == 500) {
                                                alert('服务器内部错误(500)，请联系 fanshengshuai@gmail.com ');
                                                return false;
                                            }
                                            else {
                                                alert('发生错误，error no (' + XMLHttpRequest.status + ')，请联系 fanshengshuai@gmail.com ');
                                            }
                                        }
                                });

                                return false;
                        });
                    }
            });

            if ($("form")[0] != undefined) {
                $.getScript('/js/jquery.form.js', function() {
                        $("form").each(function() {
                                if ($(this).attr('ajax') == 'true') {
                                    $(this).attr('ajax', '');

                                    //if (!$('input[name=in_ajax]')[0]) {
                                    $("<input type='hidden' name='in_ajax' value='1' />").appendTo($(this));
                                    //}

                                    $(this).ajaxForm({
                                            type:'post',
                                            dataType:'json',
                                            success: function(data) {

                                                if (data.result == 'redirect') {
                                                    location.href = data.url;
                                                }
                                                else if (data.result == 'failed') {
                                                    ajax_form_failed(data);
                                                }
                                                else {
                                                    ajax_form_success(data);
                                                }
                                            },
                                            error: function (jqXHR, textStatus, errorThrown) {

                                                if (textStatus == 'parsererror') {
                                                    $.fancybox(jqXHR['responseText']);
                                                }
                                                else {

                                                    if (jqXHR['status'] != '200') {
                                                        $.fancybox('<p style="padding:10px;">发生错误，描述如下：<br /><h1>' + jqXHR['status'] + ' &nbsp; ' + jqXHR['statusText'] + '</h1>' + jqXHR['responseText'] + '</p>');
                                                    }
                                                }
                                            }
                                    }) ;
                                }
                        });
                });
            }

            $('.tmain .t_c')
            .mouseover(function() { $(this).css({ 'background':'#eee' }); })
            .mouseout(function() { $(this).css({ 'background':'#fff' });} );
    });
}

function ajax_form_success(data) {

    if (typeof(data) == 'string' && data.indexOf('{') == 0) {
        data = eval('('+data+')');

        if (data.result == 'redirect') {
            location.href = data.url;
            return true;
        }
    }



    if (data.message == undefined) {
        message_content = data;

        if (data.url != undefined) {
            location.href = data.url;
        }
    }
    else {
        message_content = data.message;
    }

    $.fancybox(message_content, {
            'onClosed':function() {
                if (data.url != undefined && data.url != '') {
                    location.href = data.url;
                }
            }
    });
}

function ajax_form_failed(data) {
    lost_items_text = '';

    for(var key in data.items) {

        element = undefined;

        if ($('input[name=' + key + ']')[0] != undefined) {
            element = $('input[name=' + key + ']');
        }
        else if ($('select[name=' + key + ']')[0] != undefined) {
            element = $('select[name=' + key + ']');
        }
        else if ($('textarea[name=' + key + ']')[0] != undefined) {
            element = $('textarea[name=' + key + ']');
        }
        else {
            lost_items_text += '[ ' + key + ' ] &nbsp;';
        }

        if (element != undefined) {
            if (!$('#lbl_'+key)[0]) {
                element
                .css({ 'background':'red' })
                .after('<span style="color:#f00;" id="lbl_' + key + '">&nbsp;' + data.items[key] + '</span>')
            }

            if($('#lbl_'+key).html() == null) {

                element
                .css({ 'background':'red' })
                .after('<span style="color:#f00;" id="lbl_' + key + '">&nbsp;' + data.items[key] + '</span>')
                .click(function() { $(this).css({ 'background':'#FFFDDD' }); $('#lbl_' + $(this).attr('name')).remove(); });
            }
            else {
                element
                .css({ 'background':'red' })
                .click(function() { $(this).css({ 'background':'#FFFDDD' }); $('#lbl_' + $(this).attr('name')).remove(); });
                $('#lbl_'+key).html("&nbsp;"+data.items[key]);
            }
        }
    }

    if (lost_items_text != '') {
        $.fancybox('表单丢失了以下项目：' + lost_items_text);
    }

    if (data.message != undefined && data.message != '') {
        $.fancybox('<span style="color:#f00;">' + data.message + '</span>');
    }
}

/*
 if (typeof($) == 'undefined') {
     var head = document.getElementsByTagName('head').item(0);
     var new_script = document.createElement("script");
     new_script.type = "text/javascript";
     new_script.src = "/js/jquery.js";
     head.appendChild(new_script);
 }
 */
