<?php
    require_once 'connection/conn.php';

    if(isXmlHttpRequest()) {
        $json = array();
        $json["empty"] = FALSE;
        
        if(isset($_POST["produto_marca"])) {
            $json['first'] = FALSE;
            if(!isset($_SESSION['query_marca'])) {
                $json['first'] = TRUE;
                if(isset($_SESSION['query_preco'])) {
                    $_SESSION['query_marca'] = "AND m.marca_nome='{$_POST["produto_marca"]}' ";
                    $_SESSION['query_proc'] = str_replace($_SESSION['query_preco'], $_SESSION['query_marca'] . $_SESSION['query_preco'], $_SESSION['query_proc']);
                } else {
                    $_SESSION['query_proc'] .= "AND m.marca_nome='{$_POST["produto_marca"]}' ";
                    $_SESSION['query_marca'] = "AND m.marca_nome='{$_POST["produto_marca"]}' ";
                }
            } else {
                $_SESSION['query_proc'] = str_replace($_SESSION['query_marca'],"AND m.marca_nome='{$_POST["produto_marca"]}' ", $_SESSION['query_proc']);
                $_SESSION['query_marca'] = "AND m.marca_nome='{$_POST["produto_marca"]}' ";
            }
            
            $sel = $conn->prepare($_SESSION['query_proc']);
            $sel->execute();
            if($sel->rowCount() > 0) {
                $result = $sel->fetchAll();
                foreach($result as $v) {
                    if($v['produto_qtd'] > 0) {
                        $v['empty'] = false;
                    } else {
                        $v['empty'] = true;
                    }
                    if($v['produto_desconto_porcent'] <> "") {
                        $v["produto_desconto"] = $v["produto_preco"]*($v["produto_desconto_porcent"]/100);
                        $v["produto_desconto"] = number_format($v["produto_desconto"], 2, '.', '');
                        $v["produto_desconto"] = $v["produto_preco"]-$v["produto_desconto"];
                        $v["produto_desconto"] = number_format($v["produto_desconto"], 2, ',', '.');
                    }
                    
                    $v["produto_preco"] = number_format($v["produto_preco"], 2, ',', '.');
                    if(isset($_SESSION['carrinho'][$v['produto_id']])) {
                        $v["carrinho"] = $_SESSION['carrinho'][$v['produto_id']];
                    } else {
                        $v["carrinho"] = 0;
                    }
                    $json['produtos'][] = $v;
                }
            } else {
                $json['empty'] = true;
            }

            $json['query'] = $_SESSION['query_proc'];
        } else {
            $_SESSION['query_proc'] = str_replace($_SESSION['query_marca'], "", $_SESSION['query_proc']);
            unset($_SESSION['query_marca']);
            
            $sel = $conn->prepare($_SESSION['query_proc']);
            $sel->execute();
            if($sel->rowCount() > 0) {
                $result = $sel->fetchAll();
                foreach($result as $v) {
                    if($v['produto_qtd'] > 0) {
                        $v['empty'] = false;
                    } else {
                        $v['empty'] = true;
                    }
                    if($v['produto_desconto_porcent'] <> "") {
                        $v["produto_desconto"] = $v["produto_preco"]*($v["produto_desconto_porcent"]/100);
                        $v["produto_desconto"] = number_format($v["produto_desconto"], 2, '.', '');
                        $v["produto_desconto"] = $v["produto_preco"]-$v["produto_desconto"];
                        $v["produto_desconto"] = number_format($v["produto_desconto"], 2, ',', '.');
                    }
                    
                    $v["produto_preco"] = number_format($v["produto_preco"], 2, ',', '.');
                    if(isset($_SESSION['carrinho'][$v['produto_id']])) {
                        $v["carrinho"] = $_SESSION['carrinho'][$v['produto_id']];
                    } else {
                        $v["carrinho"] = 0;
                    }
                    $json['produtos'][] = $v;
                }
            } else {
                $json['empty'] = true;
            }

            $json['query'] = $_SESSION['query_proc'];
        }
        echo json_encode($json);
    } else {
        header('Location: ../');
    }
?>