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
<table class="table table-striped">
    <tr>
        <td style="font-size:30px;">标签名</td>
    </tr>
    <tr>
        <td style="font-size:20px;" id="tag_wx_id" value="{{$data['tag_wx_id']}}">{{$data['tag_name']}}</td>
    </tr>
</table>
<br/>
<table class="table table-striped">
    <tr>
        <td><input type="checkbox" id="quan"></td>
        <td>编号</td>
        <td>用户名</td>
        <td>openid</td>
    </tr>
    @foreach($datainfo as $k=>$v)
    <tr>
        <td><input type="checkbox" name="vehicle" class="vehicle" value="{{$v['id']}}"></td>
        <td>{{$v['id']}}</td>
        <td>{{$v['nickname']}}</td>
        <td class="openid" value="{{$v['openid']}}">{{$v['openid']}}</td>
    </tr>
    @endforeach
</table>
<form class="form-horizontal">
    <input type="hidden" id="tag_wx_id" value="{{$data['tag_wx_id']}}">
    <input type="button" id="sub" class="btn btn-primary submit" value="加入标签">
</form>
</body>
</html>
<script>

    $('#quan').click(function(){
        var type=$('#quan').prop('checked');
        $('.vehicle').prop('checked',type);
    })

    $('.vehicle').click(function(){
        if($(this).prop('checked')==false){
            $('#c').prop('checked',false);
        }
    })

    //点击发送
    $('#sub').click(function(){
        openid = [];
        $("input[name='vehicle']:checked").each(function(i){
            openid[i] = $(this).parent('td').next().next().next().text();
        });
        var tag_wx_id=$("#tag_wx_id").attr('value');

        $.ajax({
            url : '/admin/maketag',
            data:{openid:openid,tag_wx_id:tag_wx_id},
            type:'post',
            dataType:'json',
            success:function(msg){
               if(msg.errmsg=="ok"){
                   alert("给用户打标签成功");
                   window.location.href = '/admin/taglist';
               }
            }
        })

    })
</script>
