<?php
return ['driver'=>env('SESSION_DRIVER','database'),'lifetime'=>(int)env('SESSION_LIFETIME',30),
 'expire_on_close'=>true,'encrypt'=>true,'files'=>storage_path('framework/sessions'),
 'connection'=>env('SESSION_CONNECTION'),'table'=>'sessions','store'=>null,'lottery'=>[2,100],
 'cookie'=>'ibn_session','path'=>'/','domain'=>null,'secure'=>(bool)env('SESSION_SECURE_COOKIE',true),
 'http_only'=>true,'same_site'=>env('SESSION_SAME_SITE','strict'),'partitioned'=>false];
