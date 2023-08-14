<?php
namespace Drupal\movie\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Link;
use Drupal\Core\Url;

class ListPageController extends ControllerBase {
  public function movies() {
    global $base_url;

    $query = \Drupal::database()->select('movie_movie', 'm')
      ->fields('m', ['id', 'title'])
      ->orderBy('id', 'DESC')
      ->range(0, 10);

    $results = $query->execute()->fetchAll();
    $links = [];
    foreach ($results as $data) {
        $links[] = [$data->id, $data->title];
    }

    $output = [
      '#theme' => 'movies_list',
      '#links' => $links,
      '#base_url' => $base_url,
    ];

    return $output;
  }

  public function actors() {
    global $base_url;
    
    $query = \Drupal::database()->select('movie_actors', 'm')
      ->fields('m', ['id', 'name'])
      ->orderBy('id', 'DESC')
      ->range(0, 10);

    $results = $query->execute()->fetchAll();

    $links = [];
    foreach ($results as $data) {
        $links[] = [$data->id, $data->name];
    }

    $output = [
      '#theme' => 'actors_list',
      '#links' => $links,
      '#base_url' => $base_url,
    ];

    return $output;
  }
}