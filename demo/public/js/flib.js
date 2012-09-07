/*

flib : Fast Script Solution Lib
author:范圣帅

*/

function fss () {
    this.prototype.aaa="";
}

fss.prototype.toString = function() {
    return "Fast Script Solution Lib";
};

$(document).keypress(function(event) {
        if ($("#flib_msgbox")[0] && (event.keyCode == 27)) {
        fss.Dialog.close();
        }
        });

function checkall(form, prefix, checkall) {
    var checkall = checkall ? checkall : 'chkall';
    count = 0;
    for(var i = 0; i < form.elements.length; i++) {
        var e = form.elements[i];
        if(e.name && e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
            e.checked = form.elements[checkall].checked;
            if(e.checked) {
                count++;
            }
        }
    }
    return count;
}

function checkall(form, prefix, checkall, type, changestyle) {

    var checkall = checkall ? checkall: 'chkall';
    var type = type ? type: 'name';

    for (var i = 0; i < form.elements.length; i++) {
        var e = form.elements[i];

        if (type == 'value' && e.type == "checkbox" && e.name != checkall) {
            if (e.name != checkall && (prefix && e.value == prefix)) {
                e.checked = form.elements[checkall].checked;
            }
        }
        else if (type == 'name' && e.type == "checkbox" && e.name != checkall) {
            if ((!prefix || (prefix && e.name.match(prefix)))) {
                e.checked = form.elements[checkall].checked;
                if(changestyle && e.parentNode && e.parentNode.tagName.toLowerCase() == 'li') {
                    e.parentNode.className = e.checked ? 'checked' : '';
                }
            }
        }

    }

}

function loadJS(id, fileUrl) {
    var scriptTag = document.getElementById(id);
    var oHead = document.getElementsByTagName('HEAD').item(0);
    var oScript = document.createElement("script");
    if (scriptTag) oHead.removeChild(scriptTag);
    oScript.id = id;
    oScript.type = "text/javascript";
    oScript.src = fileUrl;
    oHead.appendChild(oScript);
}

function _$(selector) {
    if (typeof selector == "string") {
        return document.getElementById(selector);
    }
    else {
        return selector;
    }
}

window.wget = function(src) {
    return fss.ajax.get(src);
};

var currentMoveObj = '', pX, pY;
var index = 10000; // z-index;
document.onmouseup = drag_mouse_up;
document.onmousemove = drag_mouse_move;

function drag_mouse_down(Objectid) {
    currentMoveObj = Objectid;
    _$(currentMoveObj).setCapture();
    pX = event.x - document.getElementById(currentMoveObj).style.pixelLeft;
    pY = event.y - document.getElementById(currentMoveObj).style.pixelTop;
}

function drag_mouse_move() {
    if (currentMoveObj != '') {
        if (event.x - pX > 0 && event.y - pY > 0) {
            document.getElementById(currentMoveObj).style.left = event.x - pX;
            document.getElementById(currentMoveObj).style.top = event.y - pY;
            return;
        }
    }
}

function drag_mouse_up() {
    if (currentMoveObj != '') {
        document.getElementById(currentMoveObj).releaseCapture();
        currentMoveObj = '';
    }
}

function FAjaxDlg(url, title) {
    if (arguments[1]) {
        fss.Dialog.title = title;
    }
    else {
        fss.Dialog.title = '提示信息';
    }
    fss.Dialog.show();
    fss.ajax.load(url + "&in_ajax=1&inajax=1", "flib_msgbox_content");
    return false;
}

function FGoto(url) {
    location = url;
}

var quickExpr = /^[^<]*(<(.|\s)+>)[^>]*_$|^#(\w+)_$/;
fss.prototype.trim = function(text) {
    return (text || "").replace(/^\s+|\s+_$/g, "");
};

fss.prototype.showDialog = function(msg, title, w, h) {
    if (_$("flib_msgbox") == null || _$("flib_msgbox").style.display == "none") {
        if (arguments[0]) fss.Dialog.msg = msg;
        if (arguments[1]) fss.Dialog.title = title;
        if (arguments[2]) fss.Dialog.w = w;
        if (arguments[3]) fss.Dialog.h = h;
        fss.Dialog.show();
    }
};

var moveX = 0;
var moveY = 0;
var moveTop = 0;
var moveLeft = 0;
var moveable = false;
var iWidth = document.documentElement.clientWidth;
var iHeight = document.documentElement.clientHeight;
var docMouseMoveEvent = document.onmousemove;
var docMouseUpEvent = document.onmouseup;

function getEvent() {
    return window.event || arguments.callee.caller.arguments[0];
}

