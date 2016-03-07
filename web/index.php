<?php

/**
 * Foward Twitter timeline to Slack channel
 */

ini_set('display_errors', 1);

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
require_once __DIR__ . '/../settings.inc';
require_once __DIR__ . '/../twitter-api-php/TwitterAPIExchange.php';


function slack($username, $text, $iconUrl) {

    global $slackUrl;

    $ch = curl_init($slackUrl);

    $payload = [
        'text' => $text,
        'username' => $username,
        'channel' => 'twitter_timeline',
        'link_names' => true,
    ];
    if ($iconUrl) $payload['icon_url'] = $iconUrl;

    $jsonDataEncoded = json_encode($payload);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($ch);

}
function time_elapsed_string($ptime) {

    $etime = time() - $ptime;

    if ($etime < 1) {
        // return '0 seconds';
        return 'now';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
         );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
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

$url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
$getfield = '?include_entities=false&count=200';
// $getfield = '?include_entities=false&count=10';
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
        /*
        var_dump($d->id_str);
        var_dump($d->created_at);
        var_dump($d->user->utc_offset);
        var_dump($d->user->name);
        var_dump($d->user->screen_name);
        var_dump($d->user->profile_image_url);
        var_dump(preg_replace(array("/\n/", "/\s+/"), array(' ', ' '), $d->text));
        exit;
        */
        // $timestamp = strtotime($d->created_at) + (60 * 60 * 10);
        // $localDate = date('d/m/Y H:i:s', $timestamp);
        // $relativeDate = time_elapsed_string($timestamp);
        $id = $d->id_str;
        $tweet = preg_replace(array("/\n/", "/\s+/"), array(' ', ' '), $d->text);
        $screenName = $d->user->screen_name;
        // $text = "*<https://twitter.com/{$screenName}|{$screenName}>* - {$relativeDate}\n{$tweet}";
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
