## Summary

- This PHP scripts forward Twitter Timeline to a private Slack channel `twitter_timeline`.
- This PHP scripts can also forward Twitter List to a private Slack channel `twitter_$twitter_list ` (`$twitter_list` is to be defined in `settings.inc`), but the setup is not documented as it should be easy.
- If you were interested in this script, you check out "web/index.php".
- If you were interested in what this script can become, you can read [this part](https://github.com/hironozu/forward-twitter-timeline-to-slack/blob/master/web/index.php#L90) in above file.

## Requirements

- Server with Cron and PHP

## Installation

### Step 1 with Git

```shell
mkdir forward-twitter-timeline-to-slack
cd forward-twitter-timeline-to-slack
git clone git@github.com:hironozu/forward-twitter-timeline-to-slack.git .
cd twitter-api-php
git clone https://github.com/J7mbo/twitter-api-php.git .
cd ..
```

### Step 1 without Git

- Download the Zip file and extract it.
- Go to https://github.com/J7mbo/twitter-api-php, download the Zip file and extract it to twitter-api-php folder

### Step 2

- Create a private channel `twitter_timeline` for Slack team.

- Add incoming webhook for above channel if you have not created.

  + Search "Set up an incoming webhook" at https://api.slack.com/custom-integrations and click the button.
  + Select #general or whatever and click "Add Incoming WebHooks integration" button.
  + Search "Webhook URL".
  + Keep opening the page.

- Create your Twitter App at https://dev.twitter.com/apps/

  + Login with your Twitter account.
  + Click "Create New App", fill the form and click "Create your Twitter application".
  + Select your new app.
  + Select "Keys and Access Tokens" tab.
  + Create Access Token if you did not see (I had to do this).
  + Keep opening the page.

### Step 3

- Rename/Copy the file "settings.inc.example" in the files at Step 1 to "settings.inc".

- Set the followings in the file "settings.inc" with the information at Step 2:

  + $slackUrl

    * Webhook URL

  + $settings

    * Consumer Key
    * Consumer Secret
    * Access Token
    * Access Token Secret

- Upload the following three files to your server  
  (It may not need to upload them to somewhere accesible for your browser like `public_html`; I have not tried that).

  + settings.inc
  + twitter-api-php/TwitterAPIExchange.php
  + web/index.php

- Create empty file "web/since_id.txt".

  + Set the permission to 664

### Step 4

- Set Cron to execute web/index.php every 2 minutes.

- Create a new issue at https://github.com/hironozu/forward-twitter-timeline-to-slack/issues if it did not work.