fss.Dialog = {
    "l": 0,
    t: 0,
    w: 620,
    h: 400,
    title: "",
    msg: "",
    show: function() {
        scrollTop = fss.browser.scrollTop();
        scrollLeft = fss.browser.scrollLeft();
        if (fss.browser.msie) {
            document.onreadystatechange = function() {
                if (document.readyState != "complete") {
                    return;
                }
            };
        }
        if (document.getElementById("maskBG") == null) {
            var maskBG = document.createElement("div");
            maskBG.style.cssText = "position:absolute;left:0px;top:0px;width:" + iWidth + "px;height:" + $(document).height() + "px;filter:Alpha(Opacity=30);opacity:0.3;background-color:#000000;z-index:100001;";
            document.body.appendChild(maskBG);
            maskBG.style.display = "none";

            iHeight = Math.max(document.body.clientHeight, iHeight);
            var flib_msgbox = document.createElement("div");
            document.body.appendChild(flib_msgbox);

            var flib_msgbox_wrap = document.createElement("div");
            flib_msgbox.appendChild(flib_msgbox_wrap);
            flib_msgbox.style.display = "none";
            maskBG.id = "maskBG";
            flib_msgbox.id = "flib_msgbox";
        }

        maskBG = _$("maskBG");
        flib_msgbox = _$("flib_msgbox");
        flib_msgbox_html = '<div class="wrap"><div id="flib_msgbox_titlebar"><h3 id="flib_msgbox_title">' + this.title + '</h3><button id="flib_msgbox_btnclose" title="关闭">关闭</button></div>';
        flib_msgbox_html += '<div id="flib_msgbox_content">' + this.msg + '</div>';
        flib_msgbox.innerHTML = flib_msgbox_html + '</div>';

        flib_msgbox_close = _$("flib_msgbox_btnclose");
        flib_msgbox_close.onclick = this.close;
        //document.body.style.overflowX = "hidden";
        //document.body.style.overflowY = "hidden";
        if (document.addEventListener) {
            window.addEventListener("resize", this.relayout, true);
        } else {
            window.attachEvent("onresize", this.relayout);
        }
        this.relayout();
        //$("#maskBG").fadeIn("fast");
        $("#flib_msgbox").fadeIn();

        return;
    },
close: function() {
           $("#maskBG").fadeOut("fast");
           $("#flib_msgbox").fadeOut("slow");
           //$("#flib_msgbox").html('');

           document.body.style.overflowX = "";
           document.body.style.overflowY = "";
       },
startMove: function() {
               if (event.button == 1) {
                   _$("flib_msgbox").style.left = event.clientX;
               }
           },
relayout: function() {
              scrollTop = fss.browser.scrollTop();
              scrollLeft = fss.browser.scrollLeft();

              w = fss.Dialog.w;
              h = fss.Dialog.h;
              msgboxTop = scrollTop + ((document.documentElement.clientHeight - h) / 2);
              if (msgboxTop < 0) msgboxTop = 10;

              $("#flib_msgbox").css({
                      "width": w + "px",
                      "height": "auto",
                      "top": ($(document).scrollTop() + 60) + "px",
                      "left": ($(document).width() - w) / 2 + "px"
                      });
              $("#flib_msgbox").draggable();

          }
};

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != - 1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != - 1 && ! is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
var is_safari = (userAgent.indexOf('webkit') != - 1 || userAgent.indexOf('safari') != - 1);

