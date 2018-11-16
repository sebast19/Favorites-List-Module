<?php

namespace Drupal\favorites_list\services;

use Drupal\User\Entity\User;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MyPlaylistHelper {

	/**
	 * @var Drupal\Core\Session\AccountInterface $account
	 */

	protected $account;

	/**
	 * @var Drupal\Core\Database\Connection $connection
	 */

	protected $connection;

	/**
	 * @var Drupal\Core\Entity\EntityTypeManager $entity_type
	 */

	protected $entity_type;

	/**
	 * Method contruct
	 * @return Drupal\Core\Session\AccountInterface $account
	 * @return Drupal\Core\Database\Connection $connection
	 */

	public function __construct(AccountInterface $account , Connection $connection , EntityTypeManager $entity_type) {

		$this->account = $account;
		$this->connection = $connection;
		$this->entity_type = $entity_type;

	}

	/**
	 * Method Create from ContainerInterface
	 * @return Services from DependencyInjection
	 */

	public function create(ContainerInterface $container) {

		$account = $container->get('current_user');
		$connection = $container->get('database');
		$entity_type = $container->get('entity_type.manager');

		return new static(
			$account, 
			$connection, 
			$entity_type
		);

	}

	/**
	 * @return array $titles of nodes, with a query
	 */

	public function saveSerie($nids) {

		/**
		 * @var Drupal\Core\Session\AccountInterface $account->id()
		 */

		$uid = $this->account->id();

		/**
		 * load the node(s) for get the title and execute the query to database
		 */

		$nodes = $this->entity_type->getStorage('node')->loadMultiple($nids);

        foreach ($nodes as $node) {
			
			$this->connection->merge('favorites_playlist')
				->key(['id' => NULL])
				->fields([
					'uid' => $uid,
					'nid' => $node->id(),
				])
				->execute();

			$data_nodes = [

				'id' => $node->id(),
				'title' => $node->getTitle(),

			];

        }

        //return $data_nodes;

		// $message = \Drupal::Messenger()->addMessage(t('@result series has been added to the list!.' , ['@result' => implode(', ', $titles)]));

		// return $message;

	}
}
