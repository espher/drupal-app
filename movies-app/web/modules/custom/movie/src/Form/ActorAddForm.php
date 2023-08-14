<?php
/**
 * @file
 * @Contains Drupal\movie\Form\ActorAddForm.
 */

namespace Drupal\movie\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ActorAddForm extends FormBase {
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
            $query = $conn->select('movie_actors', 'm')
                ->condition('id', $_GET['id'])
                ->fields('m');
            $data = $query->execute()->fetchAssoc();
        }

        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('name'),
            '#default_value' => (isset($data['name'])) ? $data['name'] : '',
            '#required' => TRUE,
            '#wrapper_attributes' => ['class' => 'col-md-6 col-12'],
        ];
        $form['biography'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Short Description'),
            '#default_value' => (isset($data['biography'])) ? $data['biography'] : '',
            '#required' => TRUE,
            '#wrapper_attributes' => ['class' => 'col-md-6 col-12'],
        ];
        $form['picture'] = [
            '#type' => 'managed_file',
            '#title' => $this->t('picture'),
            '#description' => $this->t('Choosier Image gif png jpg jpeg'),
            '#required' => (isset($_GET['id'])) ? FALSE : TRUE,
            '#upload_location' => 'public://images/',
            '#upload_validators' => [
                'file_validate_extension' => ['png jpeg jpg'],
            ]
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
        if (is_numeric($form_state->getValue('name'))) {
            $form_state->setErrorByName('name', $this->t('Error, The First Name Must Be A String'));
        }
    }

    /**
     * (@inheritdoc)
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $image = $form_state->getValue('picture');
        $data = [
            'name'    => $form_state->getValue('name'),
            'biography'     => $form_state->getValue('biography'),
        ];

        if (!is_null($image[0])) {
            /*
            $data += [
                'picture' => $image[0],
            ];
            */
        }

        if (isset($_GET['id'])) {
            \Drupal::database()->update('movie_actors')->fields($data)->condition('id', $_GET['id'])->execute();
        } else {
            \Drupal::database()->insert('movie_actors')->fields($data)->execute();
        }
        if (!is_null($image[0])) {
            $file = File::load($image[0]);
            $file->setPermanent();
            $file->save();
        }
        \Drupal::messenger()->addStatus($this->t('Successfully saved'));
        $url = new Url('movie.display_actor');
        $response = new RedirectResponse($url->toString());
        $response->send();
    }
}

