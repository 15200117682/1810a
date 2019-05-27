<?php

namespace App\Admin\Controllers;

use App\Model\SanModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class SanController extends Controller
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

    public function sanadd(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(view("san.sanadd"));
    }

    //执行添加题目
    public function sanjia(Request $request){
        $data=$request->input();
        $wx_name=$data['wx_name'];
        $wx_a=$data['wx_a'];
        $wx_b=$data['wx_b'];
        $wx_cor=$data['wx_cor'];
        $datainfo=[
            'wx_name'   =>  $wx_name,
            'wx_a'      =>  $wx_a,
            'wx_b'      =>  $wx_b,
            'wx_cor'    =>  $wx_cor,
            'wx_time'   =>  time()
        ];
        $res=SanModel::insertGetId($datainfo);
        if($res){
            return redirect('/admin/san/sanlist');
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
        $grid = new Grid(new SanModel);

        $grid->id('编号');
        $grid->wx_name('题目');
        $grid->wx_a('答案A');
        $grid->wx_b('答案B');
        $grid->wx_cor('正确答案');
        $grid->wx_time('存入时间')->display(function(){
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
        $show = new Show(SanModel::findOrFail($id));

        $show->id('Id');
        $show->wx_name('Wx name');
        $show->wx_a('Wx a');
        $show->wx_b('Wx b');
        $show->wx_cor('Wx cor');
        $show->wx_time('Wx time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SanModel);

        $form->text('wx_name', 'Wx name');
        $form->text('wx_a', 'Wx a');
        $form->text('wx_b', 'Wx b');
        $form->text('wx_cor', 'Wx cor');
        $form->number('wx_time', 'Wx time');

        return $form;
    }
}
