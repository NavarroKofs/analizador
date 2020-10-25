<?php
//chai traders and not syrup and cadena(Beverages) or patron(drie) campos(products.product_name, products.category)
    if(isset($_GET['consulta'])) {
        $diccionario = array("and", "or", "not", "cadena(", "patron(", "campos(");
        $cadena = strtolower ($_GET['consulta']);
        $arrayDividido = explode(" ", $cadena);
        $tamaño = count($arrayDividido);
        $sentenciaSQL = "";
        $aux = 0; //Sirve para saber si ya comenzó la cadena o no
        $primero = "";
        $segundo = "";
        $auxNeg = 0;
        $auxCadena = "";
        $auxPatron = "";
        $tabla = "products";
        $campos = array("products.product_name", "products.category", "products.quantity_per_unit");
        $tableCount = count($campos);

        for ($j=0; $j < $tamaño; $j++) { 
            if(strncasecmp($arrayDividido[$j], $diccionario[5], 7) == 0){
                $palabra = "";
                for ($k=$j; $k < $tamaño; $k++) {
                    $palabra = $palabra . $arrayDividido[$k];
                    if(endsWith($arrayDividido[$k], ")")){
                        $arrayDividido[$k] = "";
                        break;
                    }
                    $arrayDividido[$k] = "";
                }
                $palabra = trim($palabra);
                $parte1=explode("campos(", $palabra);
                $parte2=explode(')', $parte1[1]);
                $separador = explode(",", $parte2[0]);
                $tableCount = count($separador);
                $newCampos = array();
                for ($l=0; $l < $tableCount; $l++) { 
                    array_push($newCampos, $separador[$l]);
                }
                $campos = $newCampos;
                $otroDivisor = explode(".", $campos[0]);
                $tabla = $otroDivisor[0];
            break;
            }
        }
        $arrayDividido = removeEmptyElements($arrayDividido);
        $tamaño = count($arrayDividido);
    
        for($i=0; $i<$tamaño; $i++){
            if(in_array($arrayDividido[$i], $diccionario, false)) {
                //AND
                if(($arrayDividido[$i] == $diccionario[0])){
                    if(($arrayDividido[$i+1] == $diccionario[2])){
                        $segundo = $arrayDividido[$i+2];
                        $auxNeg = 1;
                        $i++;
                    } else {
                        if(strncasecmp($arrayDividido[$i+1], $diccionario[3], 7) == 0){
                            $palabra = "";
                            for ($k=$i+1; $k < $tamaño; $k++) { 
                                $palabra = $palabra . " " . $arrayDividido[$k];
                                if(endsWith($arrayDividido[$k], ")")){
                                    break;
                                }
                            }
                            $parte1=explode('cadena(',$palabra);
                            $parte2=explode(')', $parte1[1]);
                            $segundo= $parte2[0];
                            $separador = explode(" ", $segundo);
                            $tamañoArreglo = count($separador);
                            $i = $i + ($tamañoArreglo - 1);
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p]='$segundo') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p]='$segundo')";
                                }
                            }
                            $auxCadena = "($auxCadena)";
                        } else {
                            if(strncasecmp($arrayDividido[$i+1], $diccionario[4], 7) == 0){
                                $palabra = "";
                                for ($k=$i+1; $k < $tamaño; $k++) { 
                                    $palabra = $palabra . " " . $arrayDividido[$k];
                                    if(endsWith($arrayDividido[$k], ")")){
                                        break;
                                    }
                                }
                                $parte1=explode('patron(',$palabra);
                                $parte2=explode(')', $parte1[1]);
                                $segundo= $parte2[0];
                                $separador = explode(" ", $segundo);
                                $tamañoArreglo = count($separador);
                                $i = $i + ($tamañoArreglo - 1);
                                $auxCadena = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%')";
                                    }
                                }
                                $auxPatron = "($auxCadena)";
                            } else {
                                $segundo = $arrayDividido[$i+1];
                            }
                        }
                    }
                    if($aux == 0) { //No hay cadena previa
                        if($auxNeg == 0) { //Bandera bajada (es positivo)
                            if($auxCadena != "") { //Hay una sentencia cadena o patron? en este caso si
                                $segundo = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $segundo = $segundo . "($campos[$p] LIKE '%$primero%') or ";
                                    } else {
                                        $segundo = $segundo . "($campos[$p] LIKE '%$primero%')";
                                    }
                                }
                                $sentenciaSQL = "(" . $auxCadena . " and ($segundo)" . ")";
                            } else {
                                $auxCadena = "(";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%')) and (";
                                    }
                                }
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%'))";
                                    }
                                }
                                $sentenciaSQL = "($auxCadena)";
                                $auxCadena = "";
                            }
                        } else {
                            $auxCadena = "(";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%')) and NOT (";
                                }
                            }
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%'))";
                                }
                            }
                            $sentenciaSQL = "($auxCadena)";
                            $auxCadena = "";
                            $auxNeg = 0;
                        }
                        $aux = 1;
                    } else  {
                        if($auxNeg == 0) {
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%')";
                                }
                            }
                            $sentenciaSQL = "($sentenciaSQL and ($auxCadena))";
                            $auxCadena = "";
                            $aux = 1;
                        } else {
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%')";
                                }
                            }
                            $sentenciaSQL = "(" . $sentenciaSQL . " and NOT ($auxCadena)" . ")";
                            $auxCadena = "";
                            $auxNeg = 0;
                        }
                    }
                    $i++;
                }
                //OR
                if(($arrayDividido[$i] == $diccionario[1])){
                    if(($arrayDividido[$i+1] == $diccionario[2])){
                        $segundo = $arrayDividido[$i+2];
                        $auxNeg = 1;
                        $i++;
                    } else {
                        if(strncasecmp($arrayDividido[$i+1], $diccionario[3], 7) == 0){
                            $palabra = "";
                            for ($k=$i+1; $k < $tamaño; $k++) { 
                                $palabra = $palabra . " " . $arrayDividido[$k];
                                if(endsWith($arrayDividido[$k], ")")) {
                                    break;
                                }
                            }
                            $parte1=explode('cadena(',$palabra);
                            $parte2=explode(')', $parte1[1]);
                            $segundo= $parte2[0];
                            $separador = explode(" ", $segundo);
                            $tamañoArreglo = count($separador);
                            $i = $i + ($tamañoArreglo - 1);
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p]='$segundo') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p]='$segundo')";
                                }
                            }
                            $auxCadena = "($auxCadena)";
                        } else {
                            if(strncasecmp($arrayDividido[$i+1], $diccionario[4], 7) == 0){
                                $palabra = "";
                                for ($k=$i+1; $k < $tamaño; $k++) { 
                                    $palabra = $palabra . " " . $arrayDividido[$k];
                                    if(endsWith($arrayDividido[$k], ")")){
                                        break;
                                    }
                                }
                                $parte1=explode('patron(',$palabra);
                                $parte2=explode(')', $parte1[1]);
                                $segundo= $parte2[0];
                                $separador = explode(" ", $segundo);
                                $tamañoArreglo = count($separador);
                                $i = $i + ($tamañoArreglo - 1);
                                $auxCadena = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%')";
                                    }
                                }
                                $auxPatron = "($auxCadena)";
                            } else {
                                $segundo = $arrayDividido[$i+1];
                            }
                        }
                    }
                    if($aux == 0) {
                        if($auxNeg == 0) {
                            if($auxCadena != "") {
                                $segundo = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $segundo = $segundo . "($campos[$p] LIKE '%$primero%') or ";
                                    } else {
                                        $segundo = $segundo . "($campos[$p] LIKE '%$primero%')";
                                    }
                                }
                                $sentenciaSQL = "(" . $auxCadena . " or ($segundo)" . ")";
                            } else {
                                $auxCadena = "(";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%')) or (";
                                    }
                                }
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                    } else {
                                        $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%'))";
                                    }
                                }
                                $sentenciaSQL = "($auxCadena)";
                                $auxCadena = "";
                            }
                        } else {
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$primero%')";
                                }
                            }
                            $auxCadena = "($auxCadena)" . " or NOT (";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%'))";
                                }
                            }
                            $sentenciaSQL = "($auxCadena)";
                            $auxCadena = "";
                            $auxNeg = 0;
                        }
                        $aux = 1;
                    } else {
                        if($auxNeg == 0) {
                            $auxCadena = "(";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%'))";
                                }
                            }
                            $sentenciaSQL = "(" . $sentenciaSQL . " or " . $auxCadena . ")";
                            $auxCadena = "";
                            $aux = 1;
                        } else {
                            $auxCadena = "";
                            for ($p=0; $p < $tableCount; $p++) {
                                if($p != ($tableCount-1)){
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%') or ";
                                } else {
                                    $auxCadena = $auxCadena . "($campos[$p] LIKE '%$segundo%')";
                                }
                            }
                            $sentenciaSQL = "(" . $sentenciaSQL . " or NOT ($auxCadena)" . ")";
                            $auxCadena = "";
                            $auxNeg = 0;
                        }
                    }
                    $i++;
                }
            } else {
                if(strncasecmp($arrayDividido[$i], $diccionario[3], 7) == 0){
                    $palabra = "";
                    for ($k=$i; $k < $tamaño; $k++) { 
                        $palabra = $palabra . " " . $arrayDividido[$k];
                        if(endsWith($arrayDividido[$k], ")")){
                            break;
                        }
                    }
                    $parte1=explode('cadena(',$palabra);
                    $parte2=explode(')', $parte1[1]);
                    $primero= $parte2[0];
                    $separador = explode(" ", $primero);
                    $tamañoArreglo = count($separador);
                    $i = $i + ($tamañoArreglo - 1);
                    $sentenciaSQL = "";
                    for ($p=0; $p < $tableCount; $p++) {
                        if($p != ($tableCount-1)){
                            $sentenciaSQL = $sentenciaSQL . "($campos[$p]='$primero') or ";
                        } else {
                            $sentenciaSQL = $sentenciaSQL . "($campos[$p]='$primero')";
                        }
                    }
                    $sentenciaSQL = "($sentenciaSQL)";
                    $aux = 1;
                } else {
                    if(strncasecmp($arrayDividido[$i], $diccionario[4], 7) == 0){
                        $palabra = "";
                        for ($k=$i; $k < $tamaño; $k++) { 
                            $palabra = $palabra . " " . $arrayDividido[$k];
                            if(endsWith($arrayDividido[$k], ")")){
                                break;
                            }
                        }
                        $parte1=explode('patron(',$palabra);
                        $parte2=explode(')', $parte1[1]);
                        $primero= $parte2[0];
                        $separador = explode(" ", $primero);
                        $tamañoArreglo = count($separador);
                        $i = $i + ($tamañoArreglo - 1);
                        $sentenciaSQL = "";
                        for ($p=0; $p < $tableCount; $p++) {
                            if($p != ($tableCount-1)){
                                $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%') or ";
                            } else {
                                $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%')";
                            }
                        }
                        $sentenciaSQL = "($sentenciaSQL)";
                        $aux = 1;
                    } else {
                        if($primero == ""){
                            $primero = $arrayDividido[$i];
                        } else {
                            $segundo = $arrayDividido[$i];
                            if($aux == 0) {
                                $sentenciaSQL = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $sentenciaSQL = $sentenciaSQL . "(($campos[$p] LIKE '%$primero%') or ($campos[$p] LIKE '%$segundo%')) or ";
                                    } else {
                                        $sentenciaSQL = $sentenciaSQL . "(($campos[$p] LIKE '%$primero%') or ($campos[$p] LIKE '%$segundo%'))";
                                    }
                                }
                                $sentenciaSQL = "($sentenciaSQL)";
                                $aux = 1;
                            } else {
                                $primero = "";
                                for ($p=0; $p < $tableCount; $p++) {
                                    if($p != ($tableCount-1)){
                                        $primero = $primero . "($campos[$p] LIKE '%$segundo%') or ";
                                    } else {
                                        $primero = $primero . "($campos[$p] LIKE '%$segundo%')";
                                    }
                                }
                                $sentenciaSQL = "(" . $sentenciaSQL . " or ($primero)" . ")";
                                $aux = 1;
                            }
                        }
                    }
                }
            }
        }
        if($aux == 0) {
            for ($p=0; $p < $tableCount; $p++) {
                if($p != ($tableCount-1)){
                    $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%') or ";
                } else {
                    $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%')";
                }
            }
        }
        $query = "select * from $tabla where " . $sentenciaSQL . ";";

    } else{
        echo 'error';
    }

    
    if($aux == 0) {
        for ($p=0; $p < $tableCount; $p++) {
            if($p != ($tableCount-1)){
                $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%') or ";
            } else {
                $sentenciaSQL = $sentenciaSQL . "($campos[$p] LIKE '%$primero%')";
            }
        }
    }
    $query = "select * from $tabla where " . $sentenciaSQL . ";";

    $servername = "localhost";
    $username = "root";
    $password = "admin";
    $dbname = "northwind";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = $query;
    $result = $conn->query($sql);

    $arrayResultados = array();

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            array_push($arrayResultados, $row);
            //echo $row;
        }
        echo json_encode($arrayResultados);
    } else {
        echo json_encode(array());
    }
    $conn->close();

    function endsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    function recorrerArreglo($arrayDividido, $i, $divisor){
        $palabra = "";
        for ($k=$i; $k < $tamaño; $k++) { 
            $palabra = $palabra . " " . $arrayDividido[$k];
            if(endsWith($arrayDividido[$k], ")")){
                break;
            }
        }
        $parte1=explode($divisor,$palabra);
        $parte2=explode(')', $parte1[1]);
    }

    function removeEmptyElements(&$element){
        if (is_array($element)) {
            if ($key = key($element)) {
                $element[$key] = array_filter($element);
            }

            if (count($element) != count($element, COUNT_RECURSIVE)) {
                $element = array_filter(current($element), __FUNCTION__);
            }

            $element = array_filter($element);

            return $element;
        } else {
            return empty($element) ? false : $element;
        }
    }
?>