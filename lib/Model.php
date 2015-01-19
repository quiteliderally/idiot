<?php

namespace Idiot;

class Model{
  protected static $table = '';
  protected $record = null;
  protected $getters = [];
  protected $setters = [];

  public static function query(){
    $proxy = new PatchedProxy(
      \ORM::for_table(static::$table)
    );

    $proxy->_patchMethod('find_one', function($orm){
      $record = $orm->find_one();
      if($record){
        return new static($record);
      }
    });

    $proxy->_patchMethod('find_many', function($orm){
      $ret = [];
      $records = $orm->find_many();
      foreach($records as $r){
        $ret[] = new static($r);
      }
      return $ret;
    });

    return $proxy;
  }

  public static function all(){
    $ret = [];
    $records = \ORM::for_table(static::$table)
      ->find_many();
    foreach($records as $r){
      $ret[] = new static($r);
    }
    return $ret;
  }

  public static function findByID($id){
    return static::query()
      ->where('id', $id)
      ->find_one();

  }

  public static function create(){
    $record = \ORM::for_table(static::$table)->create(); 
    return new static($record);
  }

  public function update($properties=[]){
    if(isset($properties['id'])){
      unset($properties['id']);
    }
    foreach($properties as $k=>$v){
      $this->set($k, $v);
    }
  }

  public function save(){
    return $this->record->save();
  }

  public function delete(){
    return $this->record->delete();
  }

  public function handleSet($field, $cb){
    $this->setters[$field] = $cb;
  }

  public function handleGet($field, $cb){
    $this->getters[$field] = $cb;
  }

  public function set($field, $value){
    if(isset($this->setters[$field])){
      return call_user_func($this->setters[$field], $this, $field, $value);
    }
    else{
      return $this->record->{$field} = $value;
    }
  }

  public function get($field){
    if(isset($this->getters[$field])){
      return call_user_func($this->getters[$field], $this, $field);
    }
    else{
      return $this->record->{$field};
    }
  }

  public function __construct($record){
    $this->record = $record;
  }

  public function __get($property){
    return $this->get($property);
  }

  public function __set($property, $value){
    return $this->set($property, $value);
  }

  public function __isset($property){
    return isset($this->record->$property);
  }
}
