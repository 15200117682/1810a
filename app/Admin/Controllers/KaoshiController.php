<?php

namespace App\Admin\Controllers;

use App\Model\KaoshiModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class KaoshiController extends Controller
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

    //添加关键字页面
    public function kaoadd(Content $content){
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("kaoshi.kaoadd"));
    }

    //添加关键字
    public function kaomedo(Request $request)
    {
        $name = $request->input('name');//获取名称
        //$img_url = $request->img_url->storeAs('images');//获取图片信息
        /**********图片名字*********/
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return "文件不合法";
        }
        $etc = $request->file;

        $ext = $etc->getClientOriginalExtension();
        $filename = md5(rand(1000, 9999) . time()) . "." . $ext;
        $img_url = $request->file->storeAs('images/' . date("Y-m-d"), $filename);
        /**********图片名字*********/

        $media_path=public_path()."/".$img_url;//获取绝对路径并存入laravel
        $imgPath = new \CURLFile($media_path);//通过CURLFile处理
        $post_data=[
            'media'=>$imgPath  //素材路径
        ];
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access&type=image";
        $data=curlPost($url,$post_data);
        $datainfo=json_decode($data,true);//转数组
        $img_media=$datainfo['media_id'];//media_id
        $created_at=$datainfo['created_at'];//时间
        if($img_media){
            $arr=[
                'name'=>$name,
                'img'=>$img_url,
                'status'=>1,
                'time'=>$created_at,
                'media_id'=>$img_media
            ];
            $res=KaoshiModel::insertGetId($arr);
            if($res){
                return redirect('/admin/kaoshi/kaolist');
            }
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
        $grid = new Grid(new KaoshiModel);

        $grid->id('编号');
        $grid->name('关键字');
        $grid->img('图片')->display(function($images){
            return "<img height='80' width='80'  src='/".$images." '>";
        });
        $grid->status('状态');
        $grid->time('添加时间');

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
        $show = new Show(KaoshiModel::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->img('Img');
        $show->status('Status');
        $show->time('Time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new KaoshiModel);

        $form->text('name', 'Name');
        $form->image('img', 'Img');
        $form->number('status', 'Status');
        $form->number('time', 'Time');

        return $form;
    }
}
