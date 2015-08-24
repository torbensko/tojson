<?php

/*

Craft\ElementCriteriaModel:
  Matrix
    children: Craft\MatrixBlockModel
  Entries
    children: Craft\EntryModel
  Assets
    children:

Craft\RichTextData

*/

namespace Craft;

class ToJsonService extends BaseApplicationComponent {

  public function toJson($content, $entryDepth=1) {

    $json = array();

    if (is_array($content)) {
      foreach ($content as $entry) {
        array_push($json, $this->processModel($entry, $entryDepth));
      }

    } elseif ( is_object($content) ) {
      $json = $this->processModel($content, $entryDepth);

    } else {
      $json = null;
    }

    return $json;
  }


  /*
   * Processes Entries and Matrix blocks.
   */
  private function processModel($entry, $entryDepth=1) 
  {
    $fields = $entry->getType()->getFieldLayout()->getFields();
    $json = array();

    foreach ( $fields as $f ) {
      $fieldObj = $f->getField();
      $name = $fieldObj->handle;
      $type = $fieldObj->type;
      $value = $entry->$name;

      // echo $type."\n";
      // if (is_object($value)) {
      //   echo get_class($value)."\n";
      // }

      switch ($type) {
        // Relationships:
        case 'Entries':
        case 'Matrix':
          // value => Craft\ElementCriteriaModel
          if ( $type != 'Entries' || $entryDepth > 0) {
            $json[$name] = array();
            foreach ($value as $submodel) {
              $subJson = $this->processModel($submodel, ($type == 'Entries' ? $entryDepth - 1 : $entryDepth));
              array_push($json[$name], $subJson);
            }
          }
          break;

        case 'Assets':
          $json[$name] = array();
          foreach ($value as $assetObj) {
            // $asset => AssetFileModel
            $assetJson = array();
            $assetJson['url'] = $assetObj->url;
            $assetJson['thumbnail'] = $assetObj->setTransform(array('mode'=>'fit', 'width'=>'100'))->url;
            array_push($json[$name], $assetJson);
          }
          break;

        case 'RichText':
          $json[$name] = $value->getRawContent();
          break;

        case 'RadioButtons':
          // $value => Craft\SingleOptionFieldData
          $json[$name] = $value->value;
          break;

        case 'PlainText':
          // value is a String
          $json[$name] = $value;
          break;

        default:
          // unknown type (dump some stuff to help us debug what we're looking at)
          $json[$name] = print_r($value, true);
      }

      // if ( $value instanceof \Craft\ElementCriteriaModel ) {
        
      // } else if ( $value instanceof \Craft\RichTextData ) {
        
      // } else if ( $value instanceof \Craft\SingleOptionFieldData ) {
      //   $json[$name] = $value->value;
      // } else if ( is_string($value) ) {
      //   $json[$name] = $value;
      // } else {
        
      // }
    }
    return $json;
  }
}
