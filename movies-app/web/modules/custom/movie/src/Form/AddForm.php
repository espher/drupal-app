<?php
/**
 * @file
 * @Contains Drupal\movie\Form\AddForm.
 */

namespace Drupal\movie\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Add form implementation.
 */
class AddForm extends FormBase {
    /**
     * (@inheritdoc)
     */
    public function getFormId() {
        return 'movie_form_id';
    }

    /**
     * (@inheritdoc)
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $conn = Database::getConnection();
        $data = [];
        if (isset($_GET['id'])) {
            $query = $conn->select('movie_movie', 'm')
                ->condition('id', $_GET['id'])
                ->fields('m');
            $data = $query->execute()->fetchAssoc();
        }

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
            '#default_value' => (isset($data['title'])) ? $data['title'] : '',
            '#required' => TRUE,
            '#wrapper_attributes' => ['class' => 'col-md-6 col-12'],
        ];
        $form['short_description'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Short Description'),
            '#default_value' => (isset($data['short_description'])) ? $data['short_description'] : '',
            '#required' => TRUE,
            '#wrapper_attributes' => ['class' => 'col-md-6 col-12'],
        ];
        $form['synopsis'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Synopsis'),
            '#required' => true,
            '#default_value' => (isset($data['synopsis'])) ? $data['synopsis'] : '',
            '#wrapper_attributes' => ['class' => 'col-md-6 col-xs-12'],
        ];
        $form['image'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('Image'),
            '#description' => $this->t('Choosier Image gif png jpg jpeg'),
            '#required' => (isset($_GET['id'])) ? FALSE : TRUE,
            '#upload_location' => 'public://images/',
            '#upload_validators' => [
                'file_validate_extension' => ['png jpeg jpg'],
            ]
        ];
        
        $actors = $this->getActors();

        $form['actor_id'] = [
            '#type' => 'select',
            '#title' => $this->t('Actor'),
            '#options' => $actors,
            '#default_value' => (isset($data['actor_id'])) ? $data['actor_id'] : '',
            '#required' => TRUE,
            '#wrapper_attributes' => ['class' => 'col-md-6 col-12'],
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('save'),
            '#buttom_type' => 'primary',
        ];

        return $form;
    }

    /**
     * (@inheritdoc)
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        if (is_numeric($form_state->getValue('title'))) {
            $form_state->setErrorByName('title', $this->t('Error, The First Name Must Be A String'));
        }
    }

    /**
     * (@inheritdoc)
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $image = $form_state->getValue('image');
        $data = [
            'title'    => $form_state->getValue('title'),
            'short_description'     => $form_state->getValue('short_description'),
            'synopsis'         => $form_state->getValue('synopsis'),
            'description'       => $form_state->getValue('description'),
        ];

        if (!is_null($image[0])) {
            $data += [
                'image' => $image[0],
            ];
        }

        if (isset($_GET['id'])) {
            \Drupal::database()->update('movie_movie')->fields($data)->condition('id', $_GET['id'])->execute();
        } else {
            \Drupal::database()->insert('movie_movie')->fields($data)->execute();
        }
        if (!is_null($image[0])) {
            $file = File::load($image[0]);
            $file->setPermanent();
            $file->save();
        }

        \Drupal::messenger()->addStatus($this->t('Successfully saved'));
        $url = new Url('movie.display_data');
        $response = new RedirectResponse($url->toString());
        $response->send();
    }
}

