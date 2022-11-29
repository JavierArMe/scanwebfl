<?php
class BD{
private $host = 'localhost';
private $user = 'root';
private $pass = '';
private $nombd = 'productos';
public function coneccionBD(){
$mysqlConn = "mysql:host=$this->host;dbname=$this->nombd";
$dbConn = new PDO($mysqlConn, $this->user, $this->pass); 
$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* class BD{
    private $host = '187.216.118.68';
    private $user = 'pruebas';
    private $pass = 'xtPduvLlPHo8IPVE';
    private $nombd = 'pruebas';
    public function coneccionBD(){
    $mysqlConn = "mysql:host=$this->host;dbname=$this->nombd";
    $dbConn = new PDO($mysqlConn, $this->user, $this->pass);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     */
/* if($dbConn){
echo 'ok';
}else{
echo 'fail';
} */

return $dbConn;

}
}