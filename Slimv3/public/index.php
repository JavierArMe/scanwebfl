<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET, POST, HEAD, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use App\Middleware\FlashMessageMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\TwigMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


use Slim\Http\UploadedFile;


require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../conexion/conexion.php';

require './PHPMailer-master/src/Exception.php';
    require './PHPMailer-master/src/PHPMailer.php';
    require './PHPMailer-master/src/SMTP.php';
//require __DIR__ . '/../public/mos.php';
$bd = new BD();
$bd = $bd->coneccionBD();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
   $response->getBody()->write('Hello World!');
   return $response;
});
 /////////////////////
 $app->POST('/addproducto', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $factor = ($data);


//print_r($factor);
$//imgname = str_replace( "\\", '/', ($factor['imagen']) );

//echo basename( $imgname ) . "<br>";
//print_r( basename($imgname));
   $id = 0;
  $codigobarras = ($factor['codigobarras']);
  $nombre = ($factor['nombre']);
  $costo = ($factor['costo']);
  $cantidad = ($factor['cantidad']);
  $descripcion = ($factor['descripcion']);
  $cantdispo = ($factor['cantdispo']);
  $ubicacion = ($factor['ubicacion']);
  $oferta = ($factor['oferta']);
  $imagen = basename($factor['imagen']);
  $estatus = "1"; 

 $sql = "INSERT INTO `productosalta`(barcode,nombre,costo,cantidadpz,descripcion,stock,ubicacion,oferta,imagen,estatus) VALUES (:barcode,:nombre,:costo,:cantidadpz,:descripcion,:stock,:ubicacion,:oferta,:imagen,:estatus)" ;        

  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':barcode', $codigobarras);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':costo', $costo);
    $stmt->bindParam(':cantidadpz', $cantidad);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':stock', $cantdispo);
    $stmt->bindParam(':ubicacion', $ubicacion);
    $stmt->bindParam(':oferta', $oferta);
    $stmt->bindParam(':imagen', $imagen);
    $stmt->bindParam(':estatus', $estatus);
    //$stmt->bindParam(':Fecha', $Fecha);
    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });



 //subir imagen 
 $app->POST('/subirimgproducto', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data['nombreArchivo']);
  $pdf = $data;
  //print_r($pdf);

