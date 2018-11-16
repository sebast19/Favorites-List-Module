<?php

/**
 * @file
 * contains \Drupal\favorites_list\Form;
 */

namespace Drupal\favorites_list\Form;

use Drupal\favorites_list\services\MyPlaylistHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FavoritesListForm extends FormBase {

  /**
   * The Playlist helper Class
   *
   * @var Drupal\favorites_list\services\MyPlaylistHelper $playlist_helper.
   */

  protected $playlist_helper;

  /**
   * Class Construct.
   */

  public function __construct(MyPlaylistHelper $playlist_helper) {

    $this->playlist_helper = $playlist_helper;

  }

  /**
   * {@inheritdoc}
   */

  public static function create(ContainerInterface $container) {

    return new static(
        $container->get('favorites.helper')
    );

  }


  /**
	 * {@inheritdoc}
	 */
	public function getFormId() {
    return 'favorites_list';
  }

  /**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {

        $field_nodes = $form_state->get('num_fields');

        $form['#tree'] = TRUE;

		$form['series_fieldset'] = [

			'#type' => 'fieldset',
			'#title' => $this->t('Favorites'),
            '#prefix' => '<div id="nodes-field-wrapper">',
            '#suffix' => '</div>',

        ];

        if (empty($field_nodes)) {

          $field_nodes = $form_state->set('num_fields' , 1);

        }

        if ($form_state->get('num_fields') > 0) {

          $value = $form_state->get('num_fields');

        } else {

          $value = 1;

        }

        for ($i = 0; $i < $value ; $i++) {

          $form['series_fieldset']['serie'][$i] = [

           '#type' => 'entity_autocomplete',
           '#target_type' => 'node',
           '#selection_settings' => [
               'target_bundles' => ['produccion'],
            ],

           '#placeholder' => ('Write the serie...'),

          ];

        }

        $form['actions'] = [

          '#type' => 'actions',

        ];


		$form['series_fieldset']['actions']['add_serie'] = [

 			'#type' => 'submit',
 			'#value' => $this->t('+'),
            '#submit' => ['::addOne'],
            '#ajax' => [
                'callback' => '::addMoreCallback',
                'wrapper' => 'nodes-field-wrapper',
            ],

 		];

        if ($value > 1) {

          $form['series_fieldset']['actions']['remove_serie'] = [

            '#type' => 'submit',
            '#value' => $this->t('-'),
            '#submit' => ['::removeCallback'],
            '#ajax' => [
              'callback' => '::addMoreCallback',
              'wrapper' => 'nodes-field-wrapper',
            ],

          ];

        }

        $form['series_fieldset']['actions']['separator'] = [

            '#type' => 'markup',
            '#markup' => '<hr>',

        ];

        $form_state->SetCached(FALSE);

        $form['series_fieldset']['actions']['submit'] = [

          '#type' => 'submit',
          '#value' => t('Save'),

        ];

        /**
         * @RenderElement("link");
         */

        $form['series_fieldset']['actions']['link_favourites'] = [
          '#type' => 'link',
          '#title' => $this->t('&nbsp;&nbsp;&nbsp;<button class="btn btn-info">Favorites List</button>'),
          '#url' => \Drupal\Core\Url::fromRoute('favorites_list.favorites_page'),
        ];

		return $form;
  }


  /**
   * function to add one field to save new serie.
   */

  public function addOne (array &$form, FormStateInterface $form_state) {

    $field_nodes = $form_state->get('num_fields');
    $add_button = $field_nodes + 1;
    $form_state->set('num_fields' , $add_button);
    $form_state->setRebuild();

  }


  /**
   * ajax callback to add the new field to the render form.
   * @return $form.
   */

  public function addMoreCallback (array &$form, FormStateInterface $form_state) {

    $field_nodes = $form_state->get('num_fields');
    return $form['series_fieldset'];

  }

  /**
   * ajax callback to remove one field of the fieldset form.
   * @return Drupal\Core\Form\FormStateInterface $form_state.
   */


  public function removeCallback (array &$form, FormStateInterface $form_state) {

    $field_nodes = $form_state->get('num_fields');

    if ($field_nodes > 1) {

      $remove_button = $field_nodes - 1;
      $form_state->set('num_fields' , $remove_button);

    }

    $form_state->setRebuild();

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /**
     * get the field value of form.
     */

    $nids = $form_state->getValue(['series_fieldset' , 'serie']);

    /**
     * call to method saveSerie() of service favorites.helper.
     */

    $example = $this->playlist_helper->saveSerie($nids);

    $this->Messenger()->addMessage(t('@result series has been added to the list!.' , ['@result' => implode(', ', $example)]));


	}
}
