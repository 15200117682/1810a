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
        return view("menu.menuadd");
    }

    public function menumedo(Request $request){
        $data=$request->input();
        $arr=[
            'menu_name'=>$data['menu_name'],
            'menu_type'=>$data['menu_type'],
            'menu_key'=>$data['menu_key'],
        ];
        $res=MenuModel::insertGetId($arr);
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

        $grid->menu_id('菜单id');
        $grid->menu_name('菜单名称');
        $grid->menu_type('菜单类型');
        $grid->menu_key('菜单key');

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

        $show->menu_id('Menu id');
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
