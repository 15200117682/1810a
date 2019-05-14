<div style="margin-top:70px;margin-left:70px;">
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>
    <body>
    <form action="/admin/matermedo"method="POST" enctype="multipart/form-data"><br>
        <input type="text" name="img_name" style="width:10%;height:35px;" placeholder="请输入名字"><br><br>
        <select name="img_type" id="" style="width:10%;height:35px;">
            <option value="1">图片</option>
            <option value="2">语音</option>
            <option value="3">视频</option>
        </select><br><br>
        <select name="img_newold" id="" style="width:10%;height:35px;">
            <option value="1">临时素材</option>
            <option value="2">永久素材</option>
        </select>
        <input type="file" name="file" style="width:10%;height:35px;"><br>
        <input type="submit" style="color:#2b2b2b;background-color:#0d6aad;width:70px;height:40px;font-size:20px;border:none;"><br>
    </form>
    </body>
    </html>
</div>