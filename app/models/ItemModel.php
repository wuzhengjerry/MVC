<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 16:52
 */

namespace app\models;

use fastphp\base\Model;
use fastphp\db\Db;

class ItemModel extends Model
{
    protected $table = 'item';

    public function search($keyword)
    {
        $sql = "select * from $this->table where item_name like :keyword";
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [':keyword' => '%keyword%']);
        $sth->execute();

            return $sth->fetchAll();
    }
}