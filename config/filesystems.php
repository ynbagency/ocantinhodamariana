<?php
$defaults=require base_path('vendor/laravel/framework/config/filesystems.php');
$defaults['disks']['public']['root']=env('PUBLIC_MEDIA_ROOT',storage_path('app/public'));
return $defaults;
