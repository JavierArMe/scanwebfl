<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET,POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Middleware\OutputBufferingMiddleware;
$json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
 
  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

// Conecta a la base de datos  con usuario, contraseña y nombre de la BD
//require("conexion.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB
//$conexion = conexion();
require __DIR__ . '/../conexion/conexion.php';

// Consulta datos y recepciona una clave para consultar dichos datos con dicha clave a usuarios
 if (isset($_GET["consultaruser"])){

    $sqlUser = mysqli_query($conexion,"INSERT INTO addplatillos(idUsuario,nombre, precio)
SELECT idproducto, descripcion, precio FROM productos WHERE productos.idproducto = ".$_GET["consultaruser"]);
    if($sqlUser){
        echo json_encode(["success"=>1]);
        exit();
    }
    else{  echo json_encode(["success"=>0]); }

}

if(isset($_GET["actualizar"])){
    
    $data = json_decode(file_get_contents("php://input"));

    $id=(isset($data->idproducto))?$data->idproducto:$_GET["actualizar"];
    $mesa = $data->mesa;
    $comensal = $data->comensal;
    $datatime = date("Y-m-d h:i:sa");
    $estatus = 'POR ORDENAR';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion,"INSERT INTO addplatillos SET 
  idUsuario  = (SELECT idproducto FROM productos WHERE productos.idproducto = '$id'),
  nombre  = (SELECT descripcion FROM productos WHERE productos.idproducto = '$id'),
  precio  = (SELECT precio FROM productos WHERE productos.idproducto = '$id'),
  cantidad = '1',
  estatus = '$estatus',
  Mesa='$mesa',
  Fecha= '$DateAndTime',
  comensal= '$comensal' ");
    echo json_encode(["success"=>1]);
    exit();
}

if(isset($_GET["restar"])){
    
    $data = json_decode(file_get_contents("php://input"));

    $id=(isset($data->idproducto))?$data->idproducto:$_GET["restar"];
    $mesa = $data->mesa;
    $comensal = $data->comensal;
    $datatime = date("Y-m-d h:i:sa");
    $estatus = 'POR ORDENAR';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion,"INSERT INTO addplatillos SET 
  idUsuario  = (SELECT idproducto FROM productos WHERE productos.idproducto = '$id'),
  nombre  = (SELECT descripcion FROM productos WHERE productos.idproducto = '$id'),
  precio  = (SELECT precio FROM productos WHERE productos.idproducto = '$id'),
  cantidad = '-1',
  estatus = '$estatus',
  Mesa='$mesa',
  Fecha= '$DateAndTime',
  comensal= '$comensal' ");
    echo json_encode(["success"=>1]);
    exit();
}


if(isset($_GET["baja"])){
    
    $data = json_decode(file_get_contents("php://input"));

    $id=(isset($data->idproducto))?$data->idproducto:$_GET["baja"];
    //$mesa = $data->mesa;
    //$comensal = $data->comensal;
    //$datatime = date("Y-m-d h:i:sa");
    //$estatus = 'POR ORDENAR';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion," UPDATE `productos` SET `estatus`='BAJA' WHERE idproducto = '$id' ");
    echo json_encode(["success"=>1]);
    exit();
}

if(isset($_GET["alta"])){
    
    $data = json_decode(file_get_contents("php://input"));

    $id=(isset($data->idproducto))?$data->idproducto:$_GET["alta"];
    //$mesa = $data->mesa;
    //$comensal = $data->comensal;
    //$datatime = date("Y-m-d h:i:sa");
    //$estatus = 'POR ORDENAR';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion," UPDATE `productos` SET `estatus`='ALTA' WHERE idproducto = '$id' ");
    echo json_encode(["success"=>1]);
    exit();
}

if (isset($_GET["consultar"])){
    $sqlEvent = mysqli_query($conexion,"SELECT `idproducto`, `descripcion`, `precio`, `imagen` FROM `productos` WHERE idproducto=".$_GET["consultar"]);
    if(mysqli_num_rows($sqlEvent) > 0){
        $eventos = mysqli_fetch_all($sqlEvent,MYSQLI_ASSOC);
        echo json_encode($eventos);
        exit();
    }
    else{  echo json_encode(["success"=>0]); }
}


if(isset($_GET["modificarplatos"])){
    
    $data = json_decode(file_get_contents("php://input"));

    $id=(isset($data->idproducto))?$data->idproducto:$_GET["modificarplatos"];
    $descripcion = $data->descripcion;
    $precio = $data->precio;
    //$precio = $data->precio;
    //$mesa = $data->mesa;
    //$comensal = $data->comensal;
    //$datatime = date("Y-m-d h:i:sa");
    //$estatus = 'POR ORDENAR';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion,"UPDATE `productos` SET `descripcion`='$descripcion',`precio`='$precio' WHERE idproducto = '$id' ");
    echo json_encode(["success"=>1]);
    exit();
}


if (isset($_GET["consultarmesas"])){
    $sqlEvent = mysqli_query($conexion,"SELECT `idmesa` FROM `addmesas` WHERE idmesa=".$_GET["consultarmesas"]);
    if(mysqli_num_rows($sqlEvent) > 0){
        $eventos = mysqli_fetch_all($sqlEvent,MYSQLI_ASSOC);
        echo json_encode($eventos);
        exit();
    }
    else{  echo json_encode(["success"=>0]); }
}

if(isset($_GET["resmes"])){
    
    $data = json_decode(file_get_contents("php://input"));

    //$id=(isset($data->idproducto))?$data->idproducto:$_GET["resmes"];
    $idmesa = $data->idmesa;
    $nombre = $data->nombre;
$telefono = $data->telefono;
$correo = $data->correo;
$fecha = $data->fecha;
$fechafinal = $data->fechafinal;

    //$mesa = $data->mesa;
    //$comensal = $data->comensal;
    $datatime = date("Y-m-d h:i:sa");
    $estatus = 'RESERVADA';
    //$fecha = $data->descripcion;
    //$DateTime = $data->datetime;

	$Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  



    $sqlEvent = mysqli_query($conexion,"INSERT INTO mesas SET 
  idmesa  = (SELECT idmesa FROM addmesas WHERE addmesas.idmesa = '$idmesa'),
  precio  = (SELECT costo FROM addmesas WHERE addmesas.idmesa = '$idmesa'),
  nombre = '$nombre',
  telefono = '$telefono',
  fecha = '$fecha',
  fechafinal = '$fechafinal',
  estatus = '$estatus'
   ");
    echo json_encode(["success"=>1]);
    exit();
}

if (isset($_GET["consultarmesasdat"])){
    $sqlEvent = mysqli_query($conexion,"SELECT `idmesa`, `costo` FROM `addmesas` WHERE idmesa=".$_GET["consultarmesasdat"]);
    if(mysqli_num_rows($sqlEvent) > 0){
        $eventos = mysqli_fetch_all($sqlEvent,MYSQLI_ASSOC);
        echo json_encode($eventos);
        exit();
    }
    else{  echo json_encode(["success"=>0]); }
}



?>