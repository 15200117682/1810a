<div style="margin-top:70px;margin-left:70px;">
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>菜单添加</title>
    </head>
    <body>
    <form action="/admin/menumedo"method="POST" enctype="multipart/form-data"><br>
        菜单名字<input type="text" name="menu_name"><br><br>
        菜单类型<input type="text" name="menu_type"><br><br>
        key  键值<input type="text" name="menu_key" placeholder="请输入key值"><br><br>
        <input type="submit"><br>
    </form>
    </body>
    </html>
</div>