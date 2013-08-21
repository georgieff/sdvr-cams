<?php

include_once('katie-files/DbLib.php');

class DBWorker {

  //WE NEED A HUGE REFACTORING

  public function import_new_data($fetched_data) {

    $db = new DbLib();
    $db->setTable('days');

    foreach ($fetched_data as $days) {
      foreach ($days as $day_date => $day) {

        // check if it's a valid date
        $db->where('unix_date', $day_date);
        $res = $db->get('days');

        if ($db->affectedRows() === 1) {
          $day_record = $res->fetchAllObject();
          $day_id = $day_record[0]->day_id;

          foreach ($day as $camera) {
          //check if there is already record in the table
            $camera_name = trim($camera['name']);
            $db->where('day_id', $day_id);
            $db->where('comment', $camera_name); //comment shoud be changed with cam_id
            $camera_res = $db->get('cams_per_day');

            if($db->affectedRows() === 0) {
              $insert_data['day_id'] = $day_id;
              $insert_data['cam_id'] = 1; // shoud be changes
              $insert_data['comment'] = $camera_name;

              $db->insert('cams_per_day', $insert_data);
              //need log class
            } else {
              //need log class
            }
          }
        }
      }
    }
  }

  public function create_days() {

    for ($j=1; $j < 13; $j++) {
      for ($i=1; $i < 32; $i++) {
        $insertData['unix_date'] = mktime(0, 0, 0, $j, $i, 2013);
        $db->insert('', $insertData, false);
      }
    }
    echo $db->getError();
  }
}