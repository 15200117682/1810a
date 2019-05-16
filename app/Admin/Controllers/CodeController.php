<?php

namespace App\Admin\Controllers;

use App\Model\CodeModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    //渠道二维码关注统计
    public function code_ure(Content $content)
    {
        $data=CodeModel::all()->toArray();
        $name='';
        $number='';
        foreach ($data as $key=>$values){
            $name.="'".$values['code_name']."',";
            $number.=$values['code_number'].",";
        }
        $name=rtrim($name,",");
        $number=rtrim($number,",");
        $dataInfo=[
            'name'=>$name,
            'number'=>$number
        ];
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("code.codeure",['data'=>$dataInfo]));
    }

    //二维码添加
    public function codeadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("code.codeadd"));
    }

    //生成二维码
    public function codemedo(Request $request){
        $arr=$request->input();
        $code_name=$arr['code_name'];
        $code_key=$arr['code_key'];
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access";
        $data=[
            "expire_seconds"    =>604800,
            "action_name"       =>"QR_STR_SCENE",
            "action_info"=>[
                "scene" =>[
                    "scene_str"=>$arr['code_key']
                ]
            ]
        ];
        $json=json_encode($data,JSON_UNESCAPED_UNICODE);//转换数据类型
        $res = curlPost($url,$json);
        $res=json_decode($res,true);
        $ticket=$res["ticket"];
        //通过ticket获取二维码
        $codePath = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
        $img = file_get_contents($codePath);
        //var_dump($img);exit;
        //写入文件
        file_put_contents("./img/qrcode/$code_key.jpg",$img);
        //渠道二维码信息入库
        $datainfo=[
            'code_name'=>$code_name,
            'code_status'=>1,
            'code_img'=>$codePath,
            'code_number'=>NULL,
            'code_key'=>$arr['code_key']
        ];
        $res=CodeModel::insertGetId($datainfo);
        if($res){
            return redirect('/admin/codelist');
        }

    }


    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CodeModel);

        $grid->id('二维码编号');
        $grid->code_name('二维码名字');
        $grid->code_status('二维码状态状态');
        $grid->code_img('二维码图片')->display(function($code_img){
            return "<img src='$code_img' style='width:100px;height:100px;'>";
        });
        $grid->code_number('关注人数');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CodeModel::findOrFail($id));

        $show->id('Id');
        $show->code_name('Code name');
        $show->code_status('Code status');
        $show->code_img('Code img');
        $show->code_number('Code number');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CodeModel);

        $form->text('code_name', 'Code name');
        $form->number('code_status', 'Code status');
        $form->text('code_img', 'Code img');
        $form->number('code_number', 'Code number');

        return $form;
    }
}
