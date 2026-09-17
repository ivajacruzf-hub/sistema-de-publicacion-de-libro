<?php
include_once __DIR__ . "/../config/conexionDB.php";
class Productos
{   
    //mostrar producto
    public static function all()
    {
        $sql = "SELECT * FROM producto";
        return ConexionPDO::query($sql); //self::$users;
    }
    //actualizar producto
    public static function update($id,$data)
    {
        if(isset($data['id'])) {
            unset($data['id']);
        }
        $campos=[];
        $valores=[];
        //construir datos
        foreach($data as $columna=>$valor) {
                $campos[]="$columna=:$columna";
                $valores[":$columna"]=$valor;
            }
        $stringCampos=implode(",",$campos);
        //preparamos la consulta
        $sql="UPDATE producto SET $stringCampos WHERE id=:id";
        $valores[':id']=$id;
        return ConexionPDO::execute($sql, $valores,false);
    }
    //adiccionar producto
    public static function add($data)
    {
        $campos=[];
        $parametros=[];
        $valores=[];
        //construir datos
        foreach($data as $columna=>$valor) {
                $campos[]=$columna;
                $parametros[]=":$columna";
                $valores[":$columna"]=$valor;
            }

        $stringCampos=implode(",",$campos);
        $stringParametros=implode(",",$parametros);
        //preparamos la consulta
        $sql="INSERT INTO producto ($stringCampos) VALUES ($stringParametros)";
        $result=ConexionPDO::execute($sql, $valores,true);
        return $result;
    }
    //eliminar producto
    public static function delete($id)
    {
        $sql="DELETE FROM producto WHERE id=:id";
        $valores=[
            ":id"=>$id
        ];
        return ConexionPDO::execute($sql, $valores,false);
    }
    
}
