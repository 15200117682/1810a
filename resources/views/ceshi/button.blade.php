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
<div class="container">
    <div class="login_body l_clear">
        <div class="login_form l_float">
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
                    <div class="b_clear submit">

                        <button type="submit">绑&nbsp;&nbsp;定</button>
                        <a href="#" class="r_float">忘记密码？</a>
                        <p class="tips hidden">登录失败，请检查您的账户与密码</p>
                    </div>
                </form>
            </div>


</div>

<script src="/js/login.js"></script>
</body>
</html>