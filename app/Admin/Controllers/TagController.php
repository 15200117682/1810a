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

        $grid->id('Id');
        $grid->tag_name('Tag name');
        $grid->tag_wx_id('Tag wx id');

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

        $show->id('Id');
        $show->tag_name('Tag name');
        $show->tag_wx_id('Tag wx id');

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

        $form->text('tag_name', 'Tag name');
        $form->number('tag_wx_id', 'Tag wx id');

        return $form;
    }

    public function destroy($id){
        $access=getAccessToken();
        $data=TagModel::where(['id'=>$id])->first()->toArray();//查去数据库数据
        $tag_wx_id=$data['tag_wx_id'];
        $url2="https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=$access";//调接口
        $in=[];//空数组
        $in['tag']['id']=$tag_wx_id;//post数据
        $in=json_encode($in,true);//json数据转换
        $json=curlPost($url2,$in);//执行接口
        $res=json_decode($json,true);//转数组
        if($res){
            $res2=TagModel::where(['tag_wx_id'=>$tag_wx_id])->delete();
            if($res2){
                return redirect('/admin/taglist');
            }else{
                return "本地数据删除失败";
            }
        }else{
            return "wechat服务器数据删除失败";
        }

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

    public function tag_del(){
        die;
        $access=getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$access";
        $data=file_get_contents($url);
        $data=json_decode($data,true);
        foreach($data as $key=>$value){
            foreach($value as $k=>$v){
                if($v['id']>100){
                    $url2="https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=$access";
                    $in=[];
                    $in['tag']['id']=$v['id'];
                    $in=json_encode($in,true);
                    $json=curlPost($url2,$in);
                }
            }
        }
        var_dump($json);

    }
}
