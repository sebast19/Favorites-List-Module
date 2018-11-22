<?php

namespace Drupal\favorites_list\services;

use Drupal\User\Entity\User;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MyPlaylistHelper {

	/**
	 *  Current User
	 * @var Drupal\Core\Session\AccountInterface $account
	 */

	protected $account;

	/**
	 *  Database Connection
	 * @var Drupal\Core\Database\Connection $connection
	 */

	protected $connection;

	/**
	 *  Entity Type Manager
	 * @var Drupal\Core\Entity\EntityTypeManager $entity_type
	 */

	protected $entity_type;

	/**
	 * Class Construct
	 *
	 * @param Drupal\Core\Session\AccountInterface $account
	 *  The Current user
	 * @param Drupal\Core\Database\Connection $connection
	 *  The Database Connection
	 * @param Drupal\Core\Entity\EntityTypeManager $entity_type
	 *  The Entity Type Manager
	 */

	public function __construct(AccountInterface $account , Connection $connection , EntityTypeManager $entity_type) {

		$this->account = $account;
		$this->connection = $connection;
		$this->entity_type = $entity_type;

	}

	/**
	 * Method create
	 *
	 * @param  Symfony\Component\DependencyInjection\ContainerInterface $container
	 */

	public static function create(ContainerInterface $container) {

		return new static(
			$container->get('current_user'),
			$container->get('database'),
			$container->get('entity_type.manager')
		);

	}

	/**
	 * Main Method to save data in database
	 *
	 * @param array $nids 
	 * 	the id's of the nodes to save in database
	 * 
	 * @return array $titles 
	 * 	the titles of nodes saved, to show a message
	 * 
	 * @return NULL
	 * 	When the array nids if empty, return null
	 */

	public function saveSerie(array $nids) {

		/**
		 * @var Drupal\Core\Session\AccountInterface $account->id()
		 */

		$uid = $this->account->id();

		/**
		 * load the node(s) for get the title and execute the query to database
		 */

		$nids = array_filter($nids); 

		if (!empty($nids)) {

			$nodes = $this->entity_type->getStorage('node')->loadMultiple($nids);

			foreach ($nodes as $node) {

				$this->connection->merge('favorites_playlist')
					->key(['id' => NULL])
					->fields([
						'uid' => $uid,
						'nid' => $node->id(),
					])
					->execute();

				$data_nodes[$node->id()] = $node->getTitle();

			}

			return $data_nodes;
			
		}

	}

	/**
	 * Method to delete series form list of user
	 * 
	 * @param array $values
	 * 	The values of series to delete
	 * 
	 * @param $uid
	 * 	The uid of the current user 
	 */

	public function deleteSerie(array $values, $uid) {

		$this->connection->delete('favorites_playlist')
						->condition('nid', $values, 'IN')
						->condition('uid', $uid)
						->execute();
							  			  

	}
}
