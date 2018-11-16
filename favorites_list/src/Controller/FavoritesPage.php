<?php

namespace Drupal\favorites_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;


/**
 * Provides route response from favorites_list module.
 */

class FavoritesPage extends ControllerBase {

	/**
	 * Returns a page with a query of all series related to favourites series of current user.
	 *
	 * @return array
	 *	a simple renderable array.
	 */

	public function Page() {

		$table_header = array(
			'title' => t('Title Of Production'),
			'action' => t('Actions'),
		);

		$form = array(
			'#type' => 'markup',
			'#markup' => t('<h4> You still don\'t have series in favorites </h4>'),
			'#title' => t('My Favorites Page'),
		);

		$connection = \Drupal::database();

		$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    	$uid = $user->get('uid')->value;

  	/**
  	* Get the ID's of nodes.
  	*/

		$result = $connection->query('SELECT * FROM favorites_playlist WHERE uid = :uid', ['uid' => $uid])->fetchAll();

		/**
		 * if result of query is not empty do a render array with a table that contains the rows of series.
		 * @var array
		 */

		if (count($result) > 0) {

			foreach ($result as $data) {

				$nids[] = $data->nid;

			}

			$entities = \Drupal::entityTypeManager()
									->getStorage('node')
									->loadByProperties(['nid' => $nids , 'status' => 1]);

			$rows = array();

			foreach ($entities as $entity) {

				$delete = Url::fromUserInput('/user/favorites/' . $entity->id() . '/remove');

				$rows[] = array(

					'title' => Link::fromTextAndUrl($entity->getTitle(), $entity->toUrl()),
					'action' => Link::fromTextAndUrl('Remove from list', $delete),
				);

			}


			$form =  array(
				'#type' => 'table',
				'#header' => $table_header,
				'#rows' => $rows,
			);

		}

		return $form;

	}

}
