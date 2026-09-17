<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class autor
{
    public static function all()
    {
        $sql = "SELECT * FROM autor";
        return ConexionPDO::query($sql); //self::$users;
    }
}