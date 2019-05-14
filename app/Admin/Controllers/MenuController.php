<?php

namespace App\Admin\Controllers;

use App\Model\MenuModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class MenuController extends Controller
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
            ->body($this->grid())
            ->row($this->sub());
    }

    public function sub(){
        return view('menu.sub');
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

    //菜单添加
    public function menuadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->meadd());
    }

    public function meadd(){
        $data=MenuModel::where(['p_id'=>0])->get()->toArray();
        return view("menu.menuadd",['data'=>$data]);
    }

    public function menumedo(Request $request){
        $data=$request->input();//接受页面所有信息
        if($data['menu_name']==""){//判断类别
            return "名字不能为空";
        }elseif($data['menu_type']==""){//判断key
            return "类型不能为空";
        }elseif($data['menu_key']==""){//判断名字
            return "key不能为空";
        }
        $info=MenuModel::where(['menu_name'=>$data['menu_name']])->get()->toArray();//查取名字是否重复
        if($info){
            return "菜单已存在，无法重复添加";
        }//如果菜单名称数据库已存在，禁止添加
        if($data['p_id']==0){//如果是顶级菜单，添加所有信息
            $count=MenuModel::where(['p_id'=>0])->get()->count();//查询菜单数量
            if($count>=3){
                return "顶级菜单太多，无法添加";
            }//如果一级菜单过多，无法添加
            $arr=[
                'menu_name'=>$data['menu_name'],
                'menu_type'=>$data['menu_type'],
                'menu_key'=>$data['menu_key'],
            ];//组合数据
            $res=MenuModel::insertGetId($arr);//入库
        }else{//如果不是顶级分类，执行添加
            $count=MenuModel::where(['p_id'=>$data['p_id']])->get()->count();//查询已有菜单的数量
            if($count>=5){
                return "菜单太多，无法添加";
            }//如果菜单过多，无法添加
            $arr=[
                'menu_name'=>$data['menu_name'],
                'menu_type'=>$data['menu_type'],
                'menu_key'=>$data['menu_key'],
                'p_id'=>$data['p_id']
            ];//拼装数据
            $res=MenuModel::insertGetId($arr);//执行添加的sql
        }
        if($res){
            return redirect('/admin/menulist');
        }

    }

    /**
     * Make a grid builder.
     *展示
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MenuModel);

        $grid->id('菜单id');
        $grid->menu_name('菜单名称');
        $grid->menu_type('菜单类型');
        $grid->menu_key('菜单key');
        $grid->p_id('所属父类');

        return $grid;
    }
    /*protected function grid(){
        $arr=MenuModel::all()->toArray();
        return view("menu.menulist",['data'=>$arr]);
    }*/

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MenuModel::findOrFail($id));

        $show->id('Menu id');
        $show->menu_name('Menu name');
        $show->menu_type('Menu type');
        $show->menu_key('Menu key');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MenuModel);

        $form->number('menu_id', 'Menu id');
        $form->text('menu_name', 'Menu name');
        $form->text('menu_type', 'Menu type');
        $form->text('menu_key', 'Menu key');

        return $form;
    }
}
