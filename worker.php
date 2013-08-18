<?php

class SDVRWorker
{

  // https://www.facebook.com/feeds/page.php?id=388707501198026&format=json
  // is the URL that should return json data : )
  // if the worker is not working check the URL in your browser
  // if it's not working in the browser then we should research another way to fetch feed from facebook page : )
  // useful link may be this one: https://developers.facebook.com/tools/explorer/?method=GET&path=sdvr.stolichnapolicia%3Ffields%3Did%2Cname
  // play & enjoy! : )

  // Set the string needed for the camera posts on the news feed
  // property declaration
  private $cameras_string = 'График на средствата за контрол на пътното движение';
  private $page_feed_url_string = 'https://www.facebook.com/feeds/page.php?id=388707501198026&format=json';
  //some random user agent : D
  private $curl_user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/536.30.1 (KHTML, like Gecko) Version/6.0.5 Safari/536.30.1';

  private $curl;
  private $resp = '';

  public function __construct() {
    $this->curl = curl_init();
  }

  public function try_fetch_feed() {
    try {
      curl_setopt_array($this->curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $this->page_feed_url_string,
        CURLOPT_USERAGENT => $this->curl_user_agent
        ));
      $this->resp = curl_exec($this->curl);
      curl_close($this->curl);

      return TRUE;
    } catch (Exception $e) {

      return FALSE;
    }
  }

  //get the result of the curl request in plain/object/array format
  public function get_all_result($in = 'plain') {
    switch ($in) {
      case 'object':
      return json_decode($this->resp);
      break;
      case 'array':
      return json_decode($this->resp, true);
      break;
      default:
      return $this->resp;
      break;
    }
  }

  // returns fetched posts about speed cameras (only!)
  public function get_cameras_results() {
    $all_results = $this->get_all_result('object');
    $return_data = array();

    foreach ($all_results->entries as $key => $entry) {

      if(strpos($entry->content, $this->cameras_string) !== false) {
        $time_interval = null;
        $time_interval->date_published = strtotime($entry->updated);
        $time_interval->title = $entry->title;
        $time_interval->id = $entry->id;
        $time_interval->content = $entry->content;
        $return_data[] = $time_interval;
      }
    }

    return $return_data;
  }

  // prepares data about all the cameras fetched from the SDVR's page
  public function prepare_data_for_insert() {
    $prepared_data = array();
    foreach ($this->get_cameras_results() as  $post) {
      $data = $this->prepare_data_from_content($post->content);
      $prepared_data[] = $data;
    }

    return $prepared_data;
  }

  //prepare data from given contet (plain text)
  //and return it in format:
  /*
    data = {
      '<date_in_unix_time>' = {
        {
          'name' = '<camera_name>',
          'is_stationaire' = 'true/false'
        },
        {
          'name' = '<camera_name>',
          'is_stationaire' = 'true/false'
        }
        ...
      },
      '<another_date_in_unix_time>' = {etc.}
    }
  */
    private function prepare_data_from_content($content) {
      $last_date = "not_for_insert";
      $is_stationaire = false;
      $positions_by_dates = array();
      $cameras = array();
      $test = explode('<br />', $content);
      foreach ($test as $key => $line) {
        $line = trim($line);

        if(strlen($line) > 0){
          $item = $this->to_date($line);

          if($item != FALSE) {
            $positions_by_dates[$last_date] = $cameras;
            $cameras = null;
            $last_date = $item;
            $is_stationaire = false;
          } else {

            if(strpos($line, 'Стационарни')>-1) {
              $is_stationaire = true;
            } else {
              $camera['name'] = $line;
              $camera['is_stationaire'] = $is_stationaire;
              $cameras[] = $camera;
            }
          }
        }
      }
      $positions_by_dates[$last_date] = $cameras;

      return $positions_by_dates;
    }

  //check if given string is valid date
    private function to_date($string='')
    {
      $string = trim($string);
      $cleaned = str_replace('.', '', $string);
      $cleaned = str_replace('год','',$cleaned);
      $cleaned = str_replace('г','',$cleaned);
      if(is_numeric($cleaned)) {
        $year_pos = strpos($string, 'г');

        if($year_pos > 0) {
          $string = substr($string, 0, $year_pos);
        }
        $date = strtotime($string);
      //echo '-'.date("d m y", $date).'<hr>';

        return $date;
      }

      return FALSE;
    }

  }





