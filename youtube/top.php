<?php
date_default_timezone_set('Asia/Phnom_Penh');
define("base_url", "http://localhost/youtube/youtube/");
define("default_blog", "7271011833334695575");
//for current url
$CURRENT_URL = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
session_start();
if (!file_exists(__DIR__ . '/Google/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/Google/autoload.php';
$OAUTH2_CLIENT_ID = '814595907237-kqk1qe9uc8iggm3m788k8u79056dipfh.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'sBKCkX2261txKtwilMcCSsuI';