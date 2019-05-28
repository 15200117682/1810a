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
        jsApiList: [] // 必填，需要使用的JS接口列表
    });
</script>