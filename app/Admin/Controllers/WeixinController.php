<?php

namespace App\Admin\Controllers;

use App\Model\WxModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
class WeixinController extends Controller
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
//        return $content
//            ->header('Create')
//            ->description('description')
//            ->body($this->form());
        return $content
            ->header('欢迎来到绯月的群发室')
            ->description('在这里您可以和自己的好友之间进行群发')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WxModel);

        $grid->id('Id');
        $grid->uid('Uid');
        $grid->openid('Openid');
        $grid->add_time('Add time')->display(function ($time) {
            return date('Y-m-d H:i:s', $time);
        });

        $grid->nickname('Nickname');
        $grid->sex('Sex')->display(function ($sex) {
            if ($sex == 0) {
                $sexs = '未知';
            } elseif ($sex == 1) {
                $sexs = '男';
            } elseif ($sex == 2) {
                $sexs = '女';
            }
            return $sexs;
        });
        $grid->headimgurl('Headimgurl')->display(function ($img) {
            return "<img src='$img'>";
        });

        $grid->subscribe_time('Subscribe time')->display(function ($time) {
            return date('Y-m-d H:i:s', $time);
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
        $show = new Show(WxModel::findOrFail($id));

        $show->id('Id');
        $show->uid('Uid');
        $show->openid('Openid');
        $show->add_time('Add time');
        $show->nickname('Nickname');
        $show->sex('Sex');
        $show->headimgurl('Headimgurl');
        $show->subscribe_time('Subscribe time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WxModel);

//        $form->text('openid', 'Openid');
//        $form->number('add_time', 'Add time');
//        $form->text('msg_type', 'Msg type');
//        $form->text('media_id', 'Media id');
//        $form->text('format', 'Format');
//        $form->text('msg_id', 'Msg id');
//        $form->text('local_file_name', 'Local file name');
//        $form->text('local_file_path', 'Local file path');
        $form->textarea('content', 'TEXT(信息不能重复输入)');
        return $form;
    }

    public function type(Request $request)
    {

        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=' .WxModel:: getWXAccessToken();
        //echo $url;echo '</br>';
        //2 请求微信接口
        $content = $request->input('content');
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data = [
            "filter" => [
                "is_to_all" => true,

            ],
            "text" => [
                "content" => $content
            ],
            "msgtype" => "text"
        ];
        //var_dump($data);
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);      //处理中文编码
        $r = $client->request('POST', $url, [
            'body' => $body
        ]);

        // 3 解析微信接口返回信息

        $response_arr = json_decode($r->getBody(), true);
        echo '<pre>';
        print_r($response_arr);
        echo '</pre>';

        if ($response_arr['errcode'] == 0) {
            echo "群发成功";
        } else {
            echo "群发失败，请重试";
            echo '</br>';


        }
    }
}
