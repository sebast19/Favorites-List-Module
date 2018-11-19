<?php

namespace Drupal\favorites_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides route response from favorites_list module.
 */


class FavoritesPage extends FormBase {

	protected $account;
	protected $connection;
	protected $entity_type;


	public function __construct(AccountInterface $account, Connection $connection, EntityTypeManager $entity_type) {

		$this->account = $account;
		$this->connection = $connection;
		$this->entity_type = $entity_type;

	}

	public static function create(ContainerInterface $container) {

		return new static(
			$container->get('current_user'),
			$container->get('database'),
			$container->get('entity_type.manager')
		);

	}

	/**
	 * @return array the id's and the titles of nodes loaded by current user.
	 */

	protected function getData() {

		$uid = $this->account->id();

		$result = $this->connection->query('SELECT * FROM favorites_playlist WHERE uid = :uid', ['uid' => $uid])->fetchAll();

		if (count($result) > 0) {

			foreach ($result as $field) {

				$nids[] = $field->nid;

			}

			$entities = $this->entity_type
					->getStorage('node')
					->loadByProperties(['nid' => $nids , 'status' => 1]);

			foreach ($entities as $entity) {

				$ids[] = $entity->id();
				$titles[] = $entity->getTitle();

				$data = [

					'id' => $ids,
					'title' => $titles,
				];
			}

			return $data;

		}

	}



	/**
	 * {@inheritdoc}
	 */

	public function getFormId() {

		return 'form_page';

	}

	/**
	 * {@inheritcdoc}
	 */

	public function buildForm(array $form , FormStateInterface $form_state) {

		$data = $this->getData();

		if (!empty($data)) {

			for ($i = 0; $i < count($data['id']); $i++) {

				$options[$data['id'][$i] ] = [

					'title' =>  $data['title'][$i],

				];

			}

		} else {

			$options = [];
		}

	    $table_header = [
			'title' => t('Title Of Production'),
		];

	    $form['table'] = [
	      '#type' => 'tableselect',
	      '#title' => $this->t('My favorite List'),
	      '#header' => $table_header,
	      '#options' => $options,
	      '#empty' => $this->t('You don\'t have series in the list yet'),
	    ];

	    $form['actions'] = [
	    	'#type' => 'submit',
	    	'#value' => 'Remove',
	    ];

	    return $form;

	}

	/**
	 * {@inheritdoc}
	 */

	public function submitForm(array &$form , FormStateInterface $form_state) {

		$values = array_filter($form_state->getValue('table'));

		if (!empty($values)) {

			$this->connection->delete('favorites_playlist')
						 	 ->condition('nid', $values, 'IN')
						  	 ->condition('uid', $this->account->id())
						 	 ->execute();

	    	$this->Messenger()->addMessage('The selected series has been remove from the list.');

		} else {

			$this->Messenger()->addWarning('Please select the production to be deleted', MessengerInterface::TYPE_WARNING);

		}


	}



}
