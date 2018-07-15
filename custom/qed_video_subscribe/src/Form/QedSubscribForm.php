<?php

namespace Drupal\qed_video_subscribe\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;

/**
 * Create a form, current user can subscrube fot video series.
 */
class QedSubscribForm extends FormBase {

  /**
   * {@inheritdoc}
   */ 
  public function getFormId() {
  	return 'subscrib_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $parameter = NULL) {
    $form['sid'] = array(
      '#type' => 'hidden',
      '#default_value' => $parameter,
    );

    // On click using ajax to update current user subscription 
    // in custom table 'video_subscribe'.
    $form['actions'] = array(
      '#type' => 'button',
      '#value' => 'Subscribe',
      '#prefix' => '<div class="change_msg">',
      '#suffix' => '</div>',
      '#ajax' => array(
        'callback' => '::subscribeVideo',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Getting Random Username',
        ),
      ), 
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //return $form;;  
  }

  /**
   * On click using ajax to update current user subscription 
   * in custom table 'video_subscribe'.
   */
  public function subscribeVideo(array &$form, FormStateInterface $form_state) {
    // Create array to store data in custom table.
    $data = array(
      'uid' => \Drupal::currentUser()->id(),
      'sid' => $form_state->getValue('sid'),
      'date_time' => REQUEST_TIME,
    );

    // Insert or update data in table.
    db_insert('video_subscribe')->fields($data)->execute();

    // Create internal url.
    $url = Url::fromRoute('qed_video_subscribe.qed_subscribe_form');
    $internal_link = \Drupal::l(t('Click here'), $url);

    // Ajax command to replace the utton with text.
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.change_msg',
        '<div class="my_top_message">Subscribe Successfully. '.$internal_link.' </div>')
    );

  return $response;
  }
}
