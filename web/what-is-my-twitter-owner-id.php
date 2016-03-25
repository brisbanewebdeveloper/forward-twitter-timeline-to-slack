<?php

/**
 * Foward Twitter timeline to Slack channel
 */

// ini_set('display_errors', 1);

require_once __DIR__ . '/../settings.inc';
require_once __DIR__ . '/../twitter-api-php/TwitterAPIExchange.php';


if (php_sapi_name() !== 'cli') exit;

if (empty($username)) {
    echo "\$username is empty in settings.inc.\n";
    exit;
}

$url = 'https://api.twitter.com/1.1/users/show.json';
$getfield = "?screen_name={$username}";

$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$data = json_decode(
    $twitter
        ->setGetfield($getfield)
        ->buildOauth($url, $requestMethod)
        ->performRequest()
);

echo "Username: {$username}\n";
echo "Owner Id: {$data->id_str}\n";
