var ddload = {
    getheight: function() {
        if ($(window).width() < 750) {
            $(".ddfhcont").css("height", 190)
        } else {
            $(".ddfhcont").css("height", $(".ddform").height() - 172 + 90)
        }
        var ddheight = $(".ddorder").height() + 15;
        document.cookie = "ddheight=" + ddheight + ";path=/"
    }
};

function total() {
    var d = $(':radio[name="pro_options"]:checked').attr('price');
    var n = $("#quantity").val();
    var p = parseFloat(d) * n;
    $("#quantity").val(n);
    $("#amount").val(p.toFixed(2));
    $("#showprice").html(p.toFixed(2))
}
function numlnc() {
    var d = $(':radio[name="pro_options"]:checked').attr('price');
    var n = $("#quantity").val();
    var n = parseInt(n) + 1;
    var p = n * parseFloat(d);
    $("#quantity").val(n);
    $("#amount").val(p.toFixed(2));
    $("#showprice").html(p.toFixed(2))
}
function numdec() {
    var d = $(':radio[name="pro_options"]:checked').attr('price');
    var n = $("#quantity").val();
    var n = parseInt(n) - 1;
    var p = n * parseFloat(d);
    if (n < 1) {
        alert('数量不能小于1件！');
        return false
    }
    $("#quantity").val(n);
    $("#amount").val(p.toFixed(2));
    $("#showprice").html(p.toFixed(2))
}

function onprice() {
    var n = $("#quantity").val();
    var d = $(':radio[name="pro_options"]:checked').attr('price');
    var p = n * parseFloat(d);
    if (n < 1) {
        alert('数量不能小于1件！');
        return false
    }
    $("#quantity").val(n);
    $("#amount").val(p.toFixed(2));
    $("#showprice").html(p.toFixed(2))
}

function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg)) return unescape(arr[2]);
    else return ''
}

$(function(e) {
    try {
        ddload.getheight();
        total();
    } catch(e) {}
});