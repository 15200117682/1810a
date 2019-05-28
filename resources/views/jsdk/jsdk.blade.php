<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>jssdk</title>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script src="/js/jquery-3.2.1.min.js"></script>
</head>
<body>

</body>
</html>
<script>
    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: '{{$appId}}', // 必填，公众号的唯一标识
        timestamp: '{{$timestamp}}', // 必填，生成签名的时间戳
        nonceStr: '{{$nonceStr}}', // 必填，生成签名的随机串
        signature: '{{$signature}}',// 必填，签名
        jsApiList: ["updateAppMessageShareData"] // 必填，需要使用的JS接口列表
    });
    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        wx.updateAppMessageShareData({
            title: '缘分', // 分享标题
            desc: '淮竹本是妾，霸业本无妻', // 分享描述
            link: 'http://yuan.qiong001.com/admin', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://yuan.qiong001.com/images/2019-05-14/31df49e305ba04046be0753b67201e70.jpg', // 分享图标
            success: function () {
                // 设置成功
            }
        })
    });
</script>