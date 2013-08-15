<?php

$worker = new SDVRWorker();
if($worker->try_fetch_feed()) {
  $decoded_response = $worker->get_cameras_results();
} else {
  $decoded_response = '{}';
}
echo "<meta charset='utf-8'>";
echo "<pre>";

var_dump($decoded_response);

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

  public function get_cameras_results() {
    $all_results = $this->get_all_result('object');
    $return_data = array();

    foreach ($all_results->entries as $key => $entry) {

      if(strpos($entry->content, $this->cameras_string) !== false) {
        $day = null;
        $day->date_published = strtotime($entry->updated);
        $day->title = $entry->title;
        $day->id = $entry->id;
        $day->content = $entry->content;
        $return_data[] = $day;
      }
    }

    return $return_data;
  }
}





