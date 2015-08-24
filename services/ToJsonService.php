<?php

namespace Craft;

class ToJsonService extends BaseApplicationComponent {

  public function toJson($content, $entryDepth=1) {

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
    $fields = null;
    if ( $entry instanceof \Craft\EntryModel || $entry instanceof \Craft\MatrixBlockModel ) {
      $fields = $entry->getType()->getFieldLayout()->getFields();
    } else {
      // Tags, Categories
      $fields = $entry->getFieldLayout()->getFields();
    }
    
    $json = array();
    if ( $entry->title != null ) {
      $json['title'] = $entry->title;
    }
    
    // Is this an image?
    if ( $entry instanceof \Craft\AssetFileModel && $entry->kind == 'image' ) {
      $json['url'] = $entry->url;
      $json['width'] = $entry->width;
      $json['height'] = $entry->height;
      // $json['thumbnail'] = $entry->setTransform(array('mode'=>'fit', 'width'=>'100'))->url;

      $json['variations'] = array();
      foreach( craft()->assetTransforms->getAllTransforms() as $transform ) {
        // $transform => AssetTransformModel
        $img = array();
        $img['url'] = $entry->setTransform($transform)->url;
        $img['width'] = $entry->width;
        $img['height'] = $entry->height;
        $json['variations'][$transform->name] = $img;
      }
    }

    foreach ( $fields as $f ) {
      $fieldObj = $f->getField();
      $name = $fieldObj->handle;
      $type = $fieldObj->type;
      $value = $entry->$name;

      // Debug:
      // $json[$name.'-'.$type] = $type;
      // $json[$name.'-class'] = get_class($value);

      if ( $value == null ) {
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
          if ( !($type == 'Entries' || $entryDepth <= 0) ) {
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
