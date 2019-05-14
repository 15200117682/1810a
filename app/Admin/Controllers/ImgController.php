<?php

namespace App\Admin\Controllers;

use App\Model\ImgModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ImgController extends Controller
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


    public function materadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->matadd());
    }

    public function matadd(){
        return view('img.materadd');
    }

    /**
     * 素材添加
     * @param Request $request
     */
    public function matermedo(Request $request){
        $name=$request->input('img_name');//获取名称
        //$img_url = $request->img_url->storeAs('images');//获取图片信息
        /**********图片名字*********/
        if(!$request->hasFile('file') || !$request->file('file')->isValid()){
            return "文件不合法";
        }
        $etc=$request->file;

        $ext=$etc->getClientOriginalExtension();
        $filename=md5(rand(1000,9999).time()).".".$ext;
        $img_url=$request->file->storeAs('images/'.date("Y-m-d"),$filename);

        /**********图片名字*********/

        $img_type=$request->input('img_type');//获取类型
        $img_newold=$request->input('img_newold');//永久临时
        if($img_newold==1){/*临时素材*/
            if($img_type==1){//存入图片
                $this->img($img_url,$name,$img_type,$img_newold);
            }elseif($img_type==2){//存入语音
                $this->img($img_url,$name,$img_type,$img_newold);
            }elseif($img_type==3){//存入视频
                $this->img($img_url,$name,$img_type,$img_newold);
            }
        }elseif($img_newold==2){/*永久素材*/
            if($img_type==1){//存入图片
                $this->img($img_url,$name,$img_type,$img_newold);
            }elseif($img_type==2){//存入语音
                $this->img($img_url,$name,$img_type,$img_newold);
            }elseif($img_type==3){//存入视频
                $this->img($img_url,$name,$img_type,$img_newold);
            }
        }


    }

    //素材上传的方法
    public function img($img_url,$name,$img_type,$img_newold){
        $media_path=public_path()."/".$img_url;//获取绝对路径并存入laravel
        $imgPath = new \CURLFile($media_path);//通过CURLFile处理
        $post_data = [
            'media'=>$imgPath  //素材路径
        ];
        if($img_type==1){//图片
            $in="image";
        }elseif($img_type==2){//语音
            $in="voice";
        }elseif($img_type==3){//视频
            $in="video";
        }
        $access=getAccessToken();//获取access
        if($img_newold==1){
            $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access&type=$in";
        }elseif($img_newold==2){
            $url="https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=$access&type=$in";
        }
        $data=curlPost($url,$post_data);
        $datainfo=json_decode($data,true);//转数组
        $img_media=$datainfo['media_id'];//media_id
        $created_at=$datainfo['created_at'];//时间
        if($img_media){
            //加入数据库
            $arr=[
                'img_name'=>$name,
                'img_url'=>$img_url,
                'img_media'=>$img_media,
                'img_time'=>time(),
                'img_type'=>$img_type,
                'img_newold'=>$img_newold
            ];
            $res=ImgModel::insertGetId($arr);
            if($res){
                return redirect('/admin/materlist');
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
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $grid = new Grid(new ImgModel);

        $grid->id('编号');
        $grid->img_name('图片名称');
        $grid->img_url('图片')->image("$url",100,100);
        $grid->img_media('media');
        $grid->img_time('存入时间')->display(function(){
            return date("Y-m-d H:i:s");
        });

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
        $show = new Show(ImgModel::findOrFail($id));

        $show->id('Img id');
        $show->img_name('Img name');
        $show->img_url('Img url');
        $show->img_media('Img media');
        $show->img_time('Img time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ImgModel);

        $form->number('img_id', 'Img id');
        $form->text('img_name', 'Img name');
        $form->text('img_url', 'Img url');
        $form->text('img_media', 'Img media');
        $form->number('img_time', 'Img time');

        return $form;
    }
}
