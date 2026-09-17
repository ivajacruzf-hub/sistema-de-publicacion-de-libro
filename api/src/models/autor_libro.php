<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class autor_libro
{
    public static function all()
    {
        $sql = "SELECT * FROM autor_libro";
        return ConexionPDO::query($sql); //self::$users;
    }
}