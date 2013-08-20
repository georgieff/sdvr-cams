<?php

include_once('worker.php');
include_once('katie-files/DbLib.php');

echo "<meta charset='utf-8'>";
echo "<pre>";
$worker = new SDVRWorker();
if($worker->try_fetch_feed()) {
  $prepared_data = $worker->prepare_data_for_insert();
} else {
  $prepared_data = null;
}
$db = new DbLib();

//var_dump($prepared_data);

