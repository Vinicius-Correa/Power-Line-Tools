<?php
  class MyDB extends SQLite3{
    function __construct(){
      $this->open('dados_lts.db');
    }
  }
  //$mod_elast = $coefic_dilat = $carga_ruptura = $temp_amb = $altura_cabo = 0;
  //$dist_cruza = $comp_vao = $altura_obs = $tipo_obs = $dist_segur = 0;
  //$altura_final = $diferenca = 0;
 ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Power Line Tools</title>
  <link rel="stylesheet" type="text/css" href="estilo.css">

</head>
  <body>
    <!-- Cabeçalho -->
    <header><h1>Power Line Tools</h1></header>
    <input type="checkbox" id="chk">
    <label for="chk" class="menu-icon">&#9776;</label>

    <!-- Menu -->
    <nav id="principal">
      <ul>
        <li><a class="menu_link" href="./index.html">Início</a></li>
        <li><a class="menu_link" href="#analise_projeto">Análise de projeto<span class="menu_mais">+</span></a></li>
        <li><a class="menu_link" href="#projeto_obra">Projeto de obra<span class="menu_mais">+</span></a></li>
        <li><a class="menu_link" href="#comissionamento">Comissionamento<span class="menu_mais">+</span></a></li>
        <li><a class="menu_link" href="#relatorio_lt">Relatório de LT<span class="menu_mais">+</span></a></li>
      </ul>
    </nav>
    <nav class="menu_auxiliar" id="analise_projeto">
      <ul>
        <li><a class="voltar" href="#">Voltar</a></li>
        <li><a class="menu_link" href="#">Faixa de segurança</a></li>
        <li><a class="menu_link" href="./travessia.php">Análise de Travessia</a></li>
        <li><a class="menu_link" href="#">Travessia com obstáculo</a></li>
      </ul>
    </nav>
    <nav class="menu_auxiliar" id="projeto_obra">
      <ul>
        <li><a class="voltar" href="#">Voltar</a></li>
        <li><a class="menu_link" href="#">Locação de estrutura</a></li>
        <li><a class="menu_link" href="#">Tabela de regulagem</a></li>
        <li><a class="menu_link" href="#">Estrutura definitiva</a></li>
        <li><a class="menu_link" href="#">Estrutura de emergência</a></li>
      </ul>
    </nav>
    <nav class="menu_auxiliar" id="comissionamento">
      <ul>
        <li><a class="voltar" href="#">Voltar</a></li>
        <li><a class="menu_link" href="#">Levantamento dos cabos</a></li>
        <li><a class="menu_link" href="#">Travessia</a></li>
      </ul>
    </nav>
    <nav class="menu_auxiliar" id="relatorio_lt">
      <ul>
        <li><a class="voltar" href="#">Voltar</a></li>
        <li><a class="menu_link" href="#">Estrutura</a></li>
        <li><a class="menu_link" href="#">Tramo</a></li>
        <li><a class="menu_link" href="#">Vão</a></li>
      </ul>
    </nav>

    <!-- Formulário -->
    <div class="container">
      <?php
        $error = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $db = new MyDB();
          $valor_lt = $_POST["sigla_lt"];
          $sql = 'SELECT sigla_lt, tensao, temp_eds, temp_max, temp_emer FROM lts WHERE valor = "' . $valor_lt . '"';
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $sigla_lt = $row['sigla_lt'];
            $tensao = $row['tensao'];
            $temp_eds = $row['temp_eds'];
            $temp_max = $row['temp_max'];
            $temp_emer = $row['temp_emer'];
          }
          $vao_lt = $_POST["vao_lt"];
          $sql = 'SELECT estrutura, cabo_condutor, vao_frente FROM ' . $valor_lt . ' WHERE estrutura = "' . $vao_lt . '"';
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $vao1 = $row['estrutura'];
            $cabo_cod = $row['cabo_condutor'];
            $vao_frente = $row['vao_frente'];
          }
          $sql = 'SELECT id FROM ' . $valor_lt . ' WHERE estrutura = "' . $vao1 . '"';
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $id = $row['id'];
            $id_avante = $id + 1;
          }
          $sql = 'SELECT estrutura FROM ' . $valor_lt . ' WHERE id = ' . $id_avante;
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $vao2 = $row['estrutura'];
          }
          $vao_lt = $vao1 . " - " . $vao2;
          $temp_amb = $_POST["temp_amb"];
          $altura_cabo = $_POST["altura_cabo"];
          $cod_obs = $_POST["tipo_obs"];
          $sql = 'SELECT "' . $tensao . '",descricao FROM tipo_obstaculo WHERE id = "' . $cod_obs . '"';
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $dist_segur_max = $row[$tensao];
            $tipo_obs = $row['descricao'];
          }
          $altura_obs = $_POST["altura_obs"];
          $dist_cruza = $_POST["dist_cruza"];
          if (empty($_POST["comp_vao"])) {
            $comp_vao = $vao_frente;
          }
          else {
            $comp_vao = $_POST["comp_vao"];
          }
          $sql = 'SELECT cabo, mod_elast, secao, massa, coefic_dilat, ruptura FROM cabos WHERE id = "' . $cabo_cod . '"';
          $result = $db->query($sql);
          while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
            $cabo = $row['cabo'];
            $massa_linear = $row['massa'];
            $carga_ruptura = $row['ruptura'];
            $mod_elast = $row['mod_elast'];
            $secao = $row['secao'];
            $coefic_dilat = $row['coefic_dilat'];
            $tracao_eds = $carga_ruptura * 0.2;
          }

          // Cálculo do vão regulador
          $anc_a = 0;
          $anc_p = 0;
          $i = 0;
          $num_ar = 0;
          $den_ar = 0;
          $sql = "SELECT aplicacao_estrut FROM '" . $valor_lt . "' WHERE id = '" . $id . "'";
          $result1 = $db->query($sql);
          while($row1 = $result1->fetchArray(SQLITE3_ASSOC) ) {
            if ($row1['aplicacao_estrut'] != 'A'){
              $i = $id - 1;
              while ($anc_a == 0){
                $sql = "SELECT aplicacao_estrut FROM '" . $valor_lt . "' WHERE id = '" . $i . "'";
                $result2 = $db->query($sql);
                while($row2 = $result2->fetchArray(SQLITE3_ASSOC) ) {
                  if ($row2['aplicacao_estrut'] == 'A'){
                    $anc_a = $i;
                  }
                  $i = $i - 1;
                }
              }
            }
            else{
              $anc_a = $id;
            }
          }
          $i = $id + 1;
          while($anc_p == 0) {
            $sql = "SELECT aplicacao_estrut FROM '" . $valor_lt . "' WHERE id = '" . $i . "'";
            $result1 = $db->query($sql);
            while($row1 = $result1->fetchArray(SQLITE3_ASSOC) ) {
              if ($row1['aplicacao_estrut'] == 'A'){
                $anc_p = $i - 1;
              }
              $i = $i + 1;
            }
          }
          $sql = "SELECT vao_frente FROM '" . $valor_lt . "' WHERE id BETWEEN '" . $anc_a . "' AND '" . $anc_p . "'";
          $result1 = $db->query($sql);
          while($row1 = $result1->fetchArray(SQLITE3_ASSOC) ) {
            $num_ar = $num_ar + pow($row1['vao_frente'], 3);
            $den_ar = $den_ar + $row1['vao_frente'];
          }
          $Ar = sqrt($num_ar / $den_ar);

          // Cálculo da traçao do cabo na condição inicial
          $a2 = $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / (24 * pow($tracao_eds, 2)) + $mod_elast * $secao * $coefic_dilat * ($temp_amb - $temp_eds) - $tracao_eds;
          $a0 = - $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / 24;
          $tracao_amb = $tracao_eds;
          $erro = 1.000;
          while ($erro > 0.001){
            $tracao_amb1 = $tracao_amb - ((pow($tracao_amb, 3) + $a2 * pow($tracao_amb, 2) + $a0) / (3 * pow($tracao_amb, 2) + 2 * $a2 * $tracao_amb));
            $erro = abs($tracao_amb1 - $tracao_amb);
            $tracao_amb = $tracao_amb1;
          }

          // Cálculo da tracção do cabo na condição de temperatura máxima
          $a2 = $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / (24 * pow($tracao_amb, 2)) + $mod_elast * $secao * $coefic_dilat * ($temp_max - $temp_amb) - $tracao_amb;
          $a0 = - $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / 24;
          $tracao_max = $tracao_amb;
          $erro = 1.000;
          while ($erro > 0.001){
            $tracao_max1 = $tracao_max - ((pow($tracao_max, 3) + $a2 * pow($tracao_max, 2) + $a0) / (3 * pow($tracao_max, 2) + 2 * $a2 * $tracao_max));
            $erro = abs($tracao_max1 - $tracao_max);
            $tracao_max = $tracao_max1;
          }

          // Cálculo da altura do cabo na condição de temperatura máxima
          $dh_max = ($tracao_max * (cosh($dist_cruza / $tracao_max) - 1) - $tracao_amb * (cosh($dist_cruza / $tracao_amb) - 1)) / $massa_linear;
          $altura_max = $altura_cabo - $dh_max;
          $diferenca_max = $altura_max - $dist_segur_max;

          // Cálculo da tracção do cabo na condição de temperatura de emergência
          $a2 = $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / (24 * pow($tracao_amb, 2)) + $mod_elast * $secao * $coefic_dilat * ($temp_emer - $temp_amb) - $tracao_amb;
          $a0 = - $mod_elast * $secao * pow($massa_linear, 2) * pow($Ar, 2) / 24;
          $tracao_emer = $tracao_max;
          $erro = 1.000;
          while ($erro > 0.001){
            $tracao_emer1 = $tracao_emer - ((pow($tracao_emer, 3) + $a2 * pow($tracao_emer, 2) + $a0) / (3 * pow($tracao_emer, 2) + 2 * $a2 * $tracao_emer));
            $erro = abs($tracao_emer1 - $tracao_emer);
            $tracao_emer = $tracao_emer1;
          }

          // Cálculo da altura do cabo na condição de temperatura de emergência
          if($tensao <= 242 && $cod_obs < 5) {
            $sql = 'SELECT emergencia FROM tipo_obstaculo WHERE id = "' . $cod_obs . '"';
            $result = $db->query($sql);
            while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
              $dist_segur_emer = $row['emergencia'];
            }
            $dh_emer = ($tracao_emer * (cosh($dist_cruza / $tracao_emer) - 1) - $tracao_amb * (cosh($dist_cruza / $tracao_amb) - 1)) / $massa_linear;
            $altura_emer = $altura_cabo - $dh_emer;
            $diferenca_emer = $altura_emer - $dist_segur_emer; //verificar valor de Lcad conforme 10.4.2 da NBR-5422, foi usado Lcad = 2
          }
          $db->close();
        }
       ?>
      <h2>Análise de travessia</h2>
      <form action="#bg" method="post" autocomplete="off">
        <ul class="formulario">
          <li class="entrada">
            <label for='lt'><p class="entrada">Sigla da LT</p></label>
            <select required id='lt' name='sigla_lt' onchange="getId(this.value)">
              <option value="">selecione aqui</option>
              <?php
                $db = new MyDB();
                $sql = 'SELECT sigla_lt, valor FROM lts';
                $result = $db->query($sql);
                while($row = $result->fetchArray(SQLITE3_ASSOC) ) {
                  echo "<option value='" . $row['valor'] ."'>" . $row['sigla_lt'] ."</option>";
                }
                $db->close();
               ?></select>
          </li>
          <li class="entrada">
            <label for="vao"><p class="entrada">Vão (estrutura a ré)</p></label>
            <select required id="vao" name="vao_lt"></select>
          </li>
          <li class="entrada">
            <label for="t_amb"><p class="entrada">Temperatura ambiente (°C)</p></label>
            <input required type="number" step="0.01" lang="en-150" min="-5" max="50" id="t_amb" name="temp_amb" value="<?php echo $temp_amb;?>" placeholder="digite aqui">
          </li>
          <li class="entrada">
            <label for="alt_cabo"><p class="entrada">Altura do cabo da LT (m)</p></label>
            <input required type="number" step="0.001" lang="en-150" min="0" id="alt_cabo" name="altura_cabo" value="<?php echo $altura_cabo;?>" placeholder="digite aqui">
          </li>
          <li class="entrada">
            <label for="obstaculo"><p class="entrada">Tipo do obstáculo</p></label>
            <select required id="obstaculo" name="tipo_obs" onchange="getObs(this.value)">
              <option value="">selecione aqui</option>
              <option value="1">Acesso por pedestres</option>
              <option value="2">Máquinas agricolas</option>
              <option value="3">Rodovias, ruas e avenidas</option>
              <option value="4">Ferrovia não eletrificada</option>
              <option value="5">Rede de distribuição</option>
              <option value="6">Linha de telecomunicações</option>
            </select>
          </li>
          <li class="entrada">
            <label for="alt_obs"><p class="entrada">Altura do obstáculo (m)</p></label>
            <input disabled type="number" step="0.001" lang="en-150" min="0" id="alt_obs" name="altura_obs" value="<?php echo $altura_obs;?>">
          </li>
          <li class="entrada">
            <label for="d_cruza"><p class="entrada">Distância de cruzamento (m)</p></label>
            <input required type="number" step="0.001" lang="en-150" min="0" id="d_cruza" name="dist_cruza" value="<?php echo $dist_cruza?>" placeholder="digite aqui">
          </li>
          <li class="entrada">
            <label for="c_vao"><p class="entrada">Comprimento do vão (m)</p></label>
            <input type="number" step="0.001" lang="en-150" min="0" id="c_vao" name="comp_vao" value="<?php echo $comp_vao?>" placeholder="digite aqui">
          </li>
          <li class="entrada">
            <input type="submit" name="subimit" value="Calcular">
          </li>
        </ul>
      </form>
    </div>

    <!-- Página de fundo -->
    <div id="bg"></div>

    <!-- Resultados -->
    <div class="box">
      <h3><a href="#" id="close">&#8592;</a>Análise de travessia</h3>
      <ul class="saida">
        <li class="saida"><h4>LT <?php echo $tensao;?>kV <?php echo $sigla_lt;?></h4></li>
        <li class="saida"><p class="saida">Vão analisado:<span class="saida"><?php echo $vao_lt;?></span></p></li>
        <li class="saida"><h5>Dados de projeto</h5></li>
        <li class="saida"><p class="saida">Temperatura EDS:<span class="saida"><?php echo $temp_eds;?> °C</span></p></li>
        <li class="saida"><p class="saida">Temperatura de projeto:<span class="saida"><?php echo $temp_max;?> °C</span></p></li>
        <li class="saida"><p class="saida">Temperatura de emergência:<span class="saida"><?php echo $temp_emer;?> °C</span></p></li>
        <li class="saida"><p class="saida">Comprimento do vão:<span class="saida"><?php echo $vao_frente;?> m</span></p></li>
        <li class="saida"><p class="saida">Vão regulador:<span class="saida"><?php echo round($Ar, 2);?> m</span></p></li>
        <li class="saida"><h5>Dados do cabo</h5></li>
        <li class="saida"><p class="saida">Cabo condutor:<span class="saida"><?php echo $cabo;?></span></p></li>
        <li class="saida"><p class="saida">Seção:<span class="saida"><?php echo $secao;?> mm²</span></p></li>
        <li class="saida"><p class="saida">Massa linear:<span class="saida"><?php echo $massa_linear;?> kg/m</span></p></li>
        <li class="saida"><p class="saida">Módulo de eslasticidade:<span class="saida"><?php echo $mod_elast;?> Mpa</span></p></li>
        <li class="saida"><p class="saida">Coeficiente de dilatação:<span class="saida"><?php echo $coefic_dilat;?> / °C</span></p></li>
        <li class="saida"><p class="saida">Carga de ruptura:<span class="saida"><?php echo $carga_ruptura;?> kgf</span></p></li>
        <li class="saida"><h5>Dados de entrada</h5></li>
        <li class="saida"><p class="saida">Temperatura ambiente:<span class="saida"><?php echo $temp_amb;?> °C</span></p></li>
        <li class="saida"><p class="saida">Altura do cabo:<span class="saida"><?php echo $altura_cabo;?> m</span></p></li>
        <li class="saida"><p class="saida">Distância de cruzamento:<span class="saida"><?php echo $dist_cruza?> m</span></p></li>
        <li class="saida"><p class="saida">Comprimento do vão:<span class="saida"><?php echo $comp_vao?> m</span></p></li>
        <?php
          if ($altura_obs != 0){
            echo "<li class='saida'><p class='saida'>Altura do obstáculo:<span class='saida'>";
            echo $altura_obs;
            echo " m</span></p></li>";
          }
         ?>
        <li class="saida"><p class="saida">Obstáculo:<span class="saida"><?php echo $tipo_obs;?></span></p></li>
        <li class="saida"><h5>Resultados - Temperatura máxima</h5></li>
        <li class="saida"><p class="saida">Distância de segurança:<span class="saida"><?php echo $dist_segur_max;?> m</span></p></li>
        <li class="saida"><p class="saida">Altura do cabo:<span class="saida"><?php echo round($altura_max, 2);?> m</span></p></li>
        <li class="saida"><p class="saida">Diferença:<span class="saida"><?php echo round($diferenca_max, 2);?> m</span> </p></li>
        <?php
          if($tensao <= 242 && $cod_obs < 5) {
            echo "<li class='saida'><h5>Resultados - Temperatura de emergência</h5></li>";
            echo "<li class='saida'><p class='saida'>Distância de segurança:<span class='saida'>"; echo $dist_segur_emer; echo" m</span></p></li>";
            echo "<li class='saida'><p class='saida'>Altura do cabo:<span class='saida'>"; echo round($altura_emer, 2); echo" m</span></p></li>";
            echo "<li class='saida'><p class='saida'>Diferença:<span class='saida'>"; echo round($diferenca_emer, 2); echo" m</span> </p></li>";
          }
         ?>
        <li class="saida"><h5></h5></li>
        <li class="saida"><input id="gerarpdf" type="button" value="Gerar PDF"></li>
      </ul>
    </div>

    <script src="./jquery-3.3.1.min.js"></script>
    <script>
      function getId(val){
        $.ajax({
          type: "POST",
          url: "getdata.php",
          data: "valor="+val,
          success: function(data){
            $("#vao").html(data);
          }
        });
      }
      function getObs(val){
        if (val >= 5) {
          $("#alt_obs").prop("disabled", false);
          $("#alt_obs").prop("required", true);
          $("#alt_obs").prop("placeholder", "digite aqui");
          $("#alt_obs").prop("value", "");
        }
        else {
          $("#alt_obs").prop("disabled", true);
          $("#alt_obs").prop("required", false);
          $("#alt_obs").prop("placeholder", "");
          if (val > 0){
            $("#alt_obs").prop("value", 0);
          }
          else {
            $("#alt_obs").prop("value", "");
          }
        }
      }
    </script>
  </body>
</html>
