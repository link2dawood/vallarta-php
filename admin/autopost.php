<?php
/*
// connections
include '../settings/db.php';

//api stuff
require 'api/apis.php';

//twitter
require 'twitter/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth($twitter_api_key, $twitter_api_secret, $twitter_access_token, $twitter_access_token_secret);
$content = $connection->get("account/verify_credentials");

//tw
$status = 'Watch '.$r['title']. ' here: http://movies.delux.icu/movie_detail.php?id='.$r['id'];
$post_tweets = $connection->post("statuses/update", ["status" => $status]);
header("location:movie_manager.php?page=data");*/


include 'api/apis.php';
// load graph-sdk files
require_once __DIR__ . '/facebook/autoload.php';

// facebook credentials array
$creds = array(
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
    'default_graph_version' => 'v10.0',
    'persistent_data_handler' => 'session'
);

// create facebook object
$facebook = new Facebook\Facebook($creds);

// helper
$helper = $facebook->getRedirectLoginHelper();

// oauth object
$oAuth2Client = $facebook->getOAuth2Client();
//echo $helper->getAccessToken();
if (isset($_GET['code'])) { // get access token
    try {
        $accessToken = $helper->getAccessToken();
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    if (!isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }
    //echo $accessToken;
    $new_accessToken = str_replace(' ', '', $accessToken);
    $endpointFormat = ENDPOINT_BASE . '{page-id}?fields=access_token&access_token={access-token}';
    $endPoint = ENDPOINT_BASE . $pageId;

    // endpoint params
    $perams = array(
        'fields' => 'access_token',
        'access_token' => $new_accessToken
    );

    // add params to endpoint
    $endPoint .= '?' . http_build_query($perams);

    // setup curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endPoint);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // make call and get response
    $response = curl_exec($ch);
    curl_close($ch);
    $responseArray = json_decode($response, true);
    echo $responseArray['access_token'];

} else { // display login url
    $permissions = [
        'public_profile',
        'instagram_basic',
        'pages_show_list',
        'instagram_manage_insights',
        'instagram_manage_comments',
        'ads_management',
        'business_management',
        'instagram_content_publish',
        'page_events',
        'pages_manage_posts',
        'pages_read_engagement'
    ];
    $loginUrl = $helper->getLoginUrl($redirect_uri, $permissions);

    echo '<a href="' . $loginUrl . '">
            Login With Facebook
        </a>';
}



