<?php
namespace Craft;

class JsonExpandService extends BaseApplicationComponent {

    public function getJson($content) {

      if (is_array($content)) {

        $json = array();

        foreach ($content as $entry) {
          $json[] = $this->getEntryJson($entry);
        }

      } elseif (is_object($content)) {

        $json = $this->getEntryJson($content);

      } else {

        $json = null;

      }

      return $json;
    }


    private function getEntryJson($entry) {

      $entryData = array();


      $entryData = $entry->getAttributes();

      $entryData['title'] = $entry->title;




      foreach ($entry->getType()->getFieldLayout()->getFields() as $field) {

        $field = $field->getField();
        $handle = $field->handle;
        $value = $entry->$handle;

        if ($value instanceof \ArrayObject) {
          $value = array_merge((array)$value);
        }



        // gets all the default attributes from the element

        // check if its a relational field type
        if ( $field['type'] == 'Categories' || $field['type'] == 'Users'  || $field['type'] == 'Assets' || $field['type'] == 'Entries' || $field['type'] == 'Matrix'  ) {

          // for each related element
          foreach ($value as $relatedElement) {

            $relatedArray = array();

            // gets all the default attributes from the element
            $relatedArray = $relatedElement->getAttributes();

            $relatedArray['title'] = $relatedElement->title;

            // setup switch for fieldtype specific further customizations
            switch ($field['type']) {
              case 'Assets':
                $relatedArray['url'] = $relatedElement->url;

                $thumbTransform = array('mode'=>'fit', 'width'=>'100');
                $relatedArray['thumbnail'] = $relatedElement->setTransform($thumbTransform)->url;
                break;

              case 'Matrix':
                $relatedArray['type'] = $relatedElement->type;
                break;

              case 'Matrix':
                $relatedArray['type'] = $relatedElement->type;
                break;



             }

            // get all the custom fields
            foreach ($relatedElement->getFieldLayout()->getFields() as $subField) {

              $subField = $subField->getField();
              $subHandle = $subField->handle;
              $subValue = $relatedElement->$subHandle;

              if ($subValue instanceof \ArrayObject) {
                  $subValue = array_merge((array)$subValue);
              }

              if($subField['type'] == "RichText") {
                // Rich Text field values need to be converted to a string
                $relatedArray[$subHandle] = (string)$subValue;
              } else {
                $relatedArray[$subHandle] = $subValue;
              }

            }
            // add the item to the fields array
            $entryData[$handle][] = $relatedArray;
          }




        } else if($field['type'] == "RichText") {

          // Rich Text field values need to be converted to a string
          $entryData[$handle] = (string)$value;

        } else {
          // just set the field value to the field


            $entryData[$handle] = $value;



          //echo $field['type'];
        }
      }

      return $entryData;

    }


}
