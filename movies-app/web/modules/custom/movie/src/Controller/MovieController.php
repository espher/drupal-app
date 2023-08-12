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

/**
 * Implement movie class operations.
 */
class MovieController extends ControllerBase {
    public function index() {
        //create table header
        $header_table = [
            'id' => $this->t('ID'),
            'title' => $this->t('Title'),
            'synopsis' => $this->t('Synopsis'),
            'view' => $this->t('View'),
            'delete' => $this->t('Delete'),
            'edit' => $this->t('Edit'),
        ];

        // get data from database
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

            //get data
            $rows[] = [
                'id' => $data->id,
                'title' => $data->title,
                'synopsis' => $data->synopsis,
                'view' => $linkView,
                'delete' => $linkDelete,
                'edit' =>  $linkEdit,
            ];
        }
        // render table
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
        $picture = $file->createFileUrl();

        return [
            '#type' => 'markup',
            '#markup' => "<h1>$title</h1><br>
                          <img src='$picture' width='100' height='100' /> <br>
                          <p>$synopsis</p>"
        ];
    }
}
