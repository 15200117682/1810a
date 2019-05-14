<?php

namespace App\Admin\Controllers;

use App\Model\ImgModel;
use App\Http\Controllers\Controller;
use App\Model\UsersModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class MassController extends Controller
{
    use HasResourceActions;

    /**
     * 群发页面
     */
    public function MassAll(Content $content)
    {
//        echo 111;exit;
        $access_token =getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=';
        $data = json_decode(file_get_contents($url), true);

        $data = $data['data']['openid'];
        return $content
            ->header('微信')
            ->description('群发列表')
            ->body(view('mass.mass')->with('data', $data));
    }

    /**
     * 执行群发
     */
    public function MassAllAdd()
    {
        $openid = $_POST['openid'];
        $media_id = $_POST['media_id'];
        $type = $_POST['type'];
        $access_token =getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access_token";
        if ($type == "text") {
            $data = [
                "touser" => $openid,
                "msgtype" => "text",
                "text" => [
                    "content" => "$media_id"
                ]
            ];
        }
        /*else if ($type == "mpnews") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else if ($type == "mpvideo") {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id",
                    "title" => "TITLE",
                    "description" => "DESCRIPTION"
                ],
                "msgtype" => "$type",
                "send_ignore_reprint" => 0
            ];
        } else {
            $data = [
                "touser" => $openid,
                "$type" => [
                    "media_id" => "$media_id"
                ],
                "msgtype" => "$type"
            ];
        }*/
        $data=json_encode($data,true);
        $data=curlPost($url,$data);
        $data=json_decode($data,true);
        var_dump($data);exit;
        if ($data['errcode'] == 0) {
            return 'ok';
        }
    }

}