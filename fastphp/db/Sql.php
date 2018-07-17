<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 11:46
 */

namespace fastphp\db;

use \PDOStatement;

class Sql
{
    //数据库表名
    protected $table;

    //数据库主键
    protected $primary = 'id';

    //where 和 order 拼装后的条件
    private $filter='';

    //PDO bindParam() 绑定的参数集合
    private $param = array();

    /*
     *查询条件拼接使用法
     * $this->where(['id=1', 'and title="web"',...])->fetch();
     * 为了防止注入，建议通过$param方式传入参数；
     * $this->>where(['id=:id'],[':id=>$id'])->fetch();
     *
     * @param array $where条件
     * @return $this 当前对象
     */
    public function where($where = array(), $param= array())
    {
        if ($where) {
            $this->filter .= ' where';
            $this->filter .= implode(' ', $where);
            $this->param = $param;
        }
        return $this;
    }

    /*
     * 拼装排序条件，使用方式
     * $this->order(['id desc', 'title asc', ...])->fetch();
     *
     * @param array $order 排序条件
     * @return @this
     */
    public function order($order = array())
    {
        if ($order) {
            $this->filter .= ' order by';
            $this->filter .= implode(',', $order);
        }
        return $this;
    }

    //查询所有
    public function fetchAll()
    {
        $sql = sprintf("select * from %s %s", $this->table, $this->filter);
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->excute();

        return $sth->fetchAll();
    }

    //查询一条
    public function fetch()
    {
        $sql = sprintf("select * from %s %s", $this->table, $this->filter);
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->fetch();
    }

    //根据条件(id)删除数据
    public function delete($id)
    {
        $sql = sprintf("delete from %s where %s = %s", $this->table, $this->primary, $this->primary);
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [$this->param => $id]);
        $sth->execute();

        return $sth->rouCount();
    }

    //新增数据
    public function add($data)
    {
        $sql = sprintf("insert into %s %s", $this->table, $this->formatInsert($data));
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();

        return $sth->rowCount();
    }

    //修改数据
    public function update($data)
    {
        $sql = sprintf("update %s set %s %s", $this->table, $this->formatUpdate($data), $this->filter);
        $sth = Db::pdo()->prepare($sql);
        $sth = $this->formateParam($sth, $data);
        $sth = $this->formateParam($sth, $this->param);
        $sth->execute();

        return $sth->rowCount();
    }

    /*
     *占位符绑定具体的变量值
     * @param PDOStatement $sth 要绑定的PDOStatement对象
     * @param array $param参数，有三种类型
     * 1 如果SQL语句用问号(?)占位符，那么$param应该为
     * [$a, $b, $c]
     * 2 如果SQL用冒号(:)占位符，
     * ['a' => $a, 'b' => $b, 'c' => $c]
     * 或者
     * [':a' => $a, ':b' => $b, ':c' => $c]
     *
     * @return PDOStatement
     */
    public function formatParam(PDOStatement $sth, $params = array())
    {
        foreach ($params as $param => &$value) {
            $param = is_int($param) ? $param + 1 :':' . ltrim($param, ':');
            $sth->bindParam($param, $value);
        }

        return $sth;
    }

    //将数组转换成插入格式的sql语句
    private function formatInsert($data)
    {
        $fields = array();
        $names = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("%s", $key);
            $names[] = sprintf(":%s", $key);
        }

        $field = implode(',', $fields);
        $name = implode(',', $names);

        return sprintf("(%s) values (%s)", $field, $name);
    }

    //将数组转换成更新格式的sql语句
    private function formatUpdate($data)
    {
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("%s = :%s", $key, $key);
        }

        return implode(',', $fields);
    }
}