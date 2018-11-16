<?php

namespace Drupal\favorites_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

/**
 * Class delete form
 * @package drupal\favorites_list\form;
 */

class DeleteForm extends ConfirmFormBase {


	  /**
	   * {@inheritdoc}
	   */

	public function getFormId() {

	    return 'delete_form';

	}


	public $nid;

	public function getQuestion() {

		$node = \Drupal::entityTypeManager()
					->getStorage('node')
					->load($this->id);


	   return t('Do you want to delete @result?', ['@result' => '"' . $node->getTitle() . '"'] );

	}


	public function getCancelUrl() {

	    return new Url('favorites_list.favorites_page');

	}

	public function getDescription() {

	    return t('<h4 class="text-warning">Only do this if you are sure!, This action cannot be undone.</h4><br>');

	}

	  /**
	   * {@inheritdoc}
	   */
	public function getConfirmText() {

	    return t('Remove it!');

	}

	  /**
	   * {@inheritdoc}
	   */
    public function getCancelText() {

    	return t('Cancel');

  	}


	  /**
	   * {@inheritdoc}
	   */
  	public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {

     	$this->id = $nid;
    	return parent::buildForm($form, $form_state);

  	}

	  /**
	    * {@inheritdoc}
	    */
  	public function validateForm(array &$form, FormStateInterface $form_state) {

    	parent::validateForm($form, $form_state);

  	}

	  /**
	   * {@inheritdoc}
	   */
  	public function submitForm(array &$form, FormStateInterface $form_state) {

		$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
	    $uid = $user->get('uid')->value;

	    $node = \Drupal::entityTypeManager()
					->getStorage('node')
					->load($this->id);

		$query = \Drupal::database();
	    $query->delete('favorites_playlist')
	          ->condition('nid',$this->id)
						->condition('uid',$uid)
	          ->execute();


	    drupal_set_message(t('@result series has been remove it from the list' , ['@result' => $node->getTitle()]));

	    $form_state->setRedirect('favorites_list.favorites_page');
  	}

}
