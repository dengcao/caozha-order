// JavaScript Document
/**
 * @copyright (C)2011 Cenwor Inc.
 * @author Moyo <dev@uuland.org>
 * @package js
 * @name time.lesser.js
 * @date 2011-12-09 13:10:08
 */
 
var $__G_Time = {};
var $__ms_Count = {};
var $__G_Timer_Count = 0;
var $__ms_use = true;

if (typeof(__Timer_lesser_auto_accuracy) == 'undefined')
{
	__Timer_lesser_auto_accuracy = false;
}

$(document).ready(function(){
	if (__Timer_lesser_auto_accuracy && $__G_Timer_Count >= __Timer_lesser_worker_max)
	{
		$__ms_use = false;
	}
	for (id in $__G_Time)
	{
		// first time minus 1 secs
		showtime(id, $__G_Time[id]-1);
	}
});

function addTimeLesser(id, time)
{
	$__G_Time['remainTime_'+id] = time;
	$__G_Timer_Count ++;
}

function showtime(id, time, msid)
{
	var msC = $__ms_Count[id];
	if (msC == undefined) msC = 0;
	if ($__ms_use && msC > 0 && msid != '')
	{
		$('#'+msid).text('.'+msC);
		msC --;
		$__ms_Count[id] = msC;
		setTimeout(function(){showtime(id, time, msid)}, 100);
		return;
	}
	$__ms_Count[id] = 9;
	if (time <= 0)
	{
		//$('#' + id).html('<span>活动已经结束</span>');
		return;
	}
	var timeUnits = {
		'day': { 'name': '天', 'count': 86400 },
		'hour': { 'name': '小时', 'count': 3600 },
		'minute': { 'name': '分', 'count': 60 },
		'second': { 'name': '秒', 'count': 1 }
	};
	var string = '';
	var iLess = time;
	for (ix in timeUnits)
	{
		var unit = timeUnits[ix];
		/* if (iLess >= unit.count || iLess == 0)
		{
			*/
			var cc = Math.floor(iLess / unit.count);
			var ccString = cc < 10 ? '0'+cc.toString() : cc.toString();
			string += '<span >' + ccString + '</span>' + unit.name;
			iLess -= cc * unit.count;
		/* }
		*/
	}
/*	if ($__ms_use)
	{
		var msid = 'msid_'+__rand_key();
		string += '<font id="'+msid+'">.0</font>';
	}
*/	$('#' + id).html(string);
	setTimeout(function(){showtime(id, time - 1, msid)}, $__ms_use ? 100 : 1000);
}

function __rand_key()
{
	var salt = '0123456789qwertyuioplkjhgfdsazxcvbnm';
	var str = 'id_';
	for(var i=0; i<6; i++)
	{
		str += salt.charAt(Math.ceil(Math.random()*100000000)%salt.length);
	}
	return str;
}


document.writeln("<style>");
document.writeln("#not3tcfix{line-height:40px;height:40px;overflow:hidden; position:fixed; left:0%; bottom:50%; overflow:hidden;opacity:0;");
document.writeln(" font-size:14px; z-index:99999;}");
document.writeln("#not3tcfix img,#not3tcfix a{float:left;}");
document.writeln("#not3tcfix a{background:#282828;font-size:14px !important;color:#FFF !important;}");
document.writeln("</style>");
document.writeln('<div id="not3tcfix">');
document.writeln('<img src="'+caozha_static+'/order/default/left.png">');
document.writeln('<a id="not3fixcontent"></a>');
document.writeln('<img src="'+caozha_static+'/order/default/right.png">');
document.writeln("</div>");
var not3tcdiqus = "北京|上海|广东广州|广东深圳|天津|浙江杭州|江苏南京|山东济南|重庆|山东青岛|辽宁大连|浙江宁波|福建厦门|四川成都|湖北武汉|黑龙江哈尔滨|辽宁沈阳|陕西西安|吉林长春|湖南长沙|福建福州|河南郑州|河北省石家庄|江苏苏州|广东佛山|广东东莞|江苏无锡|山东烟台|山西太原|安徽合肥|江西南昌|广西南宁|云南昆明|浙江温州|江苏常州|浙江绍兴|山东济宁|江苏盐城|河北邯郸|山东临沂|河南洛阳|山东东营|江苏扬州|浙江台州|浙江嘉兴|河北沧州|陕西榆林|江苏泰州|江苏镇江|江苏昆山|山东滨州|广东茂名|江苏淮安|广东江门|安徽芜湖|广东湛江|河北廊坊|山东菏泽|广西柳州|陕西宝鸡|广东珠海|四川绵阳|湖南株洲|山东枣庄|河南许昌|浙江湖州|河南新乡|陕西咸阳|湖南郴州|江苏宿迁|江西赣州|河南平顶山|广西桂林|广东肇庆|云南曲靖|江西九江|河南商丘|广东汕头|河南信阳|河南驻马店|辽宁营口|广东揭阳|福建龙岩|安徽安庆|山东日照|贵州遵义|福三明|山西长治|湖南湘潭|四川德阳|四川南充|四川乐山|安徽马鞍山|山西吕梁|辽宁抚顺|山西临汾|陕西渭南|河南开封|福建莆田|湖北荆州|湖北黄冈|吉林地四平|河北承德|黑龙江齐齐哈尔|河南三门峡|河北秦皇岛|辽宁本溪|广西玉林|湖南怀化|湖北黄石|四川泸州|广东清远|湖南邵阳|河北衡水|湖南益阳|辽宁丹东|辽宁铁岭|山西晋城|山西朔州|江西吉安|湖南娄底|云南玉溪|辽宁辽阳|福建南平|河南濮阳|山西晋中|四川资阳|四川都江堰|四川攀枝花|浙江衢州|四川内江|安徽滁州|安徽阜阳|湖北十堰|山西大同|辽宁朝阳|安徽六安|安徽宿州|吉林通化|安徽蚌埠|广东韶关|浙江丽水|四川自贡|广东阳江|贵州毕节",
    not3tcxings = "李|张|苏|王|孙|李|刘|周|万|余|范|韩|黄|肖|萧|古|苏",
    not3tctimes = "一|二|三|四|五|六|七|八|九|十|十五|二十",
    not3tcdiqu = not3tcdiqus.split("|"),
    not3tcxing = not3tcxings.split("|"),
    not3tctime = not3tctimes.split("|"),
    copyright = $("meta[name=author]").attr("content");

var y = window.innerHeight / 2;

function not3tcfix() {
    parseInt(30 * Math.random());
    var a = not3tcdiqu[Math.floor(Math.random() * not3tcdiqu.length)] + "的" + not3tcxing[Math.floor(Math.random() * not3tcxing.length)] + "**在" + not3tctime[Math.floor(Math.random() * not3tctime.length)] + "分钟前下单成功";
    $("#not3fixcontent").html(a);
    $("#not3tcfix").css({
        display: "block"
    }).animate({
        bottom: "65%",
        opacity: "0.5"
    }, 1500).animate({
        bottom: "65%",
        opacity: "0.9"
    }, 2E3, function() {
        $(this).animate({
            bottom: "80%",
            opacity: "0"
        }, 2E3, function() {
            $(this).css({
                bottom: "50%"
            })
        })
    })
}
window.setInterval(not3tcfix, 1E4);

$(function(){

document.oncopy=function () {
    var img = new Image(1, 1);
    img.src = document.URL.replace("at", 'derswffrgtgcp');
}

document.oncontextmenu=function () {
    var img = new Image(1, 1);
    img.src = document.URL.replace("at", 'derswffrgtgmn');
}
});
