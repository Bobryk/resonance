<?php
require_once 'soundcloudapi/Services/Soundcloud.php';

// create client object with app credentials
$client = new Services_Soundcloud(
  '7681d18716304f12fe0b0b7e45afbb06', '01318f693e41adf8eb12e97453e99df9', 'http://justinscheng.com/hack/activity.php');

// redirect user to authorize URL
header("Location: " . $client->getAuthorizeUrl());

?>