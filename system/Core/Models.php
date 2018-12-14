<?php

namespace System\Core;
use System\DB\Mysql;
use System\Exceptions\QueryBuildModeException;

abstract class Models extends Mysql
{
    protected $table = '';
    protected $pk = 'id';  //pk:primary key
    protected $sql = '';
    protected $select = '*';
    protected $conditions = null;
    protected $order = null;
    protected $offset = null;
    protected $limit = null;
    protected $related = null;

    public function select($columns='*'){
        if(is_array($columns)){
            $this->select = implode(', ',$columns);
        }else{
            $this->select = $columns;
        }
        return $this;
    }

    public function where($column, $value, $operator = '=')
    {
        if (is_null($this->conditions)){
            $this->conditions = "{$column} {$operator} '{$value}'";
        }else{
            $this->conditions.= "AND {$column} {$operator} '{$value}'";
        }
        return $this;
    }

    public function orwhere($column, $value, $operator = '=')
    {
        $this->conditions.= "OR {$column} {$operator} '{$value}'";
        return $this;
    }

    public function order($column,$direction='ASC'){
        if (is_null($this->order)){
            $this->order ="{$column} {$direction}";
        }else{
            $this->order.= ", {$column} {$direction}";
        }
        return $this;
    }

    public function offset($offset){
        $this->offset = $offset;
        return $this;
    }

    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }

    public function get(){
        $this->buildQuery('select');
        $this->query($this->sql);
        if ($this->num_rows()==1){
            if(is_null($this->related)) {
                $data = $this->fetch_assoc()[0];
                foreach ($data as $k => $v) {
                    $this->{$k} = $v;
                }
                     $this->reset();
                return true;
            }else{
                $class = new $this->related['class_name'];
                $data = $this->fetch_assoc()[0];
                foreach ($data as $k => $v){
                    $class->{$k} = $v;
                }
                $this->reset();
                if($this->related['relation']=='parent') {
                    $this->reset();
                    return $class;
                }else{
                    $ret[]= $class;
                    $this->reset();
                    return $ret;
                }
            }
            return true;
        }elseif($this->num_rows()>1){
            $data = $this->fetch_assoc();
            $ret = [];
            if(is_null($this->related)) {
                $class_name = get_class($this);
            }else{
                $class_name = $this->related['class_name'];
            }
            foreach($data as $item){
                $obj = new $class_name;
                foreach ($item as $k => $v){
                    $obj->{$k}= $v;
                }
                $ret[]=$obj;
            }
            $this->reset();
            return $ret;
        }else{
            $this->reset();
            return null;
        }
    }

    public function load($id){
        $this->where($this->pk,$id)->get();
    }

    public function save(){
        if(isset($this->{$this->pk}) && !empty($this->{$this->pk})){
            $this->buildQuery('update');
            $flg = 0;
        }else{
            $this->buildQuery('insert');
            $flg=1;
        }

        $this->query($this->sql);
        if($flg == 1) {
            $this->{$this->pk} = $this->last_id();
        }
        return true;
    }

    public function delete(){
        $this->buildQuery('delete');
        $this->query($this->sql);
        $this->reset();
        $keys = $this->getDataColumns();
        foreach($keys as $key){
          unset($this->{$key});
        }
    return true;
    }

    protected function reset(){
        $this->sql = '';
        $this->select = '*';
        $this->conditions = null;
        $this->order = null;
        $this->offset = null;
        $this->limit = null;
    }

    protected function buildSelectQuery(){
        if(is_null($this->realted)) {

            $this->sql = "SELECT {$this->select} FROM {$this->table}";
        }else{
            $class = new $this->related['class_name'];
            $this->sql = "SELECT {$this->select} FROM {$class->table}";
            if($this->realted ['realtion']=='child'){
                $this->where($this->realted['fk'], $this->{$this->pk});
            }else{
                $this->where($class->pk,$this->{$this->related['fk']});
            }
        }
        if (!is_null($this->conditions)){
            $this->sql.=" WHERE {$this->conditions}";
        }
        if (!is_null($this->order)){
            $this->sql.=" ORDER BY {$this->order}";
        }

        if (!is_null($this->limit)){
            if (is_null($this->offset)){
                $this->sql.=" LIMIT {$this->limit}";
            }else{
                $this->sql.=" LIMIT {$this->offset},{$this->limit}";
            }
        }
    }


    protected function buildInsertQuery(){
        $columns = $this->getDataColumns('data');
        $this->sql = "INSERT INTO {$this->table} SET ";
        $cond=[];
        foreach($columns as $k=>$v){
            $cond[]="{$k} = '{$v}'";
        }

        $this->sql .=implode(', ', $cond);
    }

    protected function buildUpdateQuery(){
        $columns = $this->getDataColumns('data');
        $this->sql = "UPDATE INTO {$this->table} SET ";
        $cond=[];
        foreach($columns as $k=>$v){
            $cond[]="{$k} = '{$v}'";
        }

        $this->sql .= implode(', ', $cond);
        $this->sql .=" WHERE{$this->pk} = '{$this->{$this->pk}}' ";
    }

    protected function buildDeleteQuery()
    {
        $this->sql = "DELETE FROM {$this->table} WHERE {$this->pk}= {$this->{$this->pk}}";

    }
        protected
        function getDataColumns($type = 'keys')
        {
            $all = get_class_vars(get_class($this));
            $vars = get_object_vars($this);

            $diff = array_diff($vars, $all);
            if ($type == 'data') {
                return $diff;
            } else {
                return array_keys($diff);
            }
        }



    public  function related($class_name, $fk, $realtion){
        $this->related = compact('class_name', 'fk','relation');
        return $this;
    }

    protected function buildQuery($mode){
        switch ($mode){
            case 'select' :
                $this->buildSelectQuery();
                break;
            case 'insert':
                $this->buildInsertQuery();
                break;

            case 'update':
                $this->buildUpdateQuery();
                break;

            case 'delete':
                $this->buildDeleteQuery();
                break;

            default:
                throw new QueryBuildModeException("Query build mode '{$mode}' does not exist.");

        }
    }
}