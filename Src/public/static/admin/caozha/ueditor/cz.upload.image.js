/*

单独调用上传接口插件，欢迎访问：www.caozha.com

代码仓库: https://gitee.com/caozha/ueditor

*/
var caozha_editor;
$(function () {
//重新实例化一个编辑器，防止在上面的editor编辑器中显示上传的图片或者文件
    caozha_editor = UE.getEditor('upload_ue');
    caozha_editor.ready(function () {
//设置编辑器不可用
        caozha_editor.setDisabled();
//隐藏编辑器，因为不会用到这个编辑器实例，所以要隐藏
        caozha_editor.hide();

    });
});

//弹出图片上传的对话框
function upImage(pic_id) {
    var myImage = caozha_editor.getDialog("insertimage");
    myImage.open();
    //侦听图片上传
    caozha_editor.addListener('beforeInsertImage', function (t, arg) {
//将地址赋值给相应的input,只去第一张图片的路径
        $("#"+pic_id).attr("value", arg[0].src);
//图片预览
        //$("#preview").attr("src", arg[0].src);
    })
}

function  img_preview(pic_id) {
    window.open($("#"+pic_id).val(),'new');
}