fss.status = {
mouseX: 0,
        mouseY: 0,
        msgBox_top: 0,
        msgBox_left: 0
};
fss.browser = {
version: (userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [])[1],
         safari: /webkit/.test(userAgent),
         opera: /opera/.test(userAgent),
         msie: /msie/.test(userAgent) && ! /opera/.test(userAgent),
         mozilla: /mozilla/.test(userAgent) && ! /(compatible|webkit)/.test(userAgent),
         scrollTop: function() {
             if (window.innerWidth) { // all but IE
                 return window.pageYOffset;
             } else if (document.documentElement && document.documentElement.clientWidth) { // IE 6
                 // with
                 // DOCTYPE
                 return document.documentElement.scrollTop;
             } else if (document.body.clientWidth) { // IE other version without
                 // DOCTYPE
                 return document.body.scrollTop;
             }
         },

scrollLeft: function() {
                if (window.innerWidth) { // all but IE
                    return window.pageXOffset;
                } else if (document.documentElement && document.documentElement.clientWidth) { // IE 6
                    // with
                    // DOCTYPE
                    return document.documentElement.scrollLeft;
                } else if (document.body.clientWidth) { // IE other version without
                    // DOCTYPE
                    return document.body.scrollLeft;
                }
            }

};
fss.ajax = {
xmlhttp: false,
         init: function() {
             var xhr = null;

             if (window.ActiveXObject) {
                 var versions = ['Microsoft.XMLHTTP', 'MSXML6.XMLHTTP', 'MSXML5.XMLHTTP', 'MSXML4.XMLHTTP', 'MSXML3.XMLHTTP', 'MSXML2.XMLHTTP', 'MSXML.XMLHTTP'];

                 for (var i = 0; i < versions.length; i++) {
                     try {
                         xhr = new ActiveXObject(versions[i]);
                         break;
                     } catch(ex) {
                         continue;
                     }
                 }
             } else {
                 xhr = new XMLHttpRequest();
             }
             xmlhttp = xhr;

         },
         load: function(src, _obj) {
                   obj = "";
                   if (typeof(_obj) == "string") {
                       obj = _$(_obj);
                   } else if (typeof(_obj) == "object") {
                       obj = _obj;
                   }
                   obj.innerHTML = "正在加载...";
                   var action = src + '&in_ajax=1&time=' + new Date().getTime();

                   $.ajax({
                       type: "get",
                       url: action,
                       beforeSend: function(XMLHttpRequest) { },
                       success: function(data, textStatus) {
                           obj.innerHTML = data;
                           evalscript(data);
                       },
                       complete: function(XMLHttpRequest, textStatus){  },
                       error: function(){ }
                   });
      },
get: function(src) {
         this.init();

         var action = src + '&time=' + new Date().getTime();

         try {
             xmlhttp.open("GET", action, true);
             xmlhttp.setRequestHeader("Content-Type","text/xml; charset=UTF-8");
             xmlhttp.send(null);
         } catch(e) {
             return "";
         }

         xmlhttp.onreadystatechange = function() {
             if (2 == xmlhttp.readyState) {
                 // document.getElementById(target).innerHTML = "正在加载...";
             }
             if (4 == xmlhttp.readyState) {
                 if (200 == xmlhttp.status) {
                     if (xmlhttp.responseText == '-1') {
                         return "";
                         // document.getElementById(target).innerHTML = "未知错误";
                     } else {
                         // document.getElementById(target).innerHTML='';
                         // document.getElementById(target).innerHTML=xmlhttp.responseText;
                         return xmlhttp.responseText;
                     }

                 } else {
                     alert("发生错误!请查看您是否已经联网。或联系网站负责人");
                 }
             }
         };
     }
};

var evalscripts = new Array();
function appendscript(src, text, reload, charset) {
    var id = hash(src + text + new Date());
    if (!reload && in_array(id, evalscripts)) return;
    if (reload && _$(id)) {
        _$(id).parentNode.removeChild(_$(id));
    }

    evalscripts.push(id);
    var scriptNode = document.createElement("script");
    scriptNode.type = "text/javascript";
    scriptNode.id = id;
    scriptNode.charset = charset;
    try {
        if (src) {
            scriptNode.src = src;
        }
        else if (text) {
            scriptNode.text = text;
        }
        _$('append_parent').appendChild(scriptNode);
    }
    catch(e) {}
}

function evalscript(s) {
    if (s.indexOf('<html') > 0) return '';

    if (s.indexOf('<script') == - 1) return s;
    var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
    var arr = new Array();
    while (arr = p.exec(s)) {
        var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
        var arr1 = new Array();
        arr1 = p1.exec(arr[0]);
        if (arr1) {
            appendscript(arr1[1], '', arr1[2], arr1[3]);
        }
        else {
            p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
            arr1 = p1.exec(arr[0]);
            if (arr1[2]) {
                try {
                    eval(arr1[2]);
                } catch(e) { alert('Ajax Script Error(s):' + e); };
            }
        }
    }
    return s;
}
// 得到一个定长的hash值,依赖于 stringxor()
function hash(string, length) {
    var length = length ? length: 32;
    var start = 0;
    var i = 0;
    var result = '';
    filllen = length - string.length % length;
    for (i = 0; i < filllen; i++) {
        string += "0";
    }
    while (start < string.length) {
        result = stringxor(result, string.substr(start, length));
        start += length;
    }
    return result;
}
function stringxor(s1, s2) {
    var s = '';
    var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var max = Math.max(s1.length, s2.length);
    for (var i = 0; i < max; i++) {
        var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
        s += hash.charAt(k % 52);
    }
    return s;
}

