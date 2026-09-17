<?php
require_once "../src/Models/autor_libro.php";
class libroController{
     public static function getAll()
    {
        $libro=libro::all();
        echo json_encode($libro);
         
    }
}