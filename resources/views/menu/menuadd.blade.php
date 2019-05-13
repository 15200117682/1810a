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
    <form action="/admin/menumedo" method="POST" enctype="multipart/form-data"><br>
        菜单名字：<input type="text" name="menu_name" placeholder="请输入菜单名" style="width:10%;height:35px;"><br><br>
        菜单父类：<select name="p_id" style="width:10%;height:30px;">
            <option value="0">--请选择所属菜单--</option>
            <?php foreach($data as $k=>$v){ ?>
            <option  value="<?php echo $v['menu_id'] ?>"><?php echo $v['menu_name'] ?></option>
            <?php } ?>
        </select><br><br>
        菜单类型：<input type="text" name="menu_type" style="width:10%;height:35px;" placeholder="请选择菜单类型"><br><br>
        key  键值：<input type="text" name="menu_key" style="width:10%;height:35px;" placeholder="请输入key值"><br><br>
        <input type="submit" value="提交" style="color:#2b2b2b;background-color:#0d6aad;width:70px;height:40px;font-size:20px;border:none;"><br>
    </form>
    </body>
    </html>
</div>