function in_array(needle, haystack) {
    if (typeof needle == 'string' || typeof needle == 'number') {
        for (var i in haystack) {
            if (haystack[i] == needle) {
                return true;
            }
        }
    }
    return false;
}

function echo(content) {
    document.write(content);
}

fss.uploader = {
eid: 0,
     attType: "",
     inputid: "",
     build: function(form, eid, attType, inputid) {
         ret = "<a href=\"javascript:;\" onclick=\"";
         // ret += "mainForm='"+form + "';";
         ret += "fss.uploader.showDlg('" + eid + "','" + attType + "','" + inputid + "');";
         ret += "\">上传</a><div id=\"attarea\"></div>";
         echo(ret);
     },
showDlg: function(eid, attType, inputid) {
             valueattType = attType;

             FAjaxDlg('?m=Ajax&do=Upload&attType=' + attType + "&eid=" + eid + "&inputid=" + inputid);
         }
};

fss.fselector = {
oDiv: "",
      selectTitle: "",
      selectitem: function(item, node, nodeText, src) {
          _$(src).value = node;

          for (i = 0; i < item.parentNode.childNodes.length; i++) {
              node = item.parentNode.childNodes[i];
              node.className = node.className.replace("current", "");
          }
          item.className = "current";
          $('#fselect_' + src).html("<div style=\"margin-right:25px\">" + item.innerHTML + "</div>");
      },
handle: function(srcid) {
            var _src = $("#" + srcid);
            if (!_src[0]) return ;
            _src.hide();
            initValue = "";
            var selectBox = document.createElement("div");
            var oDivhtml;
            selectBox.style.margin = "-12px 0 0 5px";
            _src.before(selectBox);
            _src = _src[0];
            //srcid= new Date().getTime();



            oDivhtml = "<div class='select_menu' id='fselect_ctrl_" + srcid + "' style='display:none;' onclick=\"this.style.display='none';\">";
            oDivhtml += "<ul style=\"margin-right:25px\">";
            for (i = 0; i < _src.options.length; i++) {
                oDivhtml += "<li style=\"margin:0 5px 0 5px;\" ";
                node = _src.options[i];
                if (node.selected) {
                    initValue = node.innerHTML;
                    oDivhtml += "class=\"current\" ";
                }
                oDivhtml += "onclick=\"fss.fselector.selectitem(this,'" + node.value + "','" + node.innerHTML + "','" + srcid + "');\">" + node.innerHTML + "</li>";
            }
            oDivhtml += "</ul></div>";

            oDivhtml = "<div id='fselect_" + srcid + "' onclick=\"($('#fselect_ctrl_" + srcid +"').css('display')) == 'none' ? $('#fselect_ctrl_" + srcid +"').show() : $('#fselect_ctrl_" + srcid +"').hide();\" class=\"select_ctrl_label\">"+
                "<div style=\"margin-right:25px\">" + initValue + "</div></div>" + oDivhtml;

            selectBox.innerHTML = oDivhtml;
            /*
             * if(src.width>0){ //_$("fSelect_ctrl_" + src.id).style.width =
             * (src.width + 30)+"px"; //_$("fSelect_" + src.id).style.width =
             * (src.width + 30) +"px"; }else{ //_$("fSelect_ctrl_" +
             * src.id).style.width = "100px"; //_$("fSelect_" + src.id).style.width =
             * "100px"; }
             */
            //_src.style.display = 'none';
        }
};

