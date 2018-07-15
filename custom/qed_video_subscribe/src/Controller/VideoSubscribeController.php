<?php

namespace Drupal\qed_video_subscribe\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Datetime\Element;
use Drupal\Component\Datetime\DateTimePlus;


class VideoSubscribeController extends ControllerBase {
  
  /**
   * {@inheritdoc}
   */
  public function videoList() {
	// Adding custom js.
	$subs_list = \Drupal::database()->select('node_field_data', 'n');
	$subs_list->leftJoin('node__body', 'b', 'n.nid = b.entity_id');
	$subs_list->fields('n', ['nid', 'title']);
	$subs_list->fields('b', ['body_value']);
	$subs_list->condition('n.type', 'video_subscription');
	$result = $subs_list->execute()->fetchAll();
	
	// Getting all subscribe id on current logged in user.
	$sub_id = getSubscribeId();

	// Using video_list theme for formating.
    return [
      '#theme' => 'video_list',
      '#vds' => $result,
      '#subs_id' => $sub_id,
    ];
  }
  
  /**
  * Get list of vidoes subscribe by current user.
  */
  function videos($sid) {
    // Getting node id from url.
    $sid = explode("/", \Drupal::service('path.current')->getPath());;
    
    // Get user subscribe date.
    $vsl = db_select('video_subscribe', 's');
    $vsl->fields('s', array('date_time'));
    $vsl->condition('s.uid', \Drupal::currentUser()->id());
    $vsl->condition('s.sid', $sid[2]);
    $vsl_result = $vsl->execute()->fetchAll();
    $sdate = $vsl_result[0]->date_time;

    // Subscribe date.	
    $subs_date = date('m/d/Y', $sdate);

    // Current date.
    $curr_date = date('m/d/Y', strtotime('now'));

    // Get no of weeks between two dates.
    $weeks = $this->video_subscribe_get_week($subs_date, $curr_date) + 1;
  	
  	// Fetching all video from the table based on user subscription and no of weeks completed.
  	// If user subscribe current week then he will be able to only one video, 
  	// if he subscribed more than one week then he will be able to see 2 videos.
  	// Accordingly will increase no of video based on weeks.
    $subs_list = db_select('node_field_data', 'n');
    $subs_list->leftJoin('node__field_video_subscription_id', 'vs', 'n.nid = vs.entity_id');
    $subs_list->fields('n', array('nid', 'title'));
    $subs_list->condition('vs.field_video_subscription_id_target_id', $sid[2]);
    $subs_list->condition('n.type', array('video_subscription', 'video_series'), 'IN');
    $subs_list->range(0, $weeks);
    $result = $subs_list->execute()->fetchAll();

    return [
      '#theme' => 'subs_video_list',
      '#svl' => $result,
    ];
  }

  /**
  * Get no of weeks from date of subscribed and currentdate.
  */
  function video_subscribe_get_week($subs_date, $curr_date) {
    $subs_date_obj = DateTimePlus::createFromFormat('m/d/Y', $subs_date);
    $curr_date_obj = DateTimePlus::createFromFormat('m/d/Y', $curr_date);
  
    if($subs_date > $curr_date) {
  	  return week_between_two_dates($curr_date, $subs_date);
    }
  
    return floor($subs_date_obj->diff($curr_date_obj)->days/7);
  }
}
