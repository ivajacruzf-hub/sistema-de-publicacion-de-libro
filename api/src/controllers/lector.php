<?php
require_once "../src/Models/lector.php";
class lectorController{
     public static function getAll()
    {
        $producto=lector::all();
        echo json_encode($lector);
         
    }
}