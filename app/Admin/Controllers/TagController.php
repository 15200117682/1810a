<?php

namespace App\Admin\Controllers;

use App\Model\TagModel;
use App\Http\Controllers\Controller;
use App\Model\TagOpenModel;
use App\Model\UsersModel;
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

        $grid->id('标签编号');
        $grid->tag_name('标签名字');
        $grid->tag_wx_id('在微信的id');

        return $grid;
    }

    /**
     * 给用户打标签的所有用户信息和试图
     *Make a show builder.
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $data = TagModel::where(['id' => $id])->first()->toArray();//查取标签
        $dataInfo = UsersModel::get()->toArray();
        return view("tag.taglist", ['data' => $data, 'datainfo' => $dataInfo]);
    }


    /**
     * 给用户打标签
     * @param Request $request
     * @return false|string
     */
    public function maketag(Request $request)
    {
        $data = $request->input();//所有数据
        $openid = $data['openid'];//要加入标签的用户openid
        $tag_wx_id = $data['tag_wx_id'];//加入那个标签
        $access = getAccessToken();//access_token
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=$access";//调接口
        $datainfo = [
            "openid_list" => [//粉丝列表
                    $openid
            ],
            "tagid" => $tag_wx_id
        ];//微信服务器的入标签数据
        $insertGetId=[];
        /**讲标签都存入数据库*****************/
        foreach($openid as $k=>$v){
            $v=trim($v,"\"");
            $insertGetId[]=[
                'tag_wx_id'=>$tag_wx_id,
                'openid'=>$v
            ];
        }
        TagOpenModel::insert($insertGetId);
        /**讲标签都存入数据库*****************/
        $datainfo = json_encode($datainfo, true);//转换json数据
        $json = curlPost($url, $datainfo);//curlpost使用接口
        $res = json_decode($json, true);//转换数组数据
        if ($res['errmsg'] == "ok") {
            return $res;
        }//成功返回数据
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

    /**
     * 删除数据库的标签和微信服务器的标签
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|string
     */
    public function destroy($id)
    {
        $access = getAccessToken();//access_token
        $data = TagModel::where(['id' => $id])->first()->toArray();//查去数据库数据
        $tag_wx_id = $data['tag_wx_id'];//获取微信id
        /**删除关联表中的数据**/
        TagOpenModel::where(['tag_wx_id'=>$tag_wx_id])->delete();
        $url2 = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=$access";//调接口
        $in = [];//空数组
        $in['tag']['id'] = $tag_wx_id;//post数据
        $in = json_encode($in, true);//json数据转换
        $json = curlPost($url2, $in);//执行接口 删除单个标签
        $res = json_decode($json, true);//转数组
        if ($res) {
            $res2 = TagModel::where(['tag_wx_id' => $tag_wx_id])->delete();//删除数据库
            if ($res2) {
                return redirect('/admin/taglist');
            } else {
                return "本地数据删除失败";
            }
        } else {
            return "wechat服务器数据删除失败";
        }

    }

    /**
     * 新增标签试图展示
     * @param Content $content
     * @return Content
     */
    public function tagadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("tag.tagadd"));
    }

    /**
     * 执行标签添加
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function tagmedo(Request $request)
    {
        $tag_name = $request->input();//接受数据
        $tag_name = $tag_name['tag_name'];//name名字
        $access = getAccessToken();//access_token
        $url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token=$access";//调接口
        $data = [];//空数组
        $data['tag']['name'] = $tag_name;//接口需要的数据
        $datainfo = json_encode($data, JSON_UNESCAPED_UNICODE);//转换数据类型json
        $json = curlPost($url, $datainfo);//curlpost使用接口
        $json = json_decode($json, true);//转换数组数据
        $id = $json['tag']['id'];//获取id
        $name = $json['tag']['name'];//获取名字
        $arr = [
            'tag_name' => $name,
            'tag_wx_id' => $id
        ];//组合成数组
        $res = TagModel::insertGetId($arr);//入库
        if ($res) {
            return redirect('/admin/taglist');
        }//跳转页面
    }

    /**
     * 删除所有标签
     */
    public function tag_del()
    {
        die;
        $access = getAccessToken();//access_token
        $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$access";//接口
        $data = file_get_contents($url);//使用接口
        $data = json_decode($data, true);//转换数据类型
        foreach ($data as $key => $value) {
            foreach ($value as $k => $v) {
                if ($v['id'] > 100) {
                    $url2 = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=$access";//接口
                    $in = [];//空数组
                    $in['tag']['id'] = $v['id'];//接口需要的数据
                    $in = json_encode($in, true);//转json数据
                    $json = curlPost($url2, $in);//curlpost使用接口
                }
            }
        }//循环删除所有新增的标签
        var_dump($json);

    }
}
