<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET,POST");
//header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Middleware\OutputBufferingMiddleware;

use Selective\BasePath\BasePathMiddleware;

require __DIR__ . '/../vendor/autoload.php';
//require __DIR__ . '/../conexion/conexion.php';

/* $bd = new BD();
$bd = $bd->coneccionBD(); */

$app = AppFactory::create();
//$app->setBasePath('/Slim/public');
$app->get('mas', function (Request $request, Response $response, $args) {
    $sql = "SELECT * FROM `menu`";
    try{
    $db = new BD();
    $db = $db->coneccionBD();

    $STMT = $db->query($sql);
    $friend = $STMT->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    $response->getBody()->write(json_encode($friend));
    echo json_encode($response);
    return $response
            ->withHeader('content-type','application/json')
            ->withStatus(200);
            echo json_encode($response);
    }catch(PDOException $e){
        $error = array(
            "message"=> $e->getMessage()
        );
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type','application/json')
            ->withStatus(500);
            echo json_encode($response);
    }
    echo json_encode($response);
    });
   // $app->run();
?>