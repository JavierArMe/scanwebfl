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


use Slim\Http\UploadedFile;


require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../conexion/conexion.php';
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

$app->get('/cus', function (Request $request, Response $response) {
  $sql = "SELECT * FROM menu";
 
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

 $app->get('/cuse/{id}', function (Request $request, Response $response, $args) {
  //Show book identified by $id
  $id = $args['id'];
  $sql = "SELECT `id`, `name`, `description`, `price`, `image`, `discount`, `ratingsCount`, `ratingsValue`, `availibilityCount`, `cartCount`, `weight`, `isVegetarian`, `categoryId` FROM `menu`
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

 $app->POST('/addplatillos', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
  //print_r($data);
  //echo 'nombre' . $data['name'];
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $id = ($data['id']);
  $name = ($data['name']);
  $description = ($data['description']);
  $price = ($data['price']);
  $image = ($data['image']);
  $discount = '0';
  $ratingsCount ='0';
  $ratingsValue = '0';
  $availibilityCount = ($data['availibilityCount']);
  $cartCount = '1';
  $weight = ($data['weight']);
  $isVegetarian = '0';
  $categoryId = ($data['categoryId']);
 
  $sql = "INSERT INTO menu (id,name, description, price, image,discount,ratingsCount,ratingsValue,availibilityCount,cartCount,weight,isVegetarian, categoryId) 
  VALUES (:id, :name, :description, :price, :image,:discount,:ratingsCount,:ratingsValue,:availibilityCount,:cartCount,:weight,:isVegetarian, :categoryId)";
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
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
    $stmt->bindParam(':isVegetarian', $isVegetarian);
    $stmt->bindParam(':categoryId', $categoryId);
 
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

$app->put('/mod/{id}',function (Request $request, Response $response, array $args) {
$id = $request->getAttribute('id');
$data = $request->getParsedBody();
$name = $data["name"];
$description = $data["description"];
$categoryId = $data["categoryId"];
$price = $data["price"];
$availibilityCount = $data["availibilityCount"];
$weight = $data["weight"];

$sql = "UPDATE menu SET
         name = :name,
         description = :description,
         categoryId = :categoryId,
         price = :price,
         availibilityCount = :availibilityCount,
         weight = :weight
         
WHERE id = $id";

try {
  $db = new BD();
  $conn = $db->coneccionBD();

 $stmt = $conn->prepare($sql);
 $stmt->bindParam(':name', $name);
 $stmt->bindParam(':description', $description);
 $stmt->bindParam(':categoryId', $categoryId);
 $stmt->bindParam(':price', $price);
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

$app->POST('/addpedidos', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();
 // print_r($data);
  //echo 'deliveryAddress' . $data['deliveryAddress'];
  //$json = file_get_contents('php://input');
  //$data = json_decode($json);
  $deliveryAddress = ($data['deliveryAddress']);
  $deliveryMethod = ($data['deliveryMethod']['method']);
  $paymentMethod = ($data['paymentMethod']);
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
  
  $cardHolderName = ($paymentMethod['cardHolderName']);
  $cardNumber = ($paymentMethod['cardNumber']);
  $cvv = ($paymentMethod['cvv']);
  $expiredMonth = ($paymentMethod['expiredMonth']);
  $expiredYear = ($paymentMethod['expiredYear']);
  //$deliveryMethod = ($data['deliveryMethod']);
  //$paymentMethod = 'paymentMethod';
  //$categoryId = ($data['categoryId']);
 
  $sql = "INSERT INTO pedidos (id,address, city, email, firstName, lastName, middleName, phone, place, postalCode, descr, namee, valuee, cardHolderName,cardNumber,cvv, expiredMonth,expiredYear ) 
  VALUES (:id, :address, :city, :email, :firstName,:lastName,:middleName,:phone,:place,:postalCode,:descr,:namee,:valuee,:cardHolderName, :cardNumber, :cvv, :expiredMonth, :expiredYear)";
 
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
    $stmt->bindParam(':cardHolderName', $cardHolderName);
    $stmt->bindParam(':cardNumber', $cardNumber);
    $stmt->bindParam(':cvv', $cvv);
    $stmt->bindParam(':expiredMonth', $expiredMonth);
    $stmt->bindParam(':expiredYear', $expiredYear);
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

 $app->POST('/addpedidoscomi', function (Request $request, Response $response, array $args) {
 // $data = $request->getParsedBody();
 // print_r($data);
  //echo 'deliveryAddress' . $data['deliveryAddress'];
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  /* $deliveryAddress = ($data['deliveryAddress']);
  $deliveryMethod = ($data['deliveryMethod']['method']);
  $paymentMethod = ($data['paymentMethod']); */
  $valor =  sizeof($data);
  echo sizeof($data);
  $conta = 0;
  /* while ($conta <= $valor) {
    # code...
    $conta = $conta+1;
    echo $conta;
  } */
  //foreach ($data as $data) {
    # code...
  /* }
  for ($i=0; $i < 100; $i++) { 
    $comida = ($data)[$i]; */
  //$key = $key+1;
  $i=0;
  $comida = ($data)[1];
  print_r($comida)[$i];

  $id = 0;
  $idpla = ($comida['id']);
  $name = ($comida['name']);
  $description = ($comida['description']);
  $price = ($comida['price']);
  $cartCount = ($comida['cartCount']);
  $categoryId = ($comida['categoryId']);
 
  $sql = "INSERT INTO pedidoscomida (id,idpla, name, description, price, cartCount, categoryId) 
  VALUES (:id, :idpla, :name, :description, :price,:cartCount,:categoryId)";
 ///VER COMO INSERTAR EL PLATILLOY PLATILLOS

 /*  $sql = "INSERT INTO pedidoscomida (id, idpla, name,description,price,cartCount,categoryId)
  SELECT id, idpla ,name,description,price,cartCount,categoryId FROM pedidos WHERE id=1"; */
 
  try {
    $db = new BD();
    $conn = $db->coneccionBD();
   
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    //direccion
    $stmt->bindParam(':idpla', $idpla);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':cartCount', $cartCount);
    $stmt->bindParam(':categoryId', $categoryId);
    

    //$stmt->bindParam(':deliveryMethod', $deliveryMethod);
    //$stmt->bindParam(':paymentMethod', $paymentMethod);
    //$stmt->bindParam(':categoryId', $categoryId);
    
    $result = $stmt->execute();
    $i=$i+1;
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

 $app->POST('/subirimg', function (Request $request, Response $response, array $args) {
  // $data = $request->getParsedBody();
  // print_r($data);
   //echo 'deliveryAddress' . $data['deliveryAddress'];
   $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
 
  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
  
  /* require("conexion.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = conexion(); // CREA LA CONEXION */


$dir = "/Slim/";
    //$nombre = $params->nombre;
    $nombreArchivo = $params->nombreArchivo;
    $archivo = $params->base64textString;
    $archivo = base64_decode($archivo);
    //$descripcion2 = $params->descripcion2;
    //$precio2 = $params->precio2;
    //$carpeta_destino = 'C:/Users/dxcen/Documents/Angular 12/Restaurante/src/assets/';
///$idproducto2 = $params->idproducto2;
    $filePath = $_SERVER['DOCUMENT_ROOT']."{$dir}".$nombreArchivo;
    echo  $_SERVER['DOCUMENT_ROOT'];
    file_put_contents($filePath, $archivo);
   
 //}
  });

  $container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/Slim';

$app->post('/uploadimg', function(Request $request, Response $response) {
    $directory = $this->get('upload_directory');

    $uploadedFiles = $request->getUploadedFiles();

    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['example1'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $response->write('uploaded ' . $filename . '<br/>');
    }
});

/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory directory to which the file is moved
 * @param UploadedFile $uploadedFile file uploaded file to move
 * @return string filename of moved file
 */
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app->run();