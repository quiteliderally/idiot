<?php

namespace Idiot;

class PatchedProxy{
  protected $proxied = null;
  protected $handlers = [];

  public function __construct($proxied){
    $this->proxied = $proxied;
  }

  public function _patchMethod($method, $cb){
    $this->handlers[$method] = $cb;
  }

  public function __call($method, $args){
    if(isset($this->handlers[$method])){
      array_unshift($args, $this->proxied);
      return call_user_func_array(
        $this->handlers[$method],
        $args
      );
    }
    else{
      $resp =  call_user_func_array(
        [$this->proxied, $method],
        $args
      ); 
      if($resp === $this->proxied){
        return $this;
      }
      else{
        return $resp;
      }
    }
  }
}
