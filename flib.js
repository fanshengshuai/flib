$(document).ready(function() {
    apply_ajax_form();
});

function ajax_form_success(data) {

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
                        },
                }) ;
            }
    });
}

function copyToClipboard(txt) {
    if(window.clipboardData) {
        window.clipboardData.clearData();
        window.clipboardData.setData("Text", txt);
        alert("复制成功！");
    }
    else if(navigator.userAgent.indexOf("Opera") != -1) {
        window.location = txt;
    }
    else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        }
        catch (e) {
            alert("您的firefox安全限制限制您进行剪贴板操作，请打开'about:config'将signed.applets.codebase_principal_support'设置为true'之后重试");
            return false;
        }
        var clip = Components.classes["@mozilla.org/widget/clipboard;1"].createInstance(Components.interfaces.nsIClipboard);
        if (!clip)
            return;
        var trans = Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable);
        if (!trans)
            return;
        trans.addDataFlavor('text/unicode');
        var str = new Object();
        var len = new Object();
        var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext = txt;
        str.data = copytext;
        trans.setTransferData("text/unicode",str,copytext.length*2);
        var clipid = Components.interfaces.nsIClipboard;
        if (!clip)
            return false;
        clip.setData(trans,null,clipid.kGlobalClipboard);
    }
}
