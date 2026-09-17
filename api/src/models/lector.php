<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class lector
{
    public static function all()
    {
        $sql = "SELECT * FROM lector";
        return ConexionPDO::query($sql); //self::$users;
    }
} 
