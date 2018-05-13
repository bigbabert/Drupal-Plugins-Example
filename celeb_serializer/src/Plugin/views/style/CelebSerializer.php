<?php

/**
 * @file
 * Contains \Drupal\celeb_serializer\Plugin\views\style\CelebSerializer.
 */

namespace Drupal\celeb_serializer\Plugin\views\style;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\field_collection\Entity;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\field_collection;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\field_collection\Entity\FieldCollectionItem;
use Drupal\Core\Field\FieldItemList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "ngm_serializer",
 *   title = @Translation("Celeb Serializer"),
 *   help = @Translation("Serializes views row data using the CelebSerializer component."),
 *   display_types = {"data"}
 * )
 */
class CelebSerializer extends Serializer {

    public function render() {

    function get_string_between($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    $rows = [];
    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }
    unset($this->view->row_index);

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }
    $replacedRows = [];
    
    foreach ($rows as $key => $row) {
if(isset($row['OrderId'])) {
            $orderID = $row['OrderId'];
        }
        $order = \Drupal\commerce_order\Entity\Order::load($orderID);
        if(isset($row['BillingFirstName'])) {
            $row['BillingFirstName'] = $order[0];
        }
        if(isset($row['BillingLastName'])) {
            $row['BillingLastName'] = get_string_between($row['BillingLastName'], 'class="family-name">', '</span>');
        }
        if(isset($row['BillingFullName'])) {
            $row['BillingFullName'] = get_string_between($row['BillingFullName'], 'class="given-name">', '</span>')." ".get_string_between($row['BillingFullName'], 'class="family-name">', '</span>');
        }
        if(isset($row['BillingCompany'])) {
            $row['BillingCompany'] = get_string_between($row['BillingCompany'], 'class="organization">', '</span>');
        }
        if(isset($row['BillingAddress1'])) {
            $row['BillingAddress1'] = get_string_between($row['BillingAddress1'], 'class="address-line1">', '</span>');
        }
        if(isset($row['BillingAddress2'])) {
            $row['BillingAddress2'] = get_string_between($row['BillingAddress2'], 'class="address-line2">', '</span>');
        }
        if(isset($row['BillingCity'])) {
            $row['BillingCity'] = get_string_between($row['BillingCity'], 'class="locality">', '</span>');
        }
        if(isset($row['BillingState'])) {
            $row['BillingState'] = get_string_between($row['BillingState'], 'class="country">', '</span>');
        }
        if(isset($row['BillingPostCode'])) {
            $row['BillingPostCode'] = get_string_between($row['BillingPostCode'], 'class="postal-code">', '</span>');
        }
        if(isset($row['BillingCountry'])) {
            $row['BillingCountry'] = get_string_between($row['BillingCountry'], 'class="country">', '</span>');
        }
        if(isset($row['BillingPhone'])) {
            $row['BillingPhone'] = " ";
        }
        
        if(isset($row['ShippingFirstName'])) {
            $row['ShippingFirstName'] = get_string_between($row['ShippingFirstName'], 'class="given-name">', '</span>');
        }
        if(isset($row['ShippingLastName'])) {
            $row['ShippingLastName'] = get_string_between($row['ShippingLastName'], 'class="family-name">', '</span>');
        }
        if(isset($row['ShippingFullName'])) {
            $row['ShippingFullName'] = get_string_between($row['ShippingFullName'], 'class="given-name">', '</span>')." ".get_string_between($row['ShippingFullName'], 'class="family-name">', '</span>');
        }
        if(isset($row['ShippingCompany'])) {
            $row['ShippingCompany'] = get_string_between($row['ShippingCompany'], 'class="organization">', '</span>');
        }
        if(isset($row['ShippingAddress1'])) {
            $row['ShippingAddress1'] = get_string_between($row['ShippingAddress1'], 'class="address-line1">', '</span>');
        }
        if(isset($row['ShippingAddress2'])) {
            $row['ShippingAddress2'] = get_string_between($row['ShippingAddress2'], 'class="address-line2">', '</span>');
        }
        if(isset($row['ShippingCity'])) {
            $row['ShippingCity'] = get_string_between($row['ShippingCity'], 'class="locality">', '</span>');
        }
        if(isset($row['ShippingState'])) {
            $row['ShippingState'] = get_string_between($row['ShippingState'], 'class="country">', '</span>');
        }
        if(isset($row['ShippingPostCode'])) {
            $row['ShippingPostCode'] = get_string_between($row['ShippingPostCode'], 'class="postal-code">', '</span>');
        }
        if(isset($row['ShippingCountry'])) {
            $row['ShippingCountry'] = get_string_between($row['ShippingCountry'], 'class="country">', '</span>');
        }
        if(isset($row['OrderTotal'])) {
            $row['OrderTotal'] = str_replace("$", "", $row['OrderTotal']);
        }
        
        if(isset($row['OrderLineItems'])){
        $products = explode("//", $row['OrderLineItems']);
          if($products) {
          $row['OrderLineItems'] = [];
            foreach( $products as $key => $productID) {
               $product = \Drupal\commerce_product\Entity\ProductVariation::load(get_string_between($productID, 'data-quickedit-entity-id="commerce_product_variation/', '">'));
               array_push(
                       $row['OrderLineItems'], 
                  [
                   "SKU" => $product->sku[0]->value.$key, 
                   "ItemName" => $product->title[0]->value, 
                   "Meta" => "Gem Lites",
                   "Meta" => "Gem Lites",
                   "Quantity" => "1",
                   "LineTotal" => number_format((float)$product->price[0]->number, 2, '.', ''),
                   "Price" => number_format((float)$product->price[0]->number, 2, '.', ''),
                   "Category" => "Gem Lites",
                   ]
                       );
            }              
          } else {
               $product = \Drupal\commerce_product\Entity\ProductVariation::load(get_string_between($row['OrderLineItems'], 'data-quickedit-entity-id="commerce_product_variation/', '">'));
               $row['OrderLineItems'] = [];
               array_push(
                       $row['OrderLineItems'], 
                  [
                   "SKU" => $product->sku[0]->value.$key, 
                   "ItemName" => $product->title[0]->value, 
                   "Meta" => "Gem Lites",
                   "Meta" => "Gem Lites",
                   "Quantity" => "1",
                   "LineTotal" => number_format((float)$product->price[0]->number, 2, '.', ''),
                   "Price" => number_format((float)$product->price[0]->number, 2, '.', ''),
                   "Category" => "Gem Lites",
                   ]
                       );              
          }


        }
        array_push($replacedRows, $row);
    }

    $results = 
            
            str_replace("response", "Orders", 
                str_replace("item", "Order", 
                            $this->serializer->serialize($replacedRows, $content_type, ['views_style_plugin' => $this])
                )
            );
    return $results;
    }

}
