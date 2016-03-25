<?php

/**
 * Foward Twitter timeline to Slack channel
 */

// ini_set('display_errors', 1);

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
require_once __DIR__ . '/../settings.inc';
require_once __DIR__ . '/../twitter-api-php/TwitterAPIExchange.php';


function slack($username, $text, $iconUrl = false) {

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

/**
 * The following page let you see what else you can retrieve via Twitter API:
 * https://dev.twitter.com/rest/public
 *
 * This script may make you think it is to make your Slack team as Twitter client,
 * and it is not just that. This script can be a starter kit of your project.
 *
 * Slack stores the chat history including what this script forwards to the channel,
 * and it also let you search something within the channel.
 *
 * That means Slack channel can be used as your private data storage for specific use.
 *
 * For example, you decided to collect the tweets including the word "facebook".
 * "https://twitter.com/search?q=xxx" seems to do the job according to
 * https://dev.twitter.com/rest/public/search. So you just start
 * getting the tweets having the word "facebook" with the API
 * and keep forwarding them to your Slack channel.
 *
 * Later the Slack channel gets full of tweets in regards to Facebook.
 * You can just search something from the channel, but if you use Slack API
 * like https://api.slack.com/methods/channels.history, you can even aggreate
 * all the messages and create a infographic about what word is mostly used about Facebook.
 *
 * Want to apply something only for specific messages being forwarded from this script?
 * Add the attachment data when sending the message (https://api.slack.com/docs/attachments),
 * and use "fields" to set the tag kind of data for your use so that when you use
 * https://api.slack.com/methods/channels.history you can filter out the messages not having
 * the specfic value, like someone may have said something in the channel and
 * you don't want to include the message. You can filter out them by checking if the message
 * contains "fields" with the specific value.
 *
 * And you know you can provide the slash command to aggregate the data by typing like "/makeig".
 * So you can aggregate the data when you want.
 *
 * Now, those are just idea. I am not sure if we can do that properly,
 * but I hope I have just made your eyes wide open about the possible use of Slack.
 *
 * It is kind of like how we use Evernote, but how we collect the data is automated
 * (Sure, we can do it with Evernote API, but this is about Slack so).
 *
 * And if you got into this, the following page is also useful
 * because you can avoid investigating the data structure for a request:
 * https://dev.twitter.com/rest/tools/console
 */
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
