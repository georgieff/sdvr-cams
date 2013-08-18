<?php

include_once('worker.php');

echo "<meta charset='utf-8'>";
echo "<pre>";
$worker = new SDVRWorker();
if($worker->try_fetch_feed()) {
  $prepared_data = $worker->prepare_data_for_insert();
} else {
  $prepared_data = null;
}

var_dump($prepared_data);

