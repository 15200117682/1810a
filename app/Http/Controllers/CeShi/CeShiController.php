<?php

namespace App\Http\Controllers\CeShi;

use function GuzzleHttp\Psr7\_caseless_remove;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CeShiController extends Controller
{

    //首次接入
    public function getCe(){
        echo $_GET["echostr"];
    }

    //之后接入
    public function getWechat(){
        $data = file_get_contents("php://input");//接受post数据
        $time = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";//存时间
        file_put_contents("logs/ce_event.log", $time, FILE_APPEND);//存到日志文件
        $obj = simplexml_load_string($data);//将xml数据转换成对象格式的数据
        var_dump($obj);
    }
}
