<?php

namespace Drupal\favorites_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides route response from favorites_list module.
 */

class FavoritesPage extends FormBase {

	/**	
	 * The Account of current user
	 * @var Drupal\Core\Session\AccountInterface $account
	 */

	protected $account;

	/**
	 * The Connection to database
	 * @var Drupal\Core\Database\Connection $connection
	 */

	protected $connection;

	/**
	 * The Entity Type Manager
	 * @var Drupal\Core\Entity\EntityTypeManager $entity_type
	 */

	protected $entity_type;


	/**
	 * Class Construct
	 * 
	 * @param Drupal\Core\Session\AccountInterface $account
	 * 	The Service of current user
	 * 
	 * @param Drupal\Core\Database\Connection $connection 
	 * 	The service of instance a database object
	 * 
	 * @param Drupal\Core\Entity\EntityTypeManager $entity_type
	 * 	The service of Entity Type Manager
	 */

	public function __construct(AccountInterface $account, Connection $connection, EntityTypeManager $entity_type) {

		$this->account = $account;
		$this->connection = $connection;
		$this->entity_type = $entity_type;

	}

	/**
	 * {@inheritdoc}
	 */

	public static function create(ContainerInterface $container) {

		return new static(
			$container->get('current_user'),
			$container->get('database'),
			$container->get('entity_type.manager')
		);

	}

	/**
	 * @return array $data
	 * 	The Array of id and title of each loaded production.
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
				$titles[] = Link::fromTextAndUrl($entity->getTitle(), $entity->toUrl());

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
