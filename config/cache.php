<?php
return ['default'=>env('CACHE_STORE','database'),'stores'=>[
 'database'=>['driver'=>'database','table'=>'cache','connection'=>null,'lock_connection'=>null,'lock_table'=>'cache_locks'],
 'array'=>['driver'=>'array','serialize'=>false],
 ],'prefix'=>'ibn_cache_'];
