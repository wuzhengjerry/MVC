<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 17:14
 */

namespace app\controllers;

use fastphp\base\Controller;
use app\models\ItemModel;

class ItemController extends Controller
{
    public function index()
    {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        if ($keyword) {
            $items = (new ItemModel())->search($keyword);
        } else {
            $items = (new ItemModel())->where()->order(['id DESC'])->fetchAll();
        }

        $this->assign('title', '全部条目');
        $this->assign('keyword', $keyword);
        $this->assign('items', $items);
        $this->render();
    }

    public function uploadfile()
    {
        $file = $_FILES;
        print_r($file);exit;
    }
}