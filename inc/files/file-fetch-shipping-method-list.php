<?php 
function shipgate_fetch_shipping_method_list() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://app.shipgate.io/api/v1/rates?weightGram=100&packagingType=box&lengthCm=1.0&heightCm=1.0&widthCm=1.0&skuCdList=TEST_01&skuCdList=TEST_02&toCountryCode=US&toState=Ohio&toCity=Columbus&toStreet=817%20Bates%20Brothers%20Road&toZipCd=43085',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json;charset=UTF-8',
        'Accept: application/json',
        'Authorization: a0edd49c95e9399f45a9036d32b7171915f'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}



