<?php
  class MyDB extends SQLite3{
    function __construct(){
      $this->open('dados_lts.db');
    }
  }
  if(!empty($_POST['valor'])) {
    echo "<option value=''>selecione aqui</option>";
    $valor = $_POST['valor'];
    $db = new MyDB();
    $sql = "SELECT estrutura FROM " . $valor . " ORDER BY id LIMIT (SELECT MAX(id-1) FROM " . $valor . ")";
    $result = $db->query($sql);
    while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
      echo "<option value='" . $row['estrutura'] ."'>" . $row['estrutura'] ."</option>";
    }
    $db->close();
  }
  else {
    echo "<option value=''> </option>";
  }
 ?>
