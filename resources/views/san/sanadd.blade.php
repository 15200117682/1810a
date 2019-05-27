<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>题目添加</title>
</head>
<body>
    <div style="width:50%;">
        <form class="form-horizontal" action="/admin/san/sanjia" method="POST" enctype="multipart/form-data">
            题目名称：<input type="text" name="wx_name" class="form-control">
            答案A&nbsp;：<input type="text" name="wx_a" class="form-control" value="正确">
            答案B&nbsp;：<input type="text" name="wx_b" class="form-control" value="错误"><br/><br/>
            正确答案<select name="wx_cor" style="width:30%;height:30px;">
                <option  value="A">A</option>
                <option  value="B">B</option>
            </select><br><br>
            <input type="submit" class="btn btn-primary submit">
        </form>
    </div>
</body>
</html>