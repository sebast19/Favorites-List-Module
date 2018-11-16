<?php

namespace Drupal\favorites_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Favorites Block' Block.
 *
 * @Block(
 * 	 id = "favorites_list",
 * 	 admin_label = @Translation("Favorites Block"),
 * 	 category = @Translation("Custom Block"),
 *  )
 */

class FavoritesListBlock extends BlockBase {

	/*
	 *  {@inheritdoc}
	 */
	public function build(){

		$form = \Drupal::formBuilder()->getForm('Drupal\favorites_list\Form\FavoritesListForm');
		return $form;

	}
}
