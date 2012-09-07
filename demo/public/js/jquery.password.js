$.fn.passwordStrength = function(options){
    var PW_Strength;
    return this.each(function(){
            var that = this;that.opts = {};
            that.opts = $.extend({}, $.fn.passwordStrength.defaults, options);

            that.div = $(that.opts.targetDiv);
            that.defaultClass = that.div.attr('class');

            that.percents = (that.opts.classes.length) ? 100 / that.opts.classes.length : 100;
            v = $(this).keyup(function(){
                    if( typeof el == "undefined" )
                        this.el = $(this);
                    var s = getPasswordStrength (this.value);
                    var p = this.percents;
                    var t = Math.floor( s / p );
                    if( 100 <= s ) t = this.opts.classes.length - 1;
                    PW_Strength = t+1;
                    this.div.removeAttr('class').addClass( this.defaultClass ).addClass( this.opts.classes[ t ]);	
            });

            //光标失焦
            $(this).blur(function(){
                    CheckPW($(this));
            });
    });

    function CheckPW(obj){
        if(obj.val().length > 0 && obj.val().length < 6){
            alert('密码太短，要求至少6位');
            obj.select();
            return false;
        }
        /*if(PW_Strength<8){
            alert('密码强度不够，建议重设！');
            obj.select();
            return false;
        }*/
    }
    //获取密码强度
    function getPasswordStrength(H){
        var D=(H.length);
        if(D>5){
            D=5
        }
        var F=H.replace(/[0-9]/g,"");
        var G=(H.length-F.length);
        if(G>3){G=3}
        var A=H.replace(/\W/g,"");
        var C=(H.length-A.length);
        if(C>3){C=3}
        var B=H.replace(/[A-Z]/g,"");
        var I=(H.length-B.length);
        if(I>3){I=3}
        var E=((D*10)-20)+(G*10)+(C*15)+(I*10);
        if(E<0){E=0}
        if(E>100){E=100}
        return E
    }

};

$.fn.passwordStrength.defaults = {
    classes : Array('is10','is20','is30','is40','is50','is60','is70','is80','is90','is100'),//样式名
    targetDiv : '#passwordStrengthDiv',
    cache : {}
}
$.passwordStrength = {};
$.passwordStrength.getRandomPassword = function(size){
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    var size = size || 8;
    var i = 1;
    var ret = ""
    while ( i <= size ) {
        $max = chars.length-1;
        $num = Math.floor(Math.random()*$max);
        $temp = chars.substr($num, 1);
        ret += $temp;
        i++;
    }
    return ret;			
}
