<?php

include_once('worker.php');

$worker = new SDVRWorker();
if($worker->try_fetch_feed()) {
  $decoded_response = $worker->get_cameras_results();
} else {
  $decoded_response = null;
}

echo "<meta charset='utf-8'>";
echo "<pre>";

var_dump($decoded_response);

