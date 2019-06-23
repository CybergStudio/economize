<?php
    require_once 'configuration.php';
    header("Content-Type: application/json");

    function getProductsByIds($ids) {
        global $conn;
        $sel = $conn->prepare("SELECT p.produto_id, p.produto_nome, d.produto_qtd, p.produto_img, p.produto_tamanho, d.produto_preco, d.produto_desconto_porcent, m.marca_nome, pr.promo_desconto FROM produto AS p JOIN dados_armazem AS d ON p.produto_id=d.produto_id JOIN marca_prod AS m ON p.produto_marca=m.marca_id LEFT JOIN dados_promocao AS dp ON p.produto_id=dp.produto_id LEFT JOIN promocao_temp AS pr ON dp.promo_id=pr.promo_id WHERE d.armazem_id={$_SESSION['arm_id']} AND p.produto_id IN (".$ids.")");
        $sel->execute();
        if($sel->rowCount() > 0) {
            while($v = $sel->fetch( PDO::FETCH_ASSOC )) {
                $dados[] = $v;
            }
        }
        return $dados;
    }
    
    function getContentCart() {
        global $conn;
        $results = array();
        
        if(isset($_SESSION['carrinho'])) {
            $cart = $_SESSION['carrinho'];
            $products =  getProductsByIds(implode(',', array_keys($cart)));
    
            foreach($products as $k => $product) {
                if($product['produto_desconto_porcent'] != "") {
                    $product["produto_desconto"] = $product["produto_preco"]*($product["produto_desconto_porcent"]/100);
                    $product["produto_desconto"] = number_format($product["produto_desconto"], 2, '.', '');
                    $product["produto_desconto"] = $product["produto_preco"]-$product["produto_desconto"];
                    $results[$k] = $product;
                } elseif($product['promo_desconto']) {
                    $product["produto_desconto"] = $product["produto_preco"]*($product["promo_desconto"]/100);
                    $product["produto_desconto"] = number_format($product["produto_desconto"], 2, '.', '');
                    $product["produto_desconto"] = $product["produto_preco"]-$product["produto_desconto"];
                    $results[$k] = $product;
                } else {
                    $results[$k] = $product;
                }
            }
        }
        
        return $results;
    }

    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

    // TRANFORMANDO DADOS EM INTEIRO
        $dados['inputSenderCPF'] = str_replace(".", "", $dados['inputSenderCPF']);
        $dados['inputSenderCPF'] = str_replace("-", "", $dados['inputSenderCPF']);

        $dados['creditCardHolderCPF'] = str_replace(".", "", $dados['creditCardHolderCPF']);
        $dados['creditCardHolderCPF'] = str_replace("-", "", $dados['creditCardHolderCPF']);
        
        $dados['shippingAddressPostalCode'] = str_replace("-", "", $dados['shippingAddressPostalCode']);
        
        $dados['billingAddressPostalCode'] = str_replace("-", "", $dados['billingAddressPostalCode']);
        
        $dados['billingAddressOtherPostalCode'] = str_replace("-", "", $dados['billingAddressOtherPostalCode']);
    // ---------- //

    $dadosArray["email"] = EMAIL_PAGSEGURO;
    $dadosArray["token"] = TOKEN_PAGSEGURO;

    $dadosArray["paymentMode"] = "default";
    $dadosArray["paymentMethod"] = $dados['paymentMethod'];

    if($dadosArray["paymentMethod"] == "eft") {
        $dadosArray["bankName"] = $dados['bankName'];
    }

    $dadosArray["receiverEmail"] = $dados['receiverEmail'];
    $dadosArray["currency"] = $dados['currency'];
    
    $dadosArray["extraAmount"] = $dados['extraAmount'];

    $resultsCarts = getContentCart();
    foreach($resultsCarts as $k => $v) {
        $c = $k + 1;
        
        $dadosArray["itemId{$c}"] = $v['produto_id'];
        $dadosArray["itemDescription{$c}"] = $v['produto_nome'];
        $dadosArray["itemAmount{$c}"] = isset($v['produto_desconto']) ? number_format($v['produto_desconto'], 2, '.', '') : number_format($v['produto_preco'], 2, '.', '');
        $dadosArray["itemQuantity{$c}"] = $_SESSION['carrinho'][$v['produto_id']];
    }

    $dadosArray["notificationURL"] = $dados['notificationURL'];
    $dadosArray["reference"] = $dados['reference'];
    
    $dadosArray["senderName"] = $dados['inputSenderName'];
    $dadosArray["senderCPF"] = $dados['inputSenderCPF'];
    $dadosArray["senderAreaCode"] = $dados['inputSenderDDD'];
    $dadosArray["senderPhone"] = $dados['inputSenderNum'];
    $dadosArray["senderEmail"] = $dados['inputSenderEmail'];
    $dadosArray["senderHash"] = $dados['senderHash'];

    $dadosArray["shippingAddressRequired"] = $dados['shippingAddressRequired'];
    $dadosArray["shippingAddressStreet"] = $dados['shippingAddressStreet'];
    $dadosArray["shippingAddressNumber"] = $dados['shippingAddressNumber'];
    $dadosArray["shippingAddressComplement"] = $dados['shippingAddressComplement'];
    $dadosArray["shippingAddressDistrict"] = $dados['shippingAddressDistrict'];
    $dadosArray["shippingAddressPostalCode"] = $dados['shippingAddressPostalCode'];
    $dadosArray["shippingAddressCity"] = $dados['shippingAddressCity'];
    $dadosArray["shippingAddressState"] = $dados['shippingAddressState'];
    $dadosArray["shippingAddressCountry"] = $dados['shippingAddressCountry'];

    $dadosArray["shippingType"] = $dados['shippingType'];
    $dadosArray["shippingCost"] = $dados['shippingCost'];

    if($dadosArray['paymentMethod'] == "creditCard") {
        $dadosArray["creditCardToken"] = $dados['inputTokenCard'];

        $dadosArray["installmentQuantity"] = $dados['selQtdParc'];
        $dadosArray["installmentValue"] = $dados['inputParcValue'];
        $dadosArray["noInterestInstallmentQuantity"] = 2;

        $dadosArray["creditCardHolderName"] = $dados['creditCardHolderName'];
        $dadosArray["creditCardHolderCPF"] = $dados['creditCardHolderCPF'];
        $dadosArray["creditCardHolderBirthDate"] = $dados['creditCardHolderBirthDate'];
        $dadosArray["creditCardHolderAreaCode"] = $dados['creditCardHolderAreaCode'];
        $dadosArray["creditCardHolderPhone"] = $dados['creditCardHolderPhone'];

        if($dados['billingAddress'] == 1) {
            $dadosArray["billingAddressStreet"] = $dados['billingAddressStreet'];
            $dadosArray["billingAddressNumber"] = $dados['billingAddressNumber'];
            $dadosArray["billingAddressComplement"] = $dados['billingAddressComplement'];
            $dadosArray["billingAddressDistrict"] = $dados['billingAddressDistrict'];
            $dadosArray["billingAddressPostalCode"] = $dados['billingAddressPostalCode'];
            $dadosArray["billingAddressCity"] = $dados['billingAddressCity'];
            $dadosArray["billingAddressState"] = $dados['billingAddressState'];
            $dadosArray["billingAddressCountry"] = $dados['billingAddressCountry'];
        } else {
            $dadosArray["billingAddressStreet"] = $dados['billingAddressOtherStreet'];
            $dadosArray["billingAddressNumber"] = $dados['billingAddressOtherNumber'];
            $dadosArray["billingAddressComplement"] = $dados['billingAddressOtherComplement'];
            $dadosArray["billingAddressDistrict"] = $dados['billingAddressOtherDistrict'];
            $dadosArray["billingAddressPostalCode"] = $dados['billingAddressOtherPostalCode'];
            $dadosArray["billingAddressCity"] = $dados['billingAddressOtherCity'];
            $dadosArray["billingAddressState"] = $dados['billingAddressOtherState'];
            $dadosArray["billingAddressCountry"] = $dados['billingAddressOtherCountry'];
        }
    }

    $http_query = http_build_query($dadosArray);
    $url = URL_PAGSEGURO . "transactions";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $http_query);
    $answer = curl_exec($curl);

    curl_close($curl);
    $xml = simplexml_load_string($answer);

    $json = ['dados' => $xml, "dadosArray" => $dadosArray];

    echo json_encode($json);
?>