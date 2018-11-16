<?php

namespace Drupal\favorites_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FormPage extends FormBase {

    public function getFormId() {
        return 'form_page';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $options = [
            1 => ['first_name' => 'Indy', 'last_name' => 'Jones'],
            2 => ['first_name' => 'Darth', 'last_name' => 'Vader'],
            3 => ['first_name' => 'Super', 'last_name' => 'Man'],
        ];

        $header = [
            'first_name' => $this->t('First Name'),
            'last_name' => $this->t('Last Name'),
        ];

        $form['table'] = [
            '#type' => 'tableselect',
            '#title' => $this->t('Users'),
            '#header' => $header,
            '#options' => $options,
            '#empty' => $this->t('No users found'),
        ];

        $form['actions'] = [
            '#type' => 'submit',
            '#value' => 'Remove',
        ];

        return $form;

    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

        $values = $form_state->getValue(['table']);

        if (is_array($values)) {
            $values = array_filter($values);
        }

    }

}
