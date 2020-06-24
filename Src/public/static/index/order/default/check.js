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
    if ($('#s_province').val()=="" || $('#prov').val()=="省份"){
        alert('请选择省份！');
        return false;
    }
    if ($('#s_city').val()=="" || $('#city').val()=="城市"){
        alert('请选择城市！');
        return false;
    }
    if ($('#s_county').val()=="" || $('#area').val()=="地区"){
        alert('请选择地区！');
        return false;
    }
    if ($('#addr').val()=="" ){
        alert('请填写地址！');
        return false;
    }
    $('#addresss').val($('#s_province').val()+'/@/'+$('#s_city').val()+'/@/'+$('#s_county').val()+'/@/'+$('#addr').val());
    $('#pro_options').val($('.sku_cur').text());
    $('#amount').val($('.sku_cur').text());
    $('#pro_url').val(getParentUrl());
}

function getParentUrl() {//获取iframe父页面URL
    var url = null;
    if (parent !== window) {
        try {
            url = parent.location.href;
        }catch (e) {
            url = document.referrer;
        }
    }
    return url;
}