<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class libro
{
    public static function all()
    {
        $sql = "SELECT * FROM libro";
        return ConexionPDO::query($sql); //self::$users;
    }
}