$dir = "/Slimv3/public/img/";

    $nombreArchivo = $pdf['nombreArchivo'];
    $archivo = $pdf['base64textString'];

    /* list($type, $archivo) = explode(';', $archivo);
list(, $archivo)      = explode(',', $archivo); */
$archivo = base64_decode($archivo);


    $filePath = $_SERVER['DOCUMENT_ROOT']."{$dir}".$nombreArchivo;

    file_put_contents($filePath, $archivo);
   //ver como subir imagen 


  });

 //obtener factores
 $app->get('/obtproductos', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT * FROM `productosalta`  ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
 

 $app->put('/bajaproducto/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE productosalta SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


  $app->put('/editproductoscan/{id}',function (Request $request, Response $response, array $args) {
  
    //$json = file_get_contents('php://input');
    //$data = json_decode($json);
    $data = $request->getParsedBody();
  
    $id = $request->getAttribute('id');
    //$factor = ($data['factor']);

   /*  "Array
(
    [id] => 3
    [barcode] => 23423
    [nombre] => wdasdasd
    [costo] => 23
    [cantidadpz] => 123
    [descripcion] => asd
    [stock] => 23
    [ubicacion] => weads
    [oferta] => 213
    [imagen] => sdf
    [estatus] => 0
)
Update successful! true" */
    
    //print_r($data);
    $barcode = $data['barcode'];
    $nombre = $data['nombre'];
    $costo = $data['costo'];
    $cantidadpz = $data['cantidadpz'];
    $descripcion = $data['descripcion'];
    $stock = $data['stock'];
    $ubicacion = $data['ubicacion'];
    $oferta = $data['oferta'];
    $imagen = $data['imagen'];
    $estatus = $data['estatus'];

    
 
    $sql = "UPDATE productosalta SET barcode = :barcode, nombre = :nombre, costo = :costo, cantidadpz = :cantidadpz, descripcion = :descripcion, stock = :stock, ubicacion = :ubicacion, oferta = :oferta, imagen = :imagen WHERE id = $id ";
    
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
    
     $stmt = $conn->prepare($sql);
     $stmt->bindParam(':barcode', $barcode);
     $stmt->bindParam(':nombre', $nombre);
     $stmt->bindParam(':costo', $costo);
     $stmt->bindParam(':cantidadpz', $cantidadpz);
     $stmt->bindParam(':descripcion', $descripcion);
     $stmt->bindParam(':stock', $stock);
     $stmt->bindParam(':ubicacion', $ubicacion);
     $stmt->bindParam(':oferta', $oferta);
     $stmt->bindParam(':imagen', $imagen);
    
     

  
     $result = $stmt->execute();
    
     $db = null;
     echo "Update successful! ";
     $response->getBody()->write(json_encode($result));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(200);
    } catch (PDOException $e) {
     $error = array(
       "message" => $e->getMessage()
     );
    
     $response->getBody()->write(json_encode($error));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(500);
    }
    });


 ///////////////////////

$app->POST('/addfactor', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $factor = ($data);

  $id = 0;
  $factor = ($factor['factor']);
  $estatus = "1";

 $sql = "INSERT INTO `factores`(factor,estatus) VALUES (:factor, :estatus)" ;        

  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':factor', $factor);
    $stmt->bindParam(':estatus', $estatus);
    //$stmt->bindParam(':Fecha', $Fecha);
    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });

 //obtener factores
 $app->get('/obtfactores', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT * FROM `factores`  ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obt ultimod id insertado
 $app->get('/obtultidfactores', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT MAX(id) AS id FROM factores";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 //alta baja factores
$app->put('/bajafactor/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE factores SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


//editar factores 
$app->put('/editfactor/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();

  $id = $request->getAttribute('id');
  $factor = ($data['factor']);
  
  print_r($data);
  

  
  $sql = "UPDATE factores SET factor = :factor where `id` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':factor', $factor);

   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


   ///////////////SubFactores//////////////////////

   $app->POST('/addsubfactor', function (Request $request, Response $response, array $args) {
    //$dataa = $request->getParsedBody();
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $subfactor = ($data);
  //print_r($subfactor);
    $id = 0;
    $factorid = ($subfactor['factorid']);
    $subfactorr = ($subfactor['subfactor']);
    $limitpuntos = ($subfactor['limitpuntos']);
    $estatus = "1";
  
   $sql = "INSERT INTO `subfactores`(factorid,subfactor,estatus,limitpuntos) VALUES (:factorid, :subfactorr, :estatus, :limitpuntos)" ;        
  
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':factorid', $factorid);
      $stmt->bindParam(':subfactorr', $subfactorr);
      $stmt->bindParam(':limitpuntos', $limitpuntos);
      $stmt->bindParam(':estatus', $estatus);
      //$stmt->bindParam(':Fecha', $Fecha);
      $result = $stmt->execute();
  
      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  
   });

    //obtener factores
 $app->get('/obtsubfactores', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT s.id, s.factorid, f.factor, s.subfactor, s.limitpuntos, s.estatus FROM factores f  INNER JOIN subfactores s ON f.id = s.factorid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 //obt ultimod id insertado
 $app->get('/obtultidsubfactores', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT MAX(id) AS id FROM subfactores";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });


 //alta baja subfactores
$app->put('/bajasubfactor/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE subfactores SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

  //editar subfactores 
$app->put('/editsubfactor/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();

  $id = $request->getAttribute('id');
  $factorid = ($data['factorid']);
  $subfactor = ($data['subfactor']);
  $limitpuntos = ($data['limitpuntos']);
  print_r($data);
  

  
  $sql = "UPDATE subfactores SET factorid = :factorid, subfactor = :subfactor, limitpuntos = :limitpuntos  where `id` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':factorid', $factorid);
   $stmt->bindParam(':subfactor', $subfactor);
   $stmt->bindParam(':limitpuntos', $limitpuntos);
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

  


  ///////////////categorias//////////////////////


  $app->POST('/addcategoria', function (Request $request, Response $response, array $args) {
    //$dataa = $request->getParsedBody();
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $subfactor = ($data);
  //print_r($subfactor);
    $id = 0;
    $subfactorid = ($subfactor['subfactorid']);
    $categoria = ($subfactor['categoria']);
    $estatus = "1";
  
   $sql = "INSERT INTO `categorias`(subfactorid,categoria,estatus) VALUES (:subfactorid, :categoria, :estatus)" ;        
  
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':subfactorid', $subfactorid);
      $stmt->bindParam(':categoria', $categoria);
      $stmt->bindParam(':estatus', $estatus);
      //$stmt->bindParam(':Fecha', $Fecha);
      $result = $stmt->execute();
  
      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  
   });



       //obtener cat
 $app->get('/obtcategorias', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT s.id, s.subfactorid, f.subfactor, s.categoria, s.estatus FROM subfactores f  INNER JOIN categorias s ON f.id = s.subfactorid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

  //obt ultimod id insertado
  $app->get('/obtultidcategorias', function (Request $request, Response $response) {
    $estatus = "1";
    $sql = "SELECT MAX(id) AS id FROM categorias";
   
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
      $stmt = $conn->query($sql);
      $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
     
      $response->getBody()->write(json_encode($customers));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
   });
  

 

 $app->put('/bajacategoria/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE categorias SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

   //editar subfactores 
$app->put('/editcategoria/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();

  $id = $request->getAttribute('id');
  $subfactorid = ($data['subfactorid']);
  $categoria = ($data['categoria']);
  //print_r($data);
  

  
  $sql = "UPDATE categorias SET subfactorid = :subfactorid, categoria = :categoria  where `id` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':subfactorid', $subfactorid);
   $stmt->bindParam(':categoria', $categoria);

   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


  ////////////subcategorias//////////////////

  $app->POST('/addsubcategoria', function (Request $request, Response $response, array $args) {
    //$dataa = $request->getParsedBody();
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $subfactor = ($data);
  //este es un problema al actualiiazar una tabla o cambiar de pagina print_r($subfactor);
    $id = 0;
    $categoriaid = ($subfactor['categoriaid']);
    $subcategoria = ($subfactor['subcategoria']);
    $puntaje = ($subfactor['puntaje']);
    $limitarch = ($subfactor['limitarch']);
    $medioverifi = ($subfactor['medioverifi']);
    $estatus = "1";
  
   $sql = "INSERT INTO `subcategorias`(categoriaid,subcategoria,estatus,puntaje,limitarch,medioverifi) VALUES (:categoriaid, :subcategoria, :estatus, :puntaje, :limitarch, :medioverifi)" ;        
  
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':categoriaid', $categoriaid);
      $stmt->bindParam(':subcategoria', $subcategoria);
      $stmt->bindParam(':puntaje', $puntaje);
      $stmt->bindParam(':limitarch', $limitarch);
      $stmt->bindParam(':medioverifi', $medioverifi);
      $stmt->bindParam(':estatus', $estatus);
      //$stmt->bindParam(':Fecha', $Fecha);
      $result = $stmt->execute();
  
      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  
   });

          //obtener subcat
 $app->get('/obtsubcategorias', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT s.id, s.categoriaid, f.categoria, s.subcategoria, s.puntaje, s.limitarch, s.medioverifi, s.estatus FROM categorias f  INNER JOIN subcategorias s ON f.id = s.categoriaid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->put('/bajasubcategoria/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE subcategorias SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

    //editar subca 
$app->put('/editsubcategoria/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();

  $id = $request->getAttribute('id');
  $categoriaid = ($data['categoriaid']);
  $subcategoria = ($data['subcategoria']);
  $puntaje = ($data['puntaje']);
  $limitarch = ($data['limitarch']);
  $medioverifi = ($data['medioverifi']);
  //print_r($data);
  

  
  $sql = "UPDATE subcategorias SET categoriaid = :categoriaid, subcategoria = :subcategoria, puntaje = :puntaje, limitarch = :limitarch, medioverifi = :medioverifi  where `id` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':categoriaid', $categoriaid);
   $stmt->bindParam(':subcategoria', $subcategoria);
   $stmt->bindParam(':puntaje', $puntaje);
   $stmt->bindParam(':limitarch', $limitarch);
   $stmt->bindParam(':medioverifi', $medioverifi);
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });  

  /////////////////tabla//////////////////////////

        //obtener cat
 $app->get('/obttablapdf', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT
  factores.factor,
  subfactores.subfactor,
  categorias.categoria,
  subcategorias.subcategoria,
  subcategorias.puntaje,
  subcategorias.limitarch,
  subcategorias.medioverifi
FROM factores
INNER JOIN subfactores
  ON subfactores.factorid = factores.id
INNER JOIN categorias
  ON subfactores.id = categorias.subfactorid
INNER JOIN subcategorias
  ON categorias.id = subcategorias.categoriaid WHERE factores.id = subfactores.factorid AND subfactores.id = categorias.subfactorid ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });


  ////////////puntaje y verificacion////////////////////

  $app->POST('/addpuntayverifi', function (Request $request, Response $response, array $args) {
    //$dataa = $request->getParsedBody();
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $subfactor = ($data);
  //problema es estoprint_r($subfactor);
    $id = 0;
    $subcategoriaid = ($subfactor['subcategoriaid']);
    $puntaje = ($subfactor['puntaje']);
    $medioverifi = ($subfactor['medioverifi']);
    $estatus = "1";
  
   $sql = "INSERT INTO `puntajeyverficacion`(subcategoriaid,puntaje,medioverifi,estatus) VALUES (:subcategoriaid, :puntaje, :medioverifi, :estatus)" ;        
  
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':subcategoriaid', $subcategoriaid);
      $stmt->bindParam(':puntaje', $puntaje);
      $stmt->bindParam(':medioverifi', $medioverifi);
      $stmt->bindParam(':estatus', $estatus);
      //$stmt->bindParam(':Fecha', $Fecha);
      $result = $stmt->execute();
  
      $db = null;
      $response->getBody()->write(json_encode($result));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array(
        "message" => $e->getMessage()
      );
   
      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  
   });


             //obtener subcat
 $app->get('/obtpuntyverifi', function (Request $request, Response $response) {
  $estatus = "1";
  $sql = "SELECT s.id, s.subcategoriaid, f.subcategoria, s.puntaje, s.medioverifi, s.estatus FROM subcategorias f  INNER JOIN puntajeyverficacion s ON f.id = s.subcategoriaid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->put('/bajapuntyverifi/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  

$data = $request->getParsedBody();

  $estatus = $data['estatus'];
  //print_r($data);
  $sql = "UPDATE puntajeyverficacion SET
           
           estatus = :estatus
           
  WHERE id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   //echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

  //editar editpuntayverifi 
$app->put('/editpuntayverifi/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();

  $id = $request->getAttribute('id');
  
  $subcategoriaid = ($data['subcategoriaid']);
  $puntaje = ($data['puntaje']);
  $medioverifi = ($data['medioverifi']);

  print_r($data);
  

  
  $sql = "UPDATE puntajeyverficacion SET subcategoriaid = :subcategoriaid, puntaje = :puntaje, medioverifi = :medioverifi  where `id` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':subcategoriaid', $subcategoriaid);
   $stmt->bindParam(':puntaje', $puntaje);
   $stmt->bindParam(':medioverifi', $medioverifi);

   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


  //////////////////////////////////////////////////////////////

  /////////////usuarios menu//////////

   //obtener factores
 $app->get('/obtsubfactoresu/{id}', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $factorid = $request->getAttribute('id');
  print_r($data);
  $sql = "SELECT * FROM `subfactores` WHERE `factorid` =$factorid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->get('/obtcategoriasu/{id}', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $categoriaid = $request->getAttribute('id');
  print_r($data);
  $sql = "SELECT * FROM `categorias` WHERE `subfactorid` =$categoriaid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->get('/obtsubcategoriasu/{id}', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  $categoriaid = $request->getAttribute('id');
  print_r($data);
  $sql = "SELECT * FROM `subcategorias` WHERE `categoriaid` = $categoriaid";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 //subir imagen 
 $app->POST('/subirpdf', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data['nombreArchivo']);
  $pdf = $data;
  print_r($pdf);

$dir = "/Slim/public/img/";

    $nombreArchivo = $pdf['nombreArchivo'];
    $archivo = $pdf['base64textString'];

    /* list($type, $archivo) = explode(';', $archivo);
list(, $archivo)      = explode(',', $archivo); */
$archivo = base64_decode($archivo);


    $filePath = $_SERVER['DOCUMENT_ROOT']."{$dir}".$nombreArchivo;

    file_put_contents($filePath, $archivo);
   //ver como subir imagen 


  });


///////////add datoss de archivos a bd de ususarios

$app->POST('/adddatosuserpdf', function (Request $request, Response $response, array $args) {
  $dataa = $request->getParsedBody();
  //$json = file_get_contents('php://input');
  //$data = json_decode($json, true);
  //subfactor = ($data);
 // print_r($dataa);
  $pdf = $dataa;
  //print_r($pdf);
//problema es estoprint_r($subfactor);
  $id = 0;
  //print_r($subfactor);
  $nombreArchivo = $pdf['nombreArchivo'];
  //print_r($nombreArchivo);
  $nombre = ($pdf['nombreusuario']);
  //print_r($nombre);
  //$medioverifi = ($subfactor['medioverifi']);
  //$estatus = "1";

 $sql = "INSERT INTO `archivosusuarios`(nombreusuario,nombreArchivo) VALUES (:nombreusuario, :nombreArchivo)" ;        

  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombreArchivo', $nombreArchivo);
    $stmt->bindParam(':nombreusuario', $nombre);
    //$stmt->bindParam(':medioverifi', $medioverifi);
    //$stmt->bindParam(':estatus', $estatus);
    //$stmt->bindParam(':Fecha', $Fecha);
    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });



 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 ///////////////////////////////

//enviar correo
$app->post('/enviar',function(Request $request, Response $response)
{
        $requestParamter = $request->getParsedBody();
        $dat = ($requestParamter['estatus']);
        $estatus = ($dat['name']);
        //print_r($estatus);
        //print_r($requestParamter['email']);

        $name = $requestParamter['name'];
        $cartCount = $requestParamter['cartCount'];
        $price = $requestParamter['price'];
        $middleName = $requestParamter['middleName'];
        $lastName = $requestParamter['lastName'];
        $namee = $requestParamter['namee'];
        $email = $requestParamter['email'];
        $totalprecio = $cartCount * $price;
        //$id = '1';
        sendVerificationEmail($name,$cartCount,$price, $middleName,$lastName,$namee,$email,$estatus,$totalprecio);

});


//Function to send mail, 
function sendVerificationEmail($name,$cartCount,$price, $middleName,$lastName,$namee,$email,$estatus,$totalprecio)
{      
    $mail = new PHPMailer;

    $mail->SMTPDebug=1;
    $mail->isSMTP();

    $mail->Host="smtp.gmail.com";
    $mail->Port=587;
    $mail->SMTPSecure="tls";
    $mail->SMTPAuth=true;
    $mail->Username="restaurantaut2021@gmail.com";
    $mail->Password="javier456";

    $mail->addAddress($email,"User Name");
    $mail->Subject="Detalles de su pedido";
    $mail->isHTML();
   
//$mail->Body="$htmlContent";
    $mail->Body=" 
    
    Hola, $middleName $lastName su pedido de: $name, con cantidad de: $cartCount 
    se encuentra con un estatus de: $estatus
    y con metodo de envio: $namee y su total a pagar es: $totalprecio  

    ";
    $mail->From="restaurantaut2021@gmail.com";
    $mail->FromName="PlanB";

    if($mail->send())
    {
        echo "Email Has Been Sent Your Email Address";
    }
    else
    {
        echo "Failed To Sent An Email To Your Email Address";
    }


}
//obtener menu estado alta
$app->get('/cus', function (Request $request, Response $response) {
  $estatus = "ALTA";
  $sql = "SELECT * FROM `menu` WHERE estatus = '1' ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obtener pedidos por tienda y sucursal
 $app->get('/mostpedidos/{idtienda}/{idsuc}', function (Request $request, Response $response) {
  $estatus = "ALTA";
  $idtienda = $request->getAttribute('idtienda');
  $idsuc = $request->getAttribute('idsuc');
  //Checar el where de estatus repite varias veces
  //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
  $sql = "SELECT pc.idcli, pc.name, pc.estatus, pc.price, pc.cartCount,pc.fecha, pe.id,pe.firstName,pe.lastName,pe.middleName,pe.email, pe.address, pe.city,pe.place,pe.postalCode,pe.phone,pe.descr,pe.namee,pe.valuee,pe.cardNumber,pe.expiredMonth,pe.expiredYear, pe.descri, pe.nameee, me.id, me.image FROM `pedidoscomida` pc, `pedidos` pe, `menu` me
   WHERE  pc.idcli = pe.id AND pc.idpla = me.id AND (pc.estatus = 'ALTA' OR pc.estatus = 'Procesando' OR pc.estatus = 'En espera' OR pc.estatus = 'Reembolsado' OR pc.estatus = 'Pendiente') AND pc.idtienda = $idtienda AND pc.idsuc = $idsuc ";
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
 //obtener peiddos completosd
 $app->get('/mostpedidoscomple/{idtienda}/{idsuc}', function (Request $request, Response $response) {
  $estatus = "ALTA";
  $idtienda = $request->getAttribute('idtienda');
  $idsuc = $request->getAttribute('idsuc');
  //Checar el where de estatus repite varias veces
  //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
  $sql = "SELECT pc.idcli, pc.name, pc.estatus, pc.price, pc.cartCount, pc.idtienda,pc.idsuc, pe.id,pe.firstName,pe.lastName,pe.middleName,pe.email, pe.address, pe.city,pe.place,pe.postalCode,pe.phone,pe.descr,pe.namee,pe.valuee,pe.cardNumber,pe.expiredMonth,pe.expiredYear, me.id, me.image FROM `pedidoscomida` pc, `pedidos` pe, `menu` me
   WHERE  pc.idcli = pe.id AND pc.idpla = me.id AND (pc.estatus = 'Terminado') AND pc.idtienda = $idtienda AND pc.idsuc = $idsuc";
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obtener categorias

 $app->get('/categoriasmenu/{idtienda}/{idsuc}', function (Request $request, Response $response) {
 // $estatus = "ALTA";
  $idtienda = $request->getAttribute('idtienda');
  $idsuc = $request->getAttribute('idsuc');
  //Checar el where de estatus repite varias veces
  //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
  $sql = "SELECT
	categoriasmenu.id, 
	categoriasmenu.`name`, 
	categoriasmenu.description,
  categoriasmenu.estatus,
  categoriasmenu.idtienda,
  categoriasmenu.idsucursal
FROM
	categoriasmenu where estatus = '1' AND idtienda = $idtienda AND idsucursal = $idsuc";
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->get('/categoriasmenusuctienda/{id}/{idsuc}', function (Request $request, Response $response) {
  // $estatus = "ALTA";
   $idtienda = $request->getAttribute('id');
   $idsuc = $request->getAttribute('idsuc');
   //Checar el where de estatus repite varias veces
   //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
   $sql = "SELECT
   categoriasmenu.id, 
   categoriasmenu.`name`, 
   categoriasmenu.description,
   categoriasmenu.estatus,
   categoriasmenu.idtienda,
   categoriasmenu.idsucursal
 FROM
   categoriasmenu where estatus = '1' AND idtienda = $idtienda AND idsucursal = $idsuc";
   try {
     $db = new BD();
     $conn = $db->coneccionBD();
     $stmt = $conn->query($sql);
     $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
     $db = null;
    
     $response->getBody()->write(json_encode($customers));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(200);
   } catch (PDOException $e) {
     $error = array(
       "message" => $e->getMessage()
     );
  
     $response->getBody()->write(json_encode($error));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(500);
   }
  });


//dar dealta y baja cateogrias menu
 $app->get('/categoriasmenualtabaja/{idtienda}/{idsuc}', function (Request $request, Response $response) {
  // $estatus = "ALTA";
   $idtienda = $request->getAttribute('idtienda');
   $idsuc = $request->getAttribute('idsuc');
   //Checar el where de estatus repite varias veces
   //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
   $sql = "SELECT
   categoriasmenu.id, 
   categoriasmenu.`name`, 
   categoriasmenu.`description`,
   categoriasmenu.`estatus`,
   categoriasmenu.`idtienda`,
   categoriasmenu.`idsucursal`
 FROM
   categoriasmenu WHERE idtienda = $idtienda AND idsucursal = $idsuc";
   try {
     $db = new BD();
     $conn = $db->coneccionBD();
     $stmt = $conn->query($sql);
     $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
     $db = null;
    
     $response->getBody()->write(json_encode($customers));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(200);
   } catch (PDOException $e) {
     $error = array(
       "message" => $e->getMessage()
     );
  
     $response->getBody()->write(json_encode($error));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(500);
   }
  });
//pedidos comletos para ticket
 $app->get('/mostpedidoscompletick/{idtienda}/{idsuc}/{id}', function (Request $request, Response $response) {
  $estatus = "ALTA";
  $idtienda = $request->getAttribute('idtienda');
  $idsuc = $request->getAttribute('idsuc');
  $id = $request->getAttribute('id');
  //Checar el where de estatus repite varias veces
  //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
  $sql = "SELECT pc.idcli, pc.name, pc.estatus, pc.price, pc.cartCount, pc.idtienda,pc.idsuc, pe.id,pe.firstName,pe.lastName,pe.middleName,pe.email, pe.address, pe.city,pe.place,pe.postalCode,pe.phone,pe.descr,pe.namee,pe.valuee,pe.cardNumber,pe.expiredMonth,pe.expiredYear, me.id, me.image FROM `pedidoscomida` pc, `pedidos` pe, `menu` me
  WHERE  pc.idcli = pe.id AND pc.idpla = me.id AND (pc.estatus = 'Terminado') AND pc.idtienda = $idtienda AND pc.idsuc = $idsuc AND pc.idcli = $id ";
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//mostrar las tiendas y sucursales
 $app->get('/mostrartiesuc', function (Request $request, Response $response) {
  $estatus = "ALTA";
  //Checar el where de estatus repite varias veces
  //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
  $sql = "SELECT * FROM `sucursales` WHERE Status = '1' ";
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obtener el menu por tienda y sucursal de ambos estatus
 $app->get('/suc/{id}/{idsuc}', function (Request $request, Response $response, $args) {
  $estatus = "ALTA";
  $id = $args['id'];
  $idusc =  $args['idsuc'];
  $sql = "SELECT * FROM `menu` WHERE  `idtienda`=$id AND `idsuc`=$idusc ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obrtener menu por tienda y sucursal en estatus de activo
 $app->get('/succ/{id}/{idsuc}', function (Request $request, Response $response, $args) {
  $estatus = "ALTA";
  $id = $args['id'];
  $idusc =  $args['idsuc'];
  
  $sql = "SELECT * FROM `menu` WHERE  `idtienda`=$id AND `idsuc`=$idusc AND estatus = '1'  ";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//obtener datts de platos para editar
 $app->get('/cuse/{id}', function (Request $request, Response $response, $args) {
  //Show book identified by $id
  $id = $args['id'];
  $sql = " SELECT `id`, `idtienda`, `idsuc`, `name`, `description`, `price`, `image`, `discount`, `ratingsCount`, `ratingsValue`, `availibilityCount`, `cartCount`, `weight`, `ingrediente1`, `peso1`, `weight1`, `ingrediente2`, `peso2`, `weight2`, `ingrediente3`, `peso3`, `weight3`, `ingrediente4`, `peso4`, `weight4`, `ingrediente5`, `peso5`, `weight5`, `ingrediente6`, `peso6`, `weight6`, `ingrediente7`, `peso7`, `weight7`, `ingrediente8`, `peso8`, `weight8`, `ingrediente9`, `peso9`, `weight9`, `ingrediente10`, `peso10`, `weight10`, `ingrediente11`, `peso11`, `weight11`, `ingrediente12`, `peso12`, `weight12`, `isVegetarian`, `categoryId`, `estatus` FROM `menu`
   WHERE `id`=$id";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
   
    $response->getBody()->write(json_encode($customers));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

});
//agregar platillos
 $app->POST('/addplatillos/{idtienda}/{idsuc}', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data);
  //echo 'nombre' . $data['name'];
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $id = ($data['id']);
  /* echo $idtienda ;
  echo $idsuc ;  */
  $idtienda = $request->getAttribute('idtienda');
  $idsuc = $request->getAttribute('idsuc');

  /* $idtienda = $argss['idtienda'];
  $idsuc =  $argss['idsuc']; */

  $name = ($data['name']);
  $description = ($data['description']);
  $price = ($data['price']);
  $image = ($data['image']);
  //$image = "ss";
  $discount = '0';
  $ratingsCount ='0';
  $ratingsValue = '0';
  $availibilityCount = ($data['availibilityCount']);
  $cartCount = '1';
  $weight = ($data['weight']);
  $isVegetarian = '0';
  $categoryId = ($data['categoryId']);

  $ingrediente1 = ($data['ingrediente1']);
  $peso1 = ($data['peso1']);//unidad de medidad
  $weight1 = ($data['weight1']);

  $ingrediente2 = ($data['ingrediente2']);
  $peso2 = ($data['peso2']);//unidad de medidad
  $weight2 = ($data['weight2']);

  $ingrediente3 = ($data['ingrediente3']);
  $peso3 = ($data['peso3']);//unidad de medidad
  $weight3 = ($data['weight3']);

  $ingrediente4 = ($data['ingrediente4']);
  $peso4 = ($data['peso4']);//unidad de medidad
  $weight4 = ($data['weight4']);

  $ingrediente5 = ($data['ingrediente5']);
  $peso5 = ($data['peso5']);//unidad de medidad
  $weight5 = ($data['weight5']);

  $ingrediente6 = isset(($data['ingrediente6']));
  $peso6 = isset(($data['peso6']));//unidad de medidad
  $weight6 = isset(($data['weight6']));

  $ingrediente7 = isset(($data['ingrediente7']));
  $peso7 = isset(($data['peso7']));//unidad de medidad
  $weight7 = isset(($data['weight7']));

  $ingrediente8 = isset(($data['ingrediente8']));
  $peso8 = isset(($data['peso8']));//unidad de medidad
  $weight8 = isset(($data['weight8']));

  $ingrediente9 = isset(($data['ingrediente9']));
  $peso9 = isset(($data['peso9']));//unidad de medidad
  $weight9 = isset(($data['weight9']));

  $ingrediente10 = isset(($data['ingrediente10']));
  $peso10 = isset(($data['peso10']));//unidad de medidad
  $weight10 = isset(($data['weight10']));

  $ingrediente11 = isset(($data['ingrediente11']));
  $peso11 = isset(($data['peso11']));//unidad de medidad
  $weight11 = isset(($data['weight11']));

  $ingrediente12 = isset(($data['ingrediente12']));
  $peso12 = isset(($data['peso12']));//unidad de medidad
  $weight12 = isset(($data['weight12']));

  

  $estatus = "1";
  
 
  $sql = "INSERT INTO menu (id,idtienda,idsuc,name, description, price, image,discount,ratingsCount,ratingsValue,availibilityCount,cartCount,weight,ingrediente1,peso1,weight1,ingrediente2,peso2,weight2,ingrediente3,peso3,weight3,ingrediente4,peso4,weight4,ingrediente5,peso5,weight5,ingrediente6,peso6,weight6,ingrediente7,peso7,weight7,ingrediente8,peso8,weight8,ingrediente9,peso9,weight9,ingrediente10,peso10,weight10,ingrediente11,peso11,weight11,ingrediente12,peso12,weight12,isVegetarian, categoryId, estatus) 
  VALUES (:id, :idtienda, :idsuc, :name, :description, :price, :image,:discount,:ratingsCount,:ratingsValue,:availibilityCount,:cartCount,:weight,:ingrediente1,:peso1,:weight1,:ingrediente2,:peso2,:weight2,:ingrediente3,:peso3,:weight3,:ingrediente4,:peso4,:weight4,:ingrediente5,:peso5,:weight5,:ingrediente6,:peso6,:weight6,:ingrediente7,:peso7,:weight7,:ingrediente8,:peso8,:weight8,:ingrediente9,:peso9,:weight9,:ingrediente10,:peso10,:weight10,:ingrediente11,:peso11,:weight11,:ingrediente12,:peso12,:weight12,:isVegetarian, :categoryId, :estatus)";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);

    $stmt->bindParam(':idtienda', $idtienda);
    $stmt->bindParam(':idsuc', $idsuc);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':discount', $discount);
    $stmt->bindParam(':ratingsCount', $ratingsCount);
    $stmt->bindParam(':ratingsValue', $ratingsValue);
    $stmt->bindParam(':availibilityCount', $availibilityCount);
    $stmt->bindParam(':cartCount', $cartCount);
    $stmt->bindParam(':weight', $weight);

    $stmt->bindParam(':ingrediente1', $ingrediente1);
    $stmt->bindParam(':peso1', $peso1);
    $stmt->bindParam(':weight1', $weight1);

    $stmt->bindParam(':ingrediente2', $ingrediente2);
    $stmt->bindParam(':peso2', $peso2);
    $stmt->bindParam(':weight2', $weight2);

    $stmt->bindParam(':ingrediente3', $ingrediente3);
    $stmt->bindParam(':peso3', $peso3);
    $stmt->bindParam(':weight3', $weight3);

    $stmt->bindParam(':ingrediente4', $ingrediente4);
    $stmt->bindParam(':peso4', $peso4);
    $stmt->bindParam(':weight4', $weight4);

    $stmt->bindParam(':ingrediente5', $ingrediente5);
    $stmt->bindParam(':peso5', $peso5);
    $stmt->bindParam(':weight5', $weight5);

    $stmt->bindParam(':ingrediente6', $ingrediente6);
    $stmt->bindParam(':peso6', $peso6);
    $stmt->bindParam(':weight6', $weight6);
    
    $stmt->bindParam(':ingrediente7', $ingrediente7);
    $stmt->bindParam(':peso7', $peso7);
    $stmt->bindParam(':weight7', $weight7);
    
    $stmt->bindParam(':ingrediente8', $ingrediente8);
    $stmt->bindParam(':peso8', $peso8);
    $stmt->bindParam(':weight8', $weight8);

    $stmt->bindParam(':ingrediente9', $ingrediente9);
    $stmt->bindParam(':peso9', $peso9);
    $stmt->bindParam(':weight9', $weight9);

    $stmt->bindParam(':ingrediente10', $ingrediente10);
    $stmt->bindParam(':peso10', $peso10);
    $stmt->bindParam(':weight10', $weight10);

    $stmt->bindParam(':ingrediente11', $ingrediente11);
    $stmt->bindParam(':peso11', $peso11);
    $stmt->bindParam(':weight11', $weight11);

    $stmt->bindParam(':ingrediente12', $ingrediente12);
    $stmt->bindParam(':peso12', $peso12);
    $stmt->bindParam(':weight12', $weight12);

    $stmt->bindParam(':isVegetarian', $isVegetarian);
    $stmt->bindParam(':categoryId', $categoryId);
    $stmt->bindParam(':estatus', $estatus);
 
    $result = $stmt->execute();
 
    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });

 $app->post('/agg', function (Request $request, Response $response) {

  // Get POST data
  $post = (array)$request->getParsedBody();

  $row = [
      'name' => $post['name'],
      'description' => $post['description'],
      'price' => $post['price']
  ];

  $sql = "INSERT INTO menu SET name=:name, description=:description, price=:price;";

  /** @var PDO $pdo */
  $pdo = $this->get(PDO::class);
  $success = $pdo->prepare($sql)->execute($row);

  return $response->withJson(['success' => $success]);
});
//modificar datos de menu por platillos
$app->put('/mod/{idtienda}/{idsuc}/{id}',function (Request $request, Response $response, array $args) {
$id = $request->getAttribute('id');

$idtienda = $request->getAttribute('idtienda');
$idsuc = $request->getAttribute('idsuc');

$data = $request->getParsedBody();
$name = $data["name"];
$description = $data["description"];
$categoryId = $data["categoryId"];
$price = $data["price"];
$image = $data["image"];
$availibilityCount = $data["availibilityCount"];
$weight = $data["weight"];

$sql = "UPDATE menu SET
         name = :name,
         description = :description,
         categoryId = :categoryId,
         price = :price,
         image = :image,
         availibilityCount = :availibilityCount,
         weight = :weight
         
WHERE idtienda = $idtienda AND idsuc = $idsuc AND id = $id ";

try {
  $db = new BD();
  $conn = $db->coneccionBD();

 $stmt = $conn->prepare($sql);
 $stmt->bindParam(':name', $name);
 $stmt->bindParam(':description', $description);
 $stmt->bindParam(':categoryId', $categoryId);
 $stmt->bindParam(':price', $price);
 $stmt->bindParam(':image', $image);
 $stmt->bindParam(':availibilityCount', $availibilityCount);
 $stmt->bindParam(':weight', $weight);

 $result = $stmt->execute();

 $db = null;
 echo "Update successful! ";
 $response->getBody()->write(json_encode($result));
 return $response
   ->withHeader('content-type', 'application/json')
   ->withStatus(200);
} catch (PDOException $e) {
 $error = array(
   "message" => $e->getMessage()
 );
 
 $response->getBody()->write(json_encode($error));
 return $response
   ->withHeader('content-type', 'application/json')
   ->withStatus(500);
}
});
//modificar pedidio
 $app->put('/modpedido',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();
  //$deliveryAddress = ($data['deliveryAddress']);
  //$idcli = $request->getAttribute('idcli');
  print_r($data);
  $idcli =($data['idcli']);
  $idpla =($data['id']);
  //print_r($idcli);
  //print_r($idpla);
  $dat = ($data['estatus']);
  $estatus = ($dat['name']);
  print_r($estatus);
  //echo $id;
  //$estatus = $data["estatus"];
  
  $sql = "UPDATE pedidoscomida SET
           estatus = :estatus where `idcli` = $idcli AND `idpla` = $idpla";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':estatus', $estatus);

  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );

 $response->getBody()->write(json_encode($error));
 return $response
   ->withHeader('content-type', 'application/json')
   ->withStatus(500);
}
});
//alta baja menu
$app->put('/baja/{idtienda}/{idsuc}/{id}',function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $idtienda = $request->getAttribute('idtienda');
$idsuc = $request->getAttribute('idsuc');

$data = $request->getParsedBody();
  $estatus = $data['estatus'];
  
  $sql = "UPDATE menu SET
           
           estatus = :estatus
           
  WHERE idtienda = $idtienda AND idsuc = $idsuc AND id = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':estatus', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });
//baja alta categoria menu
  $app->put('/bajacatmenu/{id}/{idtienda}/{idsuc}',function (Request $request, Response $response, array $args) {
    $id = $request->getAttribute('id');
    
    $idtienda = $request->getAttribute('idtienda');
  //$idsuc = $request->getAttribute('idsuc');
  
  $data = $request->getParsedBody();
  print_r($data);
    $estatus = $data['estatus'];
    
    $sql = "UPDATE categoriasmenu SET
             
             estatus = :estatus
             
    WHERE id = $id AND idtienda = $idtienda ";
    
    try {
      $db = new BD();
      $conn = $db->coneccionBD();
    
     $stmt = $conn->prepare($sql);
     
     $stmt->bindParam(':estatus', $estatus);
    
     $result = $stmt->execute();
    
     $db = null;
     echo "Update successful! ";
     $response->getBody()->write(json_encode($result));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(200);
    } catch (PDOException $e) {
     $error = array(
       "message" => $e->getMessage()
     );
    
     $response->getBody()->write(json_encode($error));
     return $response
       ->withHeader('content-type', 'application/json')
       ->withStatus(500);
    }
    });
//add pedidos dtos de cliente
$app->POST('/addpedidos', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
 // print_r($data);
  //echo 'deliveryAddress' . $data['deliveryAddress'];
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $deliveryAddress = ($data['deliveryAddress']);
  $deliveryMethod = ($data['deliveryMethod']['method']);
  $paymentMethod = ($data['paymentMethod']);
  $paymentMethods = ($data['paymentMethods']['method']);
  print_r($paymentMethod);
  $id = 0;
  //Direccion
  $address =($deliveryAddress['address']);
  $city = ($deliveryAddress['city']);
  $email = ($deliveryAddress['email']);
  $firstName = ($deliveryAddress['firstName']);
  $lastName = ($deliveryAddress['lastName']);
  $middleName =($deliveryAddress['middleName']);
  $phone = ($deliveryAddress['phone']);
  $place = ($deliveryAddress['place']);
  $postalCode = ($deliveryAddress['postalCode']);
  
  $descr = ($deliveryMethod['desc']);
  $namee = ($deliveryMethod['name']);
  $valuee = ($deliveryMethod['value']);
  
  /* $cardHolderName = ($paymentMethod['cardHolderName']);
  $cardNumber = ($paymentMethod['cardNumber']);
  $cvv = ($paymentMethod['cvv']);
  $expiredMonth = ($paymentMethod['expiredMonth']);
  $expiredYear = ($paymentMethod['expiredYear']);
 */
  $method = ($paymentMethods['method']);

  $descri = ($paymentMethods['desc']);
  $nameee = ($paymentMethods['name']);
  $valueee = ($paymentMethods['value']);
  //$deliveryMethod = ($data['deliveryMethod']);
  //$paymentMethod = 'paymentMethod';
  //$categoryId = ($data['categoryId']);
 
  $sql = "INSERT INTO pedidos (id,address, city, email, firstName, lastName, middleName, phone, place, postalCode, descr, namee, valuee,descri,nameee,valueee ) 
  VALUES (:id, :address, :city, :email, :firstName,:lastName,:middleName,:phone,:place,:postalCode,:descr,:namee,:valuee,:descri,:nameee,:valueee)";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    //direccion
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':middleName', $middleName);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':place', $place);
    $stmt->bindParam(':postalCode', $postalCode);
//envio
    $stmt->bindParam(':descr', $descr);
    $stmt->bindParam(':namee', $namee);
    $stmt->bindParam(':valuee', $valuee);
    //pago
    $stmt->bindParam(':descri', $descri);
    $stmt->bindParam(':nameee', $nameee);
    $stmt->bindParam(':valueee', $valueee);
    /* $stmt->bindParam(':cardHolderName', $cardHolderName);
    $stmt->bindParam(':cardNumber', $cardNumber);
    $stmt->bindParam(':cvv', $cvv);
    $stmt->bindParam(':expiredMonth', $expiredMonth);
    $stmt->bindParam(':expiredYear', $expiredYear); */
    //$stmt->bindParam(':deliveryMethod', $deliveryMethod);
    //$stmt->bindParam(':paymentMethod', $paymentMethod);
    //$stmt->bindParam(':categoryId', $categoryId);
 
    $result = $stmt->execute();
 
    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
 });
//add pedidos de comida por cliente
 $app->POST('/addpedidoscomi', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
 //print_r($dataa);
  //echo 'deliveryAddress' . $data['deliveryAddress'];
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  /* $deliveryAddress = ($data['deliveryAddress']);
  $deliveryMethod = ($data['deliveryMethod']['method']);
  $paymentMethod = ($data['paymentMethod']); */
  

  

  //$valor =  sizeof($data);
  //echo sizeof($data);
  //print_r($data);
  //$conta = 0;

  //$i=0;
  $comida = ($data);
  //print_r($comida);

  $dbs = new BD();
    $conne = $dbs->coneccionBD();

/* $sqlll = "SELECT MAX(id) AS id FROM pedidos";
$resulta = $conne->query($sqlll); */
/* $query = "SELECT `*` FROM pedidos";
$results = mysqli_query($conne, $query);
print_r($results); */
  
  $id = 0;
  $idtienda = ($comida['idtienda']);
  $idsuc = ($comida['idsuc']);
  $idpla = ($comida['id']);
  $name = ($comida['name']);
  $description = ($comida['description']);
  $price = ($comida['price']);
  $cartCount = ($comida['cartCount']);
  $categoryId = ($comida['categoryId']);
  $estatus = "ALTA";
  $Object = new DateTime();  
$Object->setTimezone(new DateTimeZone('Mexico/BajaSur'));
$DateAndTime = $Object->format("d-m-Y h:i:sa");  
print_r($DateAndTime);
  /* $sql = "INSERT INTO pedidoscomida (id,idpla, name, description, price, cartCount, categoryId) 
  VALUES (:id, :idpla, :name, :description, :price,:cartCount,:categoryId)"; */
 ///VER COMO INSERTAR EL PLATILLOY PLATILLOS
 $sql = "INSERT INTO pedidoscomida (idcli, idtienda,idsuc,idpla, name, description, price, cartCount, categoryId,estatus,fecha)
 SELECT MAX(id), :idtienda, :idsuc, :idpla, :name, :description, :price, :cartCount, :categoryId, :estatus, NOW()
 FROM pedidos " ;        

 /*  $sql = "INSERT INTO pedidoscomida (id, idpla, name,description,price,cartCount,categoryId)
  SELECT id, idpla ,name,description,price,cartCount,categoryId FROM pedidos WHERE id=1"; */
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);
   // $stmt->bindParam(':id', $id);
    //direccion
    $stmt->bindParam(':idtienda', $idtienda);
    $stmt->bindParam(':idsuc', $idsuc);
    $stmt->bindParam(':idpla', $idpla);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':cartCount', $cartCount);
    $stmt->bindParam(':categoryId', $categoryId);
    $stmt->bindParam(':estatus', $estatus);
    //$stmt->bindParam('fecha', $$DateAndTime);
    //$stmt->bindParam(':deliveryMethod', $deliveryMethod);
    //$stmt->bindParam(':paymentMethod', $paymentMethod);
    //$stmt->bindParam(':categoryId', $categoryId);
    
    $result = $stmt->execute();
    //$i=$i+1;
    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }
  
//}
 });
//tiendas-------------------------------------------------------------------------------
 $app->POST('/addtienda', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
 
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $tiendas = ($data);

    /* $idtienda = $request->getAttribute('idtienda');
    $idsuc = $request->getAttribute('idsuc'); */
  $id = 0;
  $Nombre = ($tiendas['Nombre']);
  $Telefono = ($tiendas['Telefono']);
  $Correo = ($tiendas['Correo']);
  $Fecha = ($tiendas['Fecha']);
 

  $estatus = "1";

 $sql = "INSERT INTO `tiendas`(ID_tienda, Nombre, Telefono,Correo,Fecha) VALUES (:ID_tienda, :Nombre, :Telefono,:Correo,NOW())" ;        

  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':ID_tienda', $id);
    $stmt->bindParam(':Nombre', $Nombre);
    $stmt->bindParam(':Telefono', $Telefono);
    $stmt->bindParam(':Correo', $Correo);
    //$stmt->bindParam(':Fecha', $Fecha);
    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });

 $app->put('/edittiendas/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();
  //$deliveryAddress = ($data['deliveryAddress']);
  $id = $request->getAttribute('id');
  $Nombre = ($data['Nombre']);
  $Telefono = ($data['Telefono']);
  $Correo = ($data['Correo']);
  //$Fecha = ($data['Fecha']);
  print_r($data);
  /* print_r($description);
  print_r($estatus); */
  //print_r($estatus);
  //echo $id;
  //$estatus = $data["estatus"];
  
  $sql = "UPDATE tiendas SET Nombre = :Nombre, Telefono = :Telefono, Correo = :Correo where `ID_tienda` = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':Nombre', $Nombre);
    $stmt->bindParam(':Telefono', $Telefono);
    $stmt->bindParam(':Correo', $Correo);
    
  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


  $app->get('/tiendasaltabaja', function (Request $request, Response $response) {
    // $estatus = "ALTA";
     /* $idtienda = $request->getAttribute('idtienda');
     $idsuc = $request->getAttribute('idsuc' );*/
     //Checar el where de estatus repite varias veces
     //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
     $sql = "SELECT
     tiendas.ID_tienda, 
     tiendas.`Nombre`, 
     tiendas.`Telefono`,
     tiendas.`Correo`,
     tiendas.`Fecha`
     
   FROM
   tiendas ";
     try {
       $db = new BD();
       $conn = $db->coneccionBD();
       $stmt = $conn->query($sql);
       $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
       $db = null;
      
       $response->getBody()->write(json_encode($customers));
       return $response
         ->withHeader('content-type', 'application/json')
         ->withStatus(200);
     } catch (PDOException $e) {
       $error = array(
         "message" => $e->getMessage()
       );
    
       $response->getBody()->write(json_encode($error));
       return $response
         ->withHeader('content-type', 'application/json')
         ->withStatus(500);
     }
    });


 //tiendas-------------------------------------------------------------------------------

//sucursales-------------------------------------------------------------------------------

$app->put('/bajasucursal/{id}',function (Request $request, Response $response, array $args) {
  
  //$idtienda = $request->getAttribute('idtienda');
 // $idsucursal = $request->getAttribute('idsuc');
  $id = $request->getAttribute('id');

$data = $request->getParsedBody();
print_r($data);
  $estatus = $data['estatus'];
  
  $sql = "UPDATE sucursales SET
           
           `Status` = :Status
           
  WHERE ID_sucursal = $id ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   
   $stmt->bindParam(':Status', $estatus);
  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


$app->POST('/addsucursal', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
 
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $sucursales = ($data);
print_r($sucursales);
    /* $idtienda = $request->getAttribute('idtienda');
    $idsuc = $request->getAttribute('idsuc'); */
  $id = 0;
  $Pseudonimo = ($sucursales['Pseudonimo']);
  $Ubicacion = ($sucursales['Ubicacion']);
  $Fechaalta = ($sucursales['Fechaalta']);
  $idzonsucursal = ($sucursales['ID_zonasucursal']);
  $idempleado = ($sucursales['ID_empleado']);
  $status = '1';
  $idtienda = ($sucursales['ID_tienda']);
  //$ID_sucursal = ($sucursales['ID_sucursal']);
  $idhorario = ($sucursales['ID_horario']);
 

  $estatus = "1";
//fallaba porque se les ocurrio poner el nombre dividirlo con - un ejemplo era ID_zona-sucursal asi no se reconoce
 $sql = "INSERT INTO `sucursales`(ID_sucursal, Pseudonimo, Ubicacion,Fechaalta,Status,ID_zonasucursal,ID_empleado,ID_tienda,ID_horario) VALUES (:ID_sucursal, :Pseudonimo, :Ubicacion,NOW(),:Status,:ID_zonasucursal,:ID_empleado,:ID_tienda,:ID_horario)" ;        

  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':ID_sucursal', $id);
    $stmt->bindParam(':Pseudonimo', $Pseudonimo);
    $stmt->bindParam(':Ubicacion', $Ubicacion);
    //$stmt->bindParam(':Fechaalta', $Fechaalta);
    $stmt->bindParam(':Status', $status);
    $stmt->bindParam(':ID_zonasucursal', $idzonsucursal);
    $stmt->bindParam(':ID_empleado', $idempleado);
    $stmt->bindParam(':ID_tienda', $idtienda);
    $stmt->bindParam(':ID_horario', $idhorario);
    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });

 $app->put('/editsucursal/{idtienda}/{idsucursal}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();
  //$deliveryAddress = ($data['deliveryAddress']);
  $idtienda = $request->getAttribute('idtienda');
  $idsucursal = $request->getAttribute('idsucursal');

  $Pseudonimo = ($data['Pseudonimo']);
  $Ubicacion = ($data['Ubicacion']);
 // $Fechaalta = ($data['Fechaalta']);
  //$idzonsucursal = ($data['idzonsucursal']);
  $idempleado = ($data['idempleado']);
  //$status = ($data['status']);
 // $idtienda = ($data['idtienda']);
  //$idhorario = ($data['idhorario']);
  print_r($data);
  /* print_r($description);
  print_r($estatus); */
  //print_r($estatus);
  //echo $id;
  //$estatus = $data["estatus"];
  
  $sql = "UPDATE sucursales SET Pseudonimo = :Pseudonimo, Ubicacion = :Ubicacion where `ID_tienda` = $idtienda AND `ID_sucursal` = $idsucursal ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':Pseudonimo', $Pseudonimo);
    $stmt->bindParam(':Ubicacion', $Ubicacion);
   /*  $stmt->bindParam(':Correo', $Correo); */
    
  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });


  $app->get('/sucursalaltabaja', function (Request $request, Response $response) {
    // $estatus = "ALTA";
     /* $idtienda = $request->getAttribute('idtienda');
     $idsuc = $request->getAttribute('idsuc' );*/
     //Checar el where de estatus repite varias veces
     //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
     $sql = "SELECT
     sucursales.ID_sucursal, 
     sucursales.`Pseudonimo`, 
     sucursales.`Ubicacion`,
     sucursales.`Fechaalta`,
     sucursales.`Status`,
     sucursales.`ID_zonasucursal`,
     sucursales.`ID_empleado`,
     sucursales.`ID_tienda`,
     sucursales.`ID_horario`
   FROM
   sucursales ";
     try {
       $db = new BD();
       $conn = $db->coneccionBD();
       $stmt = $conn->query($sql);
       $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
       $db = null;
      
       $response->getBody()->write(json_encode($customers));
       return $response
         ->withHeader('content-type', 'application/json')
         ->withStatus(200);
     } catch (PDOException $e) {
       $error = array(
         "message" => $e->getMessage()
       );
    
       $response->getBody()->write(json_encode($error));
       return $response
         ->withHeader('content-type', 'application/json')
         ->withStatus(500);
     }
    });


    $app->get('/horariossuc', function (Request $request, Response $response) {
      // $estatus = "ALTA";
       /* $idtienda = $request->getAttribute('idtienda');
       $idsuc = $request->getAttribute('idsuc' );*/
       //Checar el where de estatus repite varias veces
       //$sql = "SELECT * FROM `pedidoscomida` WHERE estatus = 'ALTA' ";
       $sql = "SELECT
       horario.ID_horario, 
       horario.`InicioLunes`, 
       horario.`FinLunes`, 
       horario.`InicioMartes`, 
       horario.`FinMartes`, 
       horario.`InicioMiercoles`, 
       horario.`FinMiercoles`, 
       horario.`InicioJueves`, 
       horario.`FinJueves`, 
       horario.`InicioViernes`, 
       horario.`FinViernes`, 
       horario.`InicioSabado`, 
       horario.`FinSabado`, 
       horario.`InicioDomingo`, 
       horario.`FinDomingo`
     FROM
       horario ";
       try {
         $db = new BD();
         $conn = $db->coneccionBD();
         $stmt = $conn->query($sql);
         $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
         $db = null;
        
         $response->getBody()->write(json_encode($customers));
         return $response
           ->withHeader('content-type', 'application/json')
           ->withStatus(200);
       } catch (PDOException $e) {
         $error = array(
           "message" => $e->getMessage()
         );
      
         $response->getBody()->write(json_encode($error));
         return $response
           ->withHeader('content-type', 'application/json')
           ->withStatus(500);
       }
      });
  

 //sucursales-------------------------------------------------------------------------------
//agregar categoriar por suc y tienda
 $app->POST('/addcategoriasmenu/{idtienda}/{idsuc}', function (Request $request, Response $response, array $args) {
  //$dataa = $request->getParsedBody();
 
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $catmenu = ($data);
  $dbs = new BD();
    $conne = $dbs->coneccionBD();
    $idtienda = $request->getAttribute('idtienda');
    $idsuc = $request->getAttribute('idsuc');
  $id = 0;
  $description = ($catmenu['description']);
  $name = ($catmenu['name']);

  $estatus = "1";

 $sql = "INSERT INTO `categoriasmenu`(id, name, description,estatus,idtienda,idsucursal) VALUES (:id,:name,:description,:estatus,:idtienda,:idsucursal)" ;        


  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':estatus', $estatus);
    $stmt->bindParam(':idtienda', $idtienda);
    $stmt->bindParam(':idsucursal', $idsuc);

    $result = $stmt->execute();

    $db = null;
    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $e) {
    $error = array(
      "message" => $e->getMessage()
    );
 
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus(500);
  }

 });
//editar categorias 
 $app->put('/editcategoriasmenu/{id}',function (Request $request, Response $response, array $args) {
  
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $data = $request->getParsedBody();
  //$deliveryAddress = ($data['deliveryAddress']);
  $id = $request->getAttribute('id');
  $description = ($data['description']);
  $name = ($data['name']);
  $estatus = ($data['estatus']);
  $idtienda = ($data['idtienda']);
  $idsucursal = ($data['idsucursal']);
  print_r($data);
  print_r($description);
  print_r($estatus);
  //print_r($estatus);
  //echo $id;
  //$estatus = $data["estatus"];
  
  $sql = "UPDATE categoriasmenu SET description = :description, name = :name where `id` = $id AND idtienda = $idtienda AND idsucursal = $idsucursal ";
  
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
  
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':name', $name);
   $stmt->bindParam(':description', $description);

  
   $result = $stmt->execute();
  
   $db = null;
   echo "Update successful! ";
   $response->getBody()->write(json_encode($result));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(200);
  } catch (PDOException $e) {
   $error = array(
     "message" => $e->getMessage()
   );
  
   $response->getBody()->write(json_encode($error));
   return $response
     ->withHeader('content-type', 'application/json')
     ->withStatus(500);
  }
  });

//subir imagen 
 $app->POST('/subirimg', function (Request $request, Response $response, array $args) {
   /* $data = $request->getParsedBody();
  //print_r($data);
   
  //$img = $data;
  //print_r($img);

$dir = "/Slim/public/img/";
    //$nombre = $params->nombre;
    $nombreArchivo = $data['nombreArchivo'];
    $archivo = $data['base64textString'];
   // echo $nombreArchivo;
    //echo $archivo;
    list($type, $archivo) = explode(';', $archivo);
list(, $archivo)      = explode(',', $archivo);
$archivo = base64_decode($archivo);
    //$archivo = base64_decode($archivo);
    //echo $archivo;
    //$descripcion2 = $params->descripcion2;
    //$precio2 = $params->precio2;
    //$carpeta_destino = 'C:/Users/dxcen/Documents/Angular 12/Restaurante/src/assets/';
///$idproducto2 = $params->idproducto2;
    $filePath = $_SERVER['DOCUMENT_ROOT']."{$dir}".$nombreArchivo;
    //echo $nombreArchivo;
    //print_r($nombreArchivo);
    //print_r($archivo);
    //echo $archivo;
    //echo $nombreArchivo;
    //echo  $_SERVER['DOCUMENT_ROOT'];
    file_put_contents($filePath, $archivo);
   //ver como subir imagen 
 //} */
  });

 

$app->run();