<?php

header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(array('status' => false));
  exit;
}

$path=$_SERVER['DOCUMENT_ROOT']."/Slim/public/img/";

if (isset($_FILES['uploadFile'])) { 
  $generatedName ='abc.pdf';
  $filePath = $path.$generatedName;

  if (!is_writable($path)) {
    echo json_encode(array(
      'status' => false,
      'msg'    => 'Destination directory not writable.'
    ));
    exit;
  }
print_r($filePath);
  if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], $filePath)) {
    echo json_encode(array(
      'status'        => true,
      'originalName'  => $originalName,
      'generatedName' => $generatedName
    ));
  }
}
else {
  echo json_encode(
    array('status' => false, 'msg' => 'No file uploaded.')
    //print_r($_FILES)
  );
  exit;
}

?>