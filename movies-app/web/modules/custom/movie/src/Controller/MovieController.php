<?php
/**
 * @file
 * @Contains Drupal\movie\Controller\MovieController.
 */

namespace Drupal\movie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class MovieController extends ControllerBase {
    public function index() {
        $header_table = [
            'id' => $this->t('ID'),
            'title' => $this->t('Title'),
            'synopsis' => $this->t('Synopsis'),
            'view' => $this->t('View'),
            'delete' => $this->t('Delete'),
            'edit' => $this->t('Edit'),
        ];

        $query = \Drupal::database()->select('movie_movie', 'm');
        $query->fields('m', ['id', 'title', 'synopsis']);
        $results = $query->execute()->fetchAll();
        $rows = [];

        foreach ($results as $data) {
            $url_delete = Url::fromRoute('movie.delete_form', ['id' => $data->id], []);
            $url_edit = Url::fromRoute('movie.add_form', ['id' => $data->id], []);
            $url_view = Url::fromRoute('movie.show_data', ['id' => $data->id], []);
            $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);
            $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
            $linkView = Link::fromTextAndUrl('View', $url_view);
            $rows[] = [
                'id' => $data->id,
                'title' => $data->title,
                'synopsis' => $data->synopsis,
                'view' => $linkView,
                'delete' => $linkDelete,
                'edit' =>  $linkEdit,
            ];
        }
        
        $form['table'] = [
            '#type' => 'table',
            '#header' => $header_table,
            '#rows' => $rows,
            '#empty' => $this->t('No data found'),
        ];
        return $form;
    }

    public function show(int $id) {
        $conn = Database::getConnection();

        $query = $conn->select('movie_movie', 'm')
            ->condition('id', $id)
            ->fields('m');
        $data = $query->execute()->fetchAssoc();
        $title = $data['title'];
        $synopsis = $data['synopsis'];

        $file = File::load($data['image']);
        if(!empty($file)) {
            $picture = $file->createFileUrl();
        }
        
        return [
            '#type' => 'markup',
            '#markup' => "<h1>$title</h1><br>
                          <img src='$picture' width='100' height='100' /> <br>
                          <p>$synopsis</p>"
        ];
    }

    public function view(int $id) { 
        global $base_url;

        $conn = Database::getConnection();
        $query = $conn->select('movie_movie', 'm')
                        ->condition('id', $id)
                        ->fields('m');
        $data = $query->execute()->fetchAssoc();

        $title = $data['title'];
        $synopsis = $data['synopsis'];

        $image = '';
        $file = File::load($data['image']);
        if(!empty($file)) {
            $image = $file->createFileUrl();
        }

        $query = \Drupal::database()->select('movie_actors', 'm')
                    ->fields('m', ['id', 'name'])
                    ->condition('id', $data['actor_id']);
        $data = $query->execute()->fetchAll();

        $actors = [];
        foreach ($data as $dat) {
            $actors[] = [$dat->id, $dat->name];
        }
        
        return [
            '#theme' => 'movie_single_theme',
            '#title' => $title,
            '#synopsis' => $synopsis,
            '#image' => $image,
            '#actors' => $actors,
            '#base_url' => $base_url,
        ];
    }
}
