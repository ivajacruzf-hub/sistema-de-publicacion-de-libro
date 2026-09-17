<?php
require_once "../src/Models/lectura_libro.php";
class lectura_librocontroller{
     public static function getAll()
    {
        $lectura_libro=lectura_libro::all();
        echo json_encode($lectura_libro);
         
    }
}