<?php

namespace Craft;

class ToJsonService extends BaseApplicationComponent {

  private $allowableFields = array();
  private $filterFields = false;

  public function getLinkUrl() {
    $plugin = craft()->plugins->getPlugin("tojson");
    $settings = $plugin->getSettings();
    return $settings->linkUrl;
  }

  public function toJson($content, $allowableFields = array(), $entryDepth = -1) {

    // Allow the user to specify a different URL for links
    $linkUrl = $this->getLinkUrl();
    if ( $linkUrl ) {
      craft()->setSiteUrl( $linkUrl );
    }

    $json = array();
    $this->allowableFields = $allowableFields;
    $this->filterFields = count($this->allowableFields);

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
  private function processModel($entry, $entryDepth, $priorEntries = array()) 
  {
    $json = array();

    // Some basic details
    $json['id'] = intval( $entry->id );
    $json['slug'] = $entry->slug;
    if ( $entry->uri ) {
      $json['uri'] = $entry->uri;
    }
    if ( $entry->title ) {
      $json['title'] = $entry->title;
    }

    // Have we gone too deep or have with found a circular structure
    if ( $entryDepth === 0 || in_array($json['id'], $priorEntries) ) {
      // Return high level details
      $json['_abstract'] = true;
      return $json;
    } else {
      array_push($priorEntries, $json['id']);
    }

    // $json['_schema'] = array();
    $fields = null;

    // $json['_model'] = preg_split("/[^\w]+/", get_class($entry));
    // $json['_model'] = $json['_model'][count($json['_model']) - 1];
    if ( $entry instanceof \Craft\EntryModel ) {
      $json['_section'] = $entry->getSection()->handle;

      if (isset($entry->postDate)) {
        $json['postDate'] = $entry->postDate->format(DateTime::ATOM);
      }
      if (isset($entry->dateCreated)) {
        $json['dateCreated'] = $entry->dateCreated->format(DateTime::ATOM);
      }
      if (isset($entry->dateUpdated)) {
        $json['dateUpdated'] = $entry->dateUpdated->format(DateTime::ATOM);
      }
    }
    if ( $entry instanceof \Craft\EntryModel || $entry instanceof \Craft\MatrixBlockModel ) {
      $fields = $entry->getType()->getFieldLayout()->getFields();
      $json['_type'] = $entry->getType()->handle;
      
    } else {
      // Tags, Categories
      $fields = $entry->getFieldLayout()->getFields();
    }
    
    // Apply the image transformations
    if ( $entry instanceof \Craft\AssetFileModel && $entry->kind == 'image' ) {

      // Base details
      $json['url'] = $entry->url;
      $json['width'] = $entry->width;
      $json['height'] = $entry->height;

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

      // Per-image sizing
      if ( isset($entry->customMode) && strlen($entry->customMode->value) ) {
        $custom = array();

        $custom['mode'] = $entry->customMode->value;
        $custom['width'] = isset($entry->customWidth) && $entry->customWidth > 0 ? 
            $entry->customWidth : 
            $entry->width;
        $custom['height'] = isset($entry->customHeight) && $entry->customHeight > 0 ? 
            $entry->customHeight : 
            $entry->height;
        $custom['quality'] = isset($entry->customQuality) && $entry->customQuality > 0 ? 
            $entry->customQuality : 
            100;

        if ( $entry->customMode->value == 'crop' ) {
          $custom['position'] = isset($entry->customPosition) && strlen($entry->customPosition->value) ?
              $entry->customPosition->value : 
              'center-center';
        }
        if ( isset($entry->customFormat) && strlen($entry->customFormat->value) ) {
          $custom['format'] = $entry->customFormat->value;
        }

        // Compute the URL
        $custom['url'] = $entry->setTransform($custom)->url;

        $json['variations']['_custom'] = $custom;
      }
    }

    foreach ( $fields as $f ) {
      $fieldObj = $f->getField();
      $name = $fieldObj->handle;
      $type = $fieldObj->type;
      $value = $entry->$name;

      if ( $this->filterFields && !in_array($name, $this->allowableFields) ) {
        $type = '_skip';
      }

      // TODO: add more details for each field type, such as max/min for integers
      // $json['_schema'][$name] = array("type" => $type);

      if ( $value === null ) {
        $type = '_skip';
      }

      switch ($type) {
        // Relationships:
        case 'Entries':
        case 'Matrix':
        case 'Categories':
        case 'Tags':
        case 'Assets':
          // value => Craft\ElementCriteriaModel
          $json[$name] = array();
          foreach ($value as $submodel) {
            $subJson = $this->processModel(
                $submodel, 
                ($type == 'Entries' ? $entryDepth - 1 : $entryDepth),
                $priorEntries);

            array_push($json[$name], $subJson);
          }
          break;

        case 'RichText':
          $json[$name] = "".$value;
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

        case '_skip':
          // See above
          break;

        default:
          // Unknown type. We dump some stuff to help us debug what we're looking at
          $json[$name] = print_r($value, true);
      }
    }
    return $json;
  }
}
