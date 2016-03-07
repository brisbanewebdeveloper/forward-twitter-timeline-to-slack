# Requirements

- Server with Cron and PHP

# Installation

## Step 1 with Git

```shell
mkdir forward-twitter-timeline-to-slack
cd forward-twitter-timeline-to-slack
git clone git@github.com:hironozu/forward-twitter-timeline-to-slack.git .
cd twitter-api-php
git clone https://github.com/J7mbo/twitter-api-php.git .
cd ..
```

## Step 1 without Git

- Download the Zip file and extract it.
- Go to https://github.com/J7mbo/twitter-api-php, download the Zip file and extract it to twitter-api-php folder

## Step 2

- Create your Twitter App at https://dev.twitter.com/apps/

  + Login with your Twitter account.
  + Click "Create New App", fill the form and click "Create your Twitter application".
  + Select your new app.
  + Select "Keys and Access Tokens" tab.
  + Create Access Token if you did not see (I had to do this in my case).
  + Keep opening the page.

## Step 3

- Rename/Copy the file "settings.inc.example" to "settings.inc".

- Set the followings with the information at Step 2:

  + Consumer Key
  + Consumer Secret
  + Access Token
  + Access Token Secret

- Upload the following files to your server.

  + settings.inc
  + twitter-api-php/TwitterAPIExchange.php
  + web/index.php

- Create empty file "web/since_id.txt".

  + Set the permission to 664

## Step 4

- Set Cron to execute web/index.php every 2 minutes.

- Create a new issue at https://github.com/hironozu/forward-twitter-timeline-to-slack/issues if it did not work.
