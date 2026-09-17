<?php
require_once "../src/Models/lectura_libro.php";
class autor_librocontroller{
     public static function getAll()
    {
        $autor_libro=autor_libro::all();
        echo json_encode($autor_libro);
         
    }
}