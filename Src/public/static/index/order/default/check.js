function check() {
    if ($('#realname').val()==""){
        alert('请填写姓名！');
        return false;
    }
    if ($('#realname').val().length>10){
        alert('姓名长度不能大于10位！');
        return false;
    }
    if ($('#tel').val()==""){
        alert('请填写手机号码！');
        return false;
    }else{
        //var pattern = /^(13[0-9]{9})|(15[0-9]{9})|(14[0-9]{9})|(16[0-9]{9})|(19[0-9]{9})|(17[0-9]{9})|(18[0-9]{9})$/;
        var pattern = /^(1[0-9]{10})$/;
        flag = pattern.test($('#tel').val());
        if(!flag)
        {
            alert("您输入的手机号有误！");
            return false;
        }
    }
    if ($('#s_province').val()=="" || $('#s_province').val()==null || $('#s_province').val()=="省份"){
        alert('请选择省份！');
        return false;
    }
    if ($('#s_city').val()=="" || $('#s_city').val()==null || $('#s_city').val()=="地级市"){
        alert('请选择地级市！');
        return false;
    }
    if ($('#s_county').val()=="" || $('#s_county').val()==null || $('#s_county').val()=="市、县级市"){
        order_county="";
        return false;
    }else {
        order_county=$('#s_county').val();
    }
    if ($('#addr').val()=="" ){
        alert('请填写详细地址！');
        return false;
    }
    $('#addresss').val($('#s_province').val()+'/@/'+$('#s_city').val()+'/@/'+order_county+'/@/'+$('#addr').val());
    $('#pro_options').val($('.sku_cur').text());
    $('#pro_url').val(getParentUrl());
    $('#from_url').val($.getUrlParam("from_url"));
}

function getParentUrl() {//获取iframe父页面URL
    var url = null;
    if (parent !== window) {
        try {
            url = parent.location.href;
        }catch (e) {
            url = document.referrer;
        }
    }else {
        url = window.location.href;
    }
    return url;
}

(function ($) {
    $.getUrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return decodeURI(r[2]); // decodeURI(r[2]); 解决参数是中文时的乱码问题

        return "";
    }
})(jQuery);

function updateKey(){
    var src = $('img[name="plKeyImg"]').attr('src');
    $('img[name="plKeyImg"]').attr('src',src+'?t='+Math.random());
}