<?php

class PHUMIN_STUDIO_Cache{
    
    public function get($params){
      global $engine;
      $r = [];

      foreach($params as $request){
            $controller = explode(":",$request['name']);

            $r[] = [
                  'name' => $request['name'],
                  'data' => [],
            ];
      }

      return $r;
    }
    
}