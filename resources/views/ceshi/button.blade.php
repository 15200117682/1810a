<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header>
    <nav class="b_clear">
        <div class="nav_logo l_float">
            <img src="/images/imgs/logo.svg" alt="">
        </div>
        <div class="nav_link r_float">
            <ul>
                <li><a href="#">返回首页</a></li>
                <li><a href="#">关于我们</a></li>
                <li><a href="#">联系我们</a></li>

            </ul>
        </div>
    </nav>
</header>
<div class="container">
    <div class="login_body l_clear">
        <div class="login_form l_float">
            <div class="login_top">
                <img src="/images/imgs/logo_z.svg" alt="" class="">

                <div class="login_tags b_clear">
                    <span class="top_tag l_float active" style="cursor:pointer" onClick="PwdLogin()">密码登录</span>
                    <span class="top_tag r_float" style="cursor:pointer" onClick="QrcodeLogin()">扫码登录</span>
                </div>
            </div>
            <div class="login_con">
                <form action="/ceshi/button" method="POST" enctype="multipart/form-data">
                    <div>
                        <label for="user_name">用户名</label>
                        <input type="text" name="wx_name" id="user_name" placeholder="账号/手机号/邮箱">
                        <img src="/images/imgs/icons/user.svg">
                        <p class="tips hidden">请检查您的账号</p>
                    </div>
                    <div>
                        <label for="user_pwd">密码</label>
                        <input type="password" name="wx_pwd" id="user_pwd" placeholder="请输入账户密码">
                        <img src="/images/imgs/icons/lock.svg">
                        <p class="tips hidden">请检查您的密码</p>
                    </div>
                    <div class="b_clear">
                        <label for="auth_code" class="b_clear">验证码</label>
                        <input type="text" name="" id="auth_code" placeholder="" class="l_float" maxlength="6">

                        <button class="auth_code l_float">获取验证码</button>
                        <img src="/images/imgs/icons/auth_code.svg">
                        <p class="tips hidden">验证码错误</p>

                    </div>
                    <div class="b_clear submit">

                        <button type="submit">登&nbsp;&nbsp;录</button>
                        <a href="#" class="r_float">忘记密码？</a>
                        <p class="tips hidden">登录失败，请检查您的账户与密码</p>
                    </div>
                </form>
            </div>
            <div class="login_con hidden">
                <div class="qr_code">
                    <img src="/images/imgs/qr.png" alt="">
                    <p>请使用微信扫码登录<br>仅支持已绑定微信的账户进行快速登录</p>
                </div>

            </div>
            <div class="login_otherAccount">
                <span>第三方登录</span>
                <a href="/ceshi/auth"><img src="/images/imgs/icons/wechat.svg" alt="微信登录"></a>
                <a href="http://"><img src="/images/imgs/icons/weibo.svg" alt="微博登录"></a>
                <a href=""><img src="/images/imgs/icons/qq.svg" alt="QQ登录"></a>
            </div>

        </div>
        <div class="login_ad l_float" id="AdImg">
            <a href="">查看详情</a>
        </div>
    </div>
    <div class="footer">
        <p>Copyright © 2013-2018  <a href="#">赫伟创意星空</a></p>
        <a href="#" target="_blank"><img src="/images/imgs/icons/icp_record_filing.svg" alt="工信部备案">蒙ICP备15000557号</a>更多模板：<a href="http://www.mycodes.net/" target="_blank">源码之家</a>
    </div>
</div>

<script src="/js/login.js"></script>
</body>
</html>