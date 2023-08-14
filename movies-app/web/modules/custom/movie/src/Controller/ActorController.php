<?php
/**
 * @file
 * @Contains Drupal\movie\Controller\ActorController.
 */
namespace Drupal\movie\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class ActorController extends ControllerBase {
    public function index() {
        //create table header
        $header_table = [
            'id' => $this->t('ID'),
            'name' => $this->t('Name'),
            'biography' => $this->t('Biography'),
            'view' => $this->t('View'),
            'delete' => $this->t('Delete'),
            'edit' => $this->t('Edit'),
        ];

        $query = \Drupal::database()->select('movie_actors', 'm');
        $query->fields('m', ['id', 'name', 'biography']);
        $results = $query->execute()->fetchAll();
        $rows = [];
        foreach ($results as $data) {
            $url_delete = Url::fromRoute('movie.delete_actor', ['id' => $data->id], []);
            $url_edit = Url::fromRoute('movie.add_actor', ['id' => $data->id], []);
            $url_view = Url::fromRoute('movie.show_actor', ['id' => $data->id], []);
            $linkDelete = Link::fromTextAndUrl('Delete', $url_delete);
            $linkEdit = Link::fromTextAndUrl('Edit', $url_edit);
            $linkView = Link::fromTextAndUrl('View', $url_view);

            $rows[] = [
                'id' => $data->id,
                'name' => $data->name,
                'biography' => $data->biography,
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

        $query = $conn->select('movie_actors', 'm')
            ->condition('id', $id)
            ->fields('m');
        $data = $query->execute()->fetchAssoc();
        $name = $data['name'];
        $biography = $data['biography'];

        $file = File::load($data['picture']);
        if(!empty($file)) {
            $picture = $file->createFileUrl();
        }

        return [
            '#type' => 'markup',
            '#markup' => "<h1>$name</h1><br>
                          <img src='$picture' width='100' height='100' /> <br>
                          <p>$biography</p>"
        ];
    }

    public function view(int $id) { 
        global $base_url;

        $conn = Database::getConnection();
        $query = $conn->select('movie_actors', 'm')
                        ->condition('id', $id)
                        ->fields('m');
        $data = $query->execute()->fetchAssoc();

        $name = $data['name'];
        $biography = $data['biography'];

        
        $image = '';
        $file = File::load($data['picture']);
        if(!empty($file)) {
            $image = $file->createFileUrl();
        }
    
        $query = \Drupal::database()->select('movie_movie', 'm')
                    ->fields('m', ['id', 'title'])
                    ->condition('actor_id', $data['id']);
        $data = $query->execute()->fetchAll();


        $movies = [];
        foreach ($data as $dat) {
            $movies[] = [$dat->id, $dat->title];
        }
        
        return [
            '#theme' => 'actor_single_theme',
            '#name' => $name,
            '#biography' => $biography,
            '#image' => $image,
            '#movies' => $movies,
            '#base_url' => $base_url,
        ];
    }
}
