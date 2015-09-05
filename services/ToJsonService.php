<?php

namespace Craft;

class ToJsonService extends BaseApplicationComponent {

  public function toJson($content, $entryDepth=2) {

    $json = array();

    if ( is_array($content) ) {
      foreach ($content as $entry) {
        array_push($json, $this->processModel($entry, $entryDepth));
      }

    } elseif ( is_object($content) ) {
      $json = $this->processModel($content, $entryDepth);
    }

    return $json;
  }


  /*
   * Processes Entries, Assets, Matrix blocks, Tags and Categories.
   */
  private function processModel($entry, $entryDepth=1) 
  {
    $json = array();

    // Some basic details
    $json['id'] = $entry->id;
    $json['slug'] = $entry->slug;
    $json['uri'] = $entry->uri;

    $json['_schema'] = array();
    $fields = null;

    // $json['_model'] = preg_split("/[^\w]+/", get_class($entry));
    // $json['_model'] = $json['_model'][count($json['_model']) - 1];

    if ( $entry instanceof \Craft\EntryModel || $entry instanceof \Craft\MatrixBlockModel ) {
      $fields = $entry->getType()->getFieldLayout()->getFields();
      $json['_model'] = $entry->getType()->handle;
      if ( $entry instanceof \Craft\EntryModel ) {
        $json['_section'] = $entry->getSection()->handle;
      }
    } else {
      // Tags, Categories
      $fields = $entry->getFieldLayout()->getFields();
    }
    if ( $entry->title ) {
      $json['title'] = $entry->title;
    }
    
    // Apply the image transformations
    if ( $entry instanceof \Craft\AssetFileModel && $entry->kind == 'image' ) {
      
      $json['url_src'] = $entry->url;
      $json['width_src'] = $json['width'] = $entry->width;
      $json['height_src'] = $json['height'] = $entry->height;
      $json['quality'] = 100;
      $json['mode'] = 'crop';
      $json['cropPosition'] = 'center-center';

      // Allow per-image resizing
      if (isset($entry->imageWidth)) { $json['width'] = $entry->imageWidth; }
      if (isset($entry->imageHeight)) { $json['height'] = $entry->imageHeight; }
      if (isset($entry->imageQuality)) { $json['quality'] = $entry->imageQuality; }
      if (isset($entry->imageMode)) { $json['mode'] = $entry->imageMode->value; }
      if (isset($entry->imageMode) && $entry->imageMode->value === 'crop' && $entry->imageCropPosition) { $json['cropPosition'] = $entry->imageCropPosition->value; }

      $json['url'] = $entry->setTransform($json)->url;

      // Create all the possible image variations
      $json['variations'] = array();
      foreach( craft()->assetTransforms->getAllTransforms() as $transform ) {
        // $transform => AssetTransformModel
        $img = array();
        $img['url'] = $entry->setTransform($transform)->url;
        $img['width'] = $entry->width;
        $img['height'] = $entry->height;
        $json['variations'][$transform->handle] = $img;
      }
    }

    foreach ( $fields as $f ) {
      $fieldObj = $f->getField();
      $name = $fieldObj->handle;
      $type = $fieldObj->type;
      $value = $entry->$name;

      // TODO: add more details for each field type, such as max/min for integers
      $json['_schema'][$name] = array("type" => $type);

      // Debug:
      // $json[$name.'-'.$type] = $type;
      // $json[$name.'-class'] = get_class($value);

      if ( $value === null ) {
        break;
      }

      switch ($type) {
        // Relationships:
        case 'Entries':
        case 'Matrix':
        case 'Categories':
        case 'Tags':
        case 'Assets':
          // value => Craft\ElementCriteriaModel
          $json[$name] = null;
          if ( !($type == 'Entries' && $entryDepth <= 0) ) {
            $json[$name] = array();
            foreach ($value as $submodel) {
              $subJson = $this->processModel($submodel, ($type == 'Entries' ? $entryDepth - 1 : $entryDepth));
              array_push($json[$name], $subJson);
            }
          }
          break;

        case 'RichText':
          $json[$name] = $value->getRawContent();
          break;

        case 'RadioButtons':
        case 'Dropdown':
          // $value => Craft\SingleOptionFieldData
          $json[$name] = $value->value;
          break;

        case 'MultiSelect':
          // $value => Craft\MultiOptionsFieldData
          $json[$name] = array();
          foreach ($value as $v) {
            array_push($json[$name], $v->value);
          }
          break;

        case 'Date':
          // value => Craft\DateTime
          $json[$name] = $value->iso8601();
          break;

        case 'Table':
          // value => Array
          $tableJson = array();
          foreach ($value as $row) {
            // All values are repeated with generic names and those given in the field. We stick to the values.
            array_push($tableJson, array_values(array_slice($row, 0, count($row)/2)));
          }
          $json[$name] = $tableJson;
          break;

        case 'PlainText':
        case 'Color':
          // value => String
          $json[$name] = $value;
          break;

        case 'Number':
        case 'Lightswitch':
          // value => String
          $json[$name] = floatval($value);
          break;

        default:
          // Unknown type. We dump some stuff to help us debug what we're looking at
          $json[$name] = print_r($value, true);
      }
    }
    return $json;
  }
}
