
var layer_skin_color=new Array(
    "#4d6176",
    "#666666",
    "#ffa4d1",
    "#1aa094",
    "#1e9fff",
    "#ffb800",
    "#e82121",
    "#963885",
    "#2D8CF0",
    "#ffb800",
    "#e82121",
    "#963885");

var layuimini_bgColorDefault=8; // 主题默认配置

function layer_skin(layer) {
    var bgcolorId = sessionStorage.getItem('layuiminiBgcolorId');
    if (bgcolorId === null || bgcolorId === undefined || bgcolorId === '') {
        bgcolorId = layuimini_bgColorDefault;
        sessionStorage.setItem('layuiminiBgcolorId', bgcolorId);
    }
    $('<style>').html('body .caozha-layer-'+bgcolorId+' .layui-layer-title{background:'+layer_skin_color[bgcolorId]+';}\n' +
        'body .caozha-layer-'+bgcolorId+' .layui-layer-btn0{border-color: '+layer_skin_color[bgcolorId]+';}\n' +
        'body .caozha-layer-'+bgcolorId+' .layui-layer-btn a{background:'+layer_skin_color[bgcolorId]+';}\n' +
        'body .caozha-layer-'+bgcolorId+' .layui-layer-btn .layui-layer-btn1{background:#fff;}\n' +
        'body .caozha-submit{background:'+layer_skin_color[bgcolorId]+';}').appendTo($('head'));

   //全局使用。即所有弹出层都默认采用，但是单个配置skin的优先级更高
    layer.config({
        skin: "caozha-layer-"+bgcolorId,
    })
}
