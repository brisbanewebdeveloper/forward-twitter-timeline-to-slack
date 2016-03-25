<?php

/**
 * Foward Twitter List to Slack channel named twitter_$twitter_list
 * $twitter_list should be defined in settings.inc
 */

// ini_set('display_errors', 1);

require_once __DIR__ . '/../../settings.inc';
require_once __DIR__ . '/../../twitter-api-php/TwitterAPIExchange.php';


function slack($username, $text, $iconUrl = false) {

    global $slackUrl;
    global $twitter_list;


    $ch = curl_init($slackUrl);

    $payload = [
        'text' => $text,
        'username' => $username,
        'channel' => 'twitter_' . $twitter_list,
        'link_names' => true,
    ];
    if ($iconUrl) $payload['icon_url'] = $iconUrl;

    $jsonDataEncoded = json_encode($payload);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($ch);

}




if (php_sapi_name() !== 'cli') {

    $valid = true;

    if (empty($secret)) {
        $valid = false;
    } else {
        if ( ! isset($_GET[$secret])) {
            $valid = false;
        }
    };
    if ( ! $valid) {
        header('HTTP/1.0 403 Not Found');
        exit;
    }
}

$logFile = __DIR__ . '/since_id.txt';
if (file_exists($logFile)) {
    $since_id = file_get_contents($logFile);
} else {
    $since_id = null;
}

$url = 'https://api.twitter.com/1.1/lists/statuses.json';
$getfield = "?slug={$twitter_list}&owner_id={$owner_id}";
if ( ! empty($since_id)) $getfield .= '&since_id=' . $since_id;

$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$data = json_decode(
    $twitter
        ->setGetfield($getfield)
        ->buildOauth($url, $requestMethod)
        ->performRequest()
);

// var_dump($data);
if (empty($data->errors)) {

    $id = null;
    $data = array_reverse($data);

    foreach ($data as $index => $d) {
        /*
        var_dump($d);
        exit;
        */
        $id = $d->id_str;
        $tweet = preg_replace(array("/\n/", "/\s+/"), array(' ', ' '), $d->text);
        $screenName = $d->user->screen_name;
        $text = "<https://twitter.com/{$screenName}|[{$screenName}]> <https://twitter.com/{$screenName}/status/{$id}|[URL]> {$tweet}";
        // var_dump($text);
        slack($d->user->name, $text, $d->user->profile_image_url);
    }

    if ( ! is_null($id)) $since_id = $id;
    // var_dump($since_id);
    // var_dump($id);
    file_put_contents($logFile, $since_id);

} else {
    $text = "*Error*\n";
    foreach ($data->errors as $e) {
        $text .= "{$e->message} ({$e->code})\n";
    }
    slack('Twitter Timeline', $text);
}
