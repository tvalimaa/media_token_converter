<?php

namespace Drupal\mediatoken\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to help celebrate good times!
 *
 * @Filter(
 *   id = "mediatoken",
 *   title = @Translation("Media token Converter"),
 *   description = @Translation("Convert d7 media module media tokens"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterMediatoken extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    global $base_root;
    $pattern = '
      /
      \{              # { character
          (?:         # non-capturing group
              [^{}]   # anything that is not a { or }
              |       # OR
              (?R)    # recurses the entire pattern
          )*          # previous group zero or more times
      \}              # } character
      /x
    ';
    preg_match_all($pattern, $text, $matches);
    $medias = $matches[0];
    $imgs = [];
    foreach($medias as $media => $element) {
      $element = json_decode($element);
      $db = \Drupal::database();
      $query = $db->select('migrate_map_d7_file_managed', 'f');
      $query->fields('f', ['filename']);
      $query->condition('f.fid', $element->fid);
      $data = $query->execute()->fetchCol();

      $imgs[$media] = '<img class="media-element file-default" alt="'.$element->attributes->alt.'" title="'.$element->attributes->title.'" typeof="foaf:Image" src="'.$base_root.'/sites/default/files/d7/'.$data[0].'" width="'.$element->attributes->width.'" height="'.$element->attributes->height.'">';
      $medias[$media] = '[['.$medias[$media].']]';
    }

    return new FilterProcessResult(str_replace($medias, $imgs, $text));
  }
}