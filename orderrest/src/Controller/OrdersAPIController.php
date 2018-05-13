<?php

/**
 * @file
 * Contains \Drupal\orderrest\Controller\OrdersAPIController.
 */

namespace Drupal\orderrest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\Entity;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class OrdersAPIController extends ControllerBase {

    public function OrdersAPI(Request $request) {
        //order id parameter
        $orderID = $request->get('order_id');
        if ($orderID) {
        //Load Order
        $order = \Drupal\commerce_order\Entity\Order::load($orderID);
        //Load Billing info
        $billingProfile = \Drupal::entityTypeManager()->getStorage('profile')->load($order->getBillingProfile()->id());
        $user_arr = $billingProfile->get('address');
        $user_ship = $billingProfile->get('field_shipping');
        
        $userFields = (object) $user_arr[0]->getValue();
        
        if($user_ship[0]) {
            $shipFields = (object) $user_ship[0]->getValue();        
        } else {
            $shipFields = $userFields;
        }
        
            if($shipFields->given_name) {
                $shippingName = $shipFields->given_name;
            } else {
                $shippingName = $userFields->given_name;            
            }

            if($shipFields->family_name) {
                $shippingFamily = $shipFields->family_name;
            } else {
                $shippingFamily = $userFields->family_name;            
            }

            if($shipFields->organization) {
                $shippingOrg = $shipFields->organization;
            } else {
                $shippingOrg = $userFields->organization;            
            }

            if($shipFields->address_line1) {
                $shippingAd1 = $shipFields->address_line1;
            } else {
                $shippingAd1 = $userFields->address_line1;            
            }

            if($shipFields->address_line2) {
                $shippingAd2 = $shipFields->address_line2;
            } else {
                $shippingAd2 = $userFields->address_line2;            
            }

            if($shipFields->locality) {
                $shippingLoc = $shipFields->locality;
            } else {
                $shippingLoc = $userFields->locality;            
            }

            if($shipFields->postal_code) {
                $shippingZip = $shipFields->postal_code;
            } else {
                $shippingZip = $userFields->postal_code;            
            }
            if($shipFields->administrative_area) {
               $shipAdminArea = $shipFields->administrative_area;
            } else {
                $shipAdminArea = $userFields->administrative_area;
            }

            if($shipFields->country_code) {
                $shipCountry = \Drupal\dmaps\LocationCountriesManager::locationCountryName($shipFields->country_code);
                if ($shipProv = \Drupal\dmaps\LocationCountriesManager::locationProvinceName($shipFields->country_code, $shipAdminArea)) {
                    if ($shipProv != "") {
                        $shipState = $shipProv;
                    } else {
                        $shipState = $shipCountry;
                    }
                } else {
                    $shipState = $shipCountry;
                }            
                $shippingCC = $shipFields->country_code;
            } else {
                $shipCountry = \Drupal\dmaps\LocationCountriesManager::locationCountryName($userFields->country_code);
                if ($shipProv = \Drupal\dmaps\LocationCountriesManager::locationProvinceName($userFields->country_code, $userFields->administrative_area)) {
                    if ($shipProv != "") {
                        $shipState = $shipProv;
                    } else {
                        $shipState = $shipCountry;
                    }
                } else {
                    $shipState = $shipCountry;
                }
                $shippingCC = $userFields->country_code;            
            }
        
        $country = \Drupal\dmaps\LocationCountriesManager::locationCountryName($userFields->country_code);
        if ($province = \Drupal\dmaps\LocationCountriesManager::locationProvinceName($userFields->country_code, $userFields->administrative_area)) {
            if ($province != "") {
                $state = $province;
            } else {
                $state = $country;
            }
        } else {
            $state = $country;
        }
        //Get Order Items
        $orders = "";
        foreach ($order->getItems() as $item) {
            $orders.= '<OrderLineItems>
                           <SKU>8-14513-02012-3</SKU>
                           <ItemName>' . $item->get('title')->value . '</ItemName>
                           <Meta>Gem Lites: ' . $item->get('title')->value . '</Meta>
                           <Quantity>' . round($item->get('quantity')->value) . '</Quantity>
                           <LineTotal>' . number_format((float) $item->get('total_price')->number, 2, '.', '') . '</LineTotal>
                           <Price>' . number_format((float) $item->get('unit_price')->number, 2, '.', '') . '</Price>
                           <Category>Gem Lites</Category>
                       </OrderLineItems>';
        }
        $xmlFile = '<?xml version="1.0" encoding="UTF-8"?>
                        <Orders>
                          <Order>
                            <OrderId>' . $orderID . '</OrderId>
                            <OrderNumber>' . $orderID . '</OrderNumber>
                            <OrderDate>' . format_date($order->get('created')->value, 'custom', 'Y-m-d H:i:s') . '</OrderDate>
                            <OrderStatus>' . $order->get('state')->value . '</OrderStatus>
                            <BillingFirstName>' . $userFields->given_name . '</BillingFirstName>
                            <BillingLastName>' . $userFields->family_name . '</BillingLastName>
                            <BillingFullName>' . $userFields->given_name . ' ' . $userFields->family_name . '</BillingFullName>
                            <BillingCompany>' . $userFields->organization . '</BillingCompany>
                            <BillingAddress1>' . $userFields->address_line1 . '</BillingAddress1>
                            <BillingAddress2>' . $userFields->address_line2 . '</BillingAddress2>
                            <BillingCity>' . $userFields->locality . '</BillingCity>
                            <BillingState>' . $state->render() . '</BillingState>
                            <BillingPostCode>' . $userFields->postal_code . '</BillingPostCode>
                            <BillingCountry>' . $country->render() . ' (' . $userFields->country_code . ')</BillingCountry>
                            <BillingEmail>' . $order->get('mail')->value . '</BillingEmail>
                            <BillingPhone></BillingPhone>
                            <ShippingFirstName>' . $shippingName . '</ShippingFirstName>
                            <ShippingLastName>' . $shippingFamily . '</ShippingLastName>
                            <ShippingFullName>' . $shippingName . ' ' . $shippingFamily . '</ShippingFullName>
                            <ShippingCompany>' . $shippingOrg . '</ShippingCompany>
                            <ShippingAddress1>' . $shippingAd1 . '</ShippingAddress1>
                            <ShippingAddress2>' . $shippingAd2 . '</ShippingAddress2>
                            <ShippingCity>' . $shippingLoc . '</ShippingCity>
                            <ShippingState>' . $shipState->render() . '</ShippingState>
                            <ShippingPostCode>' . $shippingZip . '</ShippingPostCode>
                            <ShippingCountry>' . $shipCountry->render() . ' (' . $shippingCC . ')</ShippingCountry>
                            <ShippingMethodID>flat_rate:8</ShippingMethodID>
                            <ShippingMethod>Rush</ShippingMethod>
                            <PaymentMethodID>square</PaymentMethodID>
                            <PaymentMethod>Credit card</PaymentMethod>
                            <DiscountTotal>0.00</DiscountTotal>
                            <ShippingTotal>0.00</ShippingTotal>
                            <ShippingTaxTotal>0.00</ShippingTaxTotal>
                            <OrderTotal>' . number_format((float) $order->get('total_price')->number, 2, '.', '') . '</OrderTotal>
                            <FeeTotal>0.00</FeeTotal>
                            <TaxTotal>0.00</TaxTotal>
                            <CustomerNote></CustomerNote>
                            <CustomerId>1333</CustomerId>'.$orders.'</Order></Orders>';   
        } else {
            $xmlFile = '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlFile.= '<ErrorCode>400</ErrorCode>'
                    . '<ErrorMessage>Bad Request</ErrorMessage>';
        }


        $response = new Response();
        $response->setContent($xmlFile);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

}
