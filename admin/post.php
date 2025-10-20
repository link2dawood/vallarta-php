<?php
require_once __DIR__ . '/facebook/autoload.php';

    $fb = new Facebook\Facebook([
         'app_id' => '920213225496176',
         'app_secret' => '1ab52d875a1b7f64c632527870f3b334',
         'default_graph_version' => 'v10.0', //update latest version here
        ]);


    $pageAccessToken ='EAANE7dZAsXnABAMLJi9NMTZCvSJuVkRhdfIh40B3V3rB0LiScZCC3kRgNZCB7ivprfKWFnD3LKRFhZBlHZAbfmOWXqoElqMNPhS7wDCHpLlsaDE6oZCBfUn4qMwogHqaJvVnLYd2blSEFNOxKqa5Kr7LAjy5NRR0oKGMrmU6wFmJ6ADm06MAmN7ZCtIJe4pvylZAxjIlJ6sda7Tw9wCECap2H';
$linkData = [
    'message' => 'hello 2'
];
    try {
        $response = $fb->post('/me/feed', $linkData, $pageAccessToken);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: '.$e->getMessage();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: '.$e->getMessage();
    }
    
    $graphNode = $response->getGraphNode();


?>