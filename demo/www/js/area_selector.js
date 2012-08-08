// JavaScript Document
//使用须知，需要用到id为province,city的下拉列表
var url='/js/area_data.js';
var provinceId=0;
var cityId=0;

function initArea() {

    $('#province').html('');

    //初始化省份
    $.getJSON(url,function(data) {
            $.each(data, function(i,current) {
                    $('#province').append("<option value='"+current.id+"'>"+current.name+"</option>");
            });

            //初始化默认县级市
            if(provinceId != "" && provinceId != undefined) {
                $('#province').attr('value',provinceId);
                initCity(provinceId, 1);

            }
            else {
                //如果用户信息已经存在，则获取用户信息，否则加载默认信息
                $('#province').val(17);

                initCity(17);
            }
    });


    //为省份绑定change事件
    $('#province').change(function() {
            provinceid=$('#province').val();

            initCity(provinceid,0);

    });
    //为地区绑定change事件
    $('#city').change(function(){
        $('#city_name').val($('#city').find('option:selected').text());
    });

}

function initCity(provinceId,isload) {
    $('#city').html('');

    $.getJSON(url,function(data) {
            $.each(data, function(i,current) {
                    if(current.id==provinceId) {
                        //获取当前省份，遍历下面的县级市
                        var arr=current.citys;

                        $.each(arr,function(ii,curr) {
                                //在进行each,因为返回的json数据没有固定的键
                                $.each(curr,function(name,value) {
                                        $('#city').append("<option value='"+name+"'>"+value+"</option>");
                                });
                        });

                        if (isload==1) {
                            $('#city').attr('value',cityId);
                        }
                    }
            });
        $('#city_name').val($('#city').find('option:selected').text());
    });

    //重置province_name,city_name
    $('#province_name').val($('#province').find('option:selected').text());

}

$().ready(function() {


        html = '<select name="province_id" id="province"></select><select name="city_id" id="city"></select>';
        html+='<input type="hidden" id="province_name" name="province_name"><input type="hidden" id="city_name" name="city_name">';
        area_selector = $('#area_selector');
        provinceId = area_selector.attr('province');
        cityId = area_selector.attr('city');

        area_selector.html(html);

        initArea();
});
