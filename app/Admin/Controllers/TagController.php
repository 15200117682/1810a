<?php

namespace App\Admin\Controllers;

use App\Model\TagModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class TagController extends Controller
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

    public function tagadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("tag.tagadd"));
    }

    //执行标签添加
    public function tagmedo(Request $request){
        $tag_name=$request->input();
        $tag_name=$tag_name['tag_name'];
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/tags/create?access_token=$access";
        $data=[];
        $data['tag']['name']=$tag_name;
        $datainfo=json_encode($data,JSON_UNESCAPED_UNICODE);
        $json=curlPost($url,$datainfo);
        $json=json_decode($json,true);
        $id=$json['tag']['id'];
        $name=$json['tag']['name'];
        $arr=[
            'tag_name'=>$name,
            'tag_wx_id'=>$id
        ];
        $res=TagModel::insertGetId($arr);
        if($res){
            return redirect('/admin/taglist');
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
        $grid = new Grid(new TagModel);
        $grid->id('标签id');
        $grid->tag_name('标签名称');
        $grid->tag_wx_id('标签在微信上的id');


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
        $show = new Show(TagModel::findOrFail($id));
        $show->id('id');
        $show->tag_name('tag name');
        $show->tag_wx_id('tag wx id');


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TagModel);
        $form->id('id');
        $form->tag_name('tag name');
        $form->tag_wx_id('tag wx id');


        return $form;
    }
}