var citySelectorItem = false;
fss.citySelector = {
initvalue: "请选择",
           text: "",
           province: "",
           city: "",
           lastselectp: 0,
           init: function() {
               html = "<input autocomplete='off' type=text id='province' name='province' value='" + this.province + "' /> 城市：<input autocomplete='off' type=text id='city' name='city' value='" + this.city + "'/>";
               document.write("<div id='cityinfo' style='display:block;' onclick='fss.citySelector.build();'>" + html + "</div>");
           },
build: function() {
           ps = arr_city[0].length;
           this.text = "<ul id='provincelist'>";
           for (i = 0; i < ps; i++) {
               this.text += "<li id='cityselect_p" + i + "' ";
               this.text += " ";
               this.text += "onclick=\"fss.citySelector.selectProvince(" + arr_city[0][i][0] + ",'" + arr_city[0][i][1] + "'," + i + ");\">";
               this.text += "<a>" + arr_city[0][i][1] + "</a></li>";
           }
           fss.Dialog.title = "请选择城市：";
           this.text += "</ul><div class='c'></div>";
           // this.text += "<div id='city_selector_cityTitle'
           // style='display:block;'></div>";
           fss.Dialog.msg = this.text + "<div class='c'></div><div style='margin:5px 0 0 0'; id='cityarea'></div>";
           fss.Dialog.w = "630";
           fss.Dialog.show();
           // $("#flib_msgbox").css({"width":"920px"});
           this.bandEvent();
       },
selectProvince: function(pid, obj, parrid) {
                    _$("cityselect_p" + fss.citySelector.lastselectp).style.background = '#ffffff';
                    _$("cityselect_p" + parrid).style.background = '#FFFF67';
                    fss.citySelector.lastselectp = parrid;
                    cs = arr_city[1].length;
                    this.province = obj;
                    // _$('city_selector_cityTitle').innerHTML = "ddddddddddddd";//<div
                    // style='' ><font style='color:red'>" + obj + "</font> 下的所有市区 ：</div>";
                    cityDiv = "<ul id='citylist'>";
		    cityDiv += "<li onclick=\"fss.citySelector.selectCity(0, '', " + parrid + ");\"><a>默认</a></li>";
                    for (i = 0; i < cs; i++) {
                        if (arr_city[1][i][1] == pid) {
                            cityDiv += "<li onclick=\"fss.citySelector.selectCity(" + i + ",'" + arr_city[1][i][0] + "'," + parrid + ");\"><a>" + arr_city[1][i][0] + "</a></li>";
                        }
                    }
                    cityDiv += "</ul><div style='clear:both;'></div>";
                    _$('cityarea').innerHTML = cityDiv;
                },
selectCity: function(cityid, city, parrid) {
                this.province = arr_city[0][parrid][1];
                this.city = city;
                _$('province').value = this.province;
                _$('city').value = this.city;
                // 有些查询页面也调用了这个地区选择器，但是没有邮编等，所以做一下判断
                if(_$('zip')) {
                    _$('zip').value = arr_city[1][cityid][4];
                }
                if(_$('region')) {
                    _$('region').value = arr_city[1][cityid][3];
                }
                fss.Dialog.close();
            },
bandEvent: function() {
               /*
                * _$("citySelector").onclick=function(){ if(!citySelectorItem){
                * citySelectorItem = true; _$("citySelector").className = "on"; }else{
                * citySelectorItem = false; _$("citySelector").className = "off"; } };
                */
           },
show: function(province, city) {
          if (arguments[0]) this.province = province;
          if (arguments[1]) this.city = city;
          this.init();

      },
hidden: function() {

        }
};
function setInputStyle() {
    if (document.all && document.getElementById) {
        navRoot = document.getElementsByTagName("input");

        for (i = 0; i < navRoot.length; i++) {
            node = navRoot[i];
            node.onmouseover = node.onfocus = function() {
                this.className += " input_hover";
            };
            node.onmouseout = node.onblur = function() {
                this.className = this.className.replace(" input_hover", "");
            };
        }
    }
}

function getExt(path) {
    return path.lastIndexOf('.') == - 1 ? '': path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
}

function setSort(sortid) {
    if (!_$("catid").value.match( new RegExp(sortid, "g"))) {
        _$("catid").value = _$("catid").value.replace(/\.*$/, '');
        _$("catid").value += "." + sortid;
    }
}
function removeSort(sortid) {
    $("#catid").val($("#catid").val().replace(sortid+'.',''));
}

function selectThisrow(rowID) {
    ckecked = $("#ckbox_"+rowID)[0].checked;
    if (ckecked) {
        $("#tr_"+rowID).css({"background":"#fff"});
        $("#ckbox_"+rowID)[0].checked = false;
    }
    else {
        $("#tr_"+rowID).css({"background":"#DDD"});
        $("#ckbox_"+rowID)[0].checked = true;
    }
}

function listcategory(cid) {
    $('#flib_msgbox_content').load('/ajax/?m=Ajax&do=categorylist&pid='+cid+'&selected='+$('#catid').val());
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

function flib() {
}

flib.message = function() {
}

var ajax_forms = new Array();
flib.message.send = function(to_uid, to_username) {

    html = '<form ajax="true" id="message_send_form" method="post" action="/message/send"><div style="padding:10px;"><table class="tmain" cellspacing="1" cellpadding="3" style="margin-bottom:10px;"><tr><th>收件人</th><td>' + to_username + '<input type="hidden" name="to_uid" style="width:200px;" value="' + to_uid + '" /></td></tr></table><div style="width:98%;margin:auto;"><textarea name="message_contents" style="width:250px; height:100px;"></textarea><br /><button type="submit" class="button primary green">发送</button></div></div></form>';

    //if 
    $.fancybox(html);
    apply_ajax_form();
}
