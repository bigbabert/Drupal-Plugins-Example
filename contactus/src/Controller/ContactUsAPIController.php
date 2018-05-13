<?php

/**
 * @file
 * Contains \Drupal\contactus\Controller\ContactUsAPIController.
 */

namespace Drupal\contactus\Controller;

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
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Html;

class ContactUsAPIController extends ControllerBase {

    public function contactUs(Request $request) {
 
        $cron_config = \Drupal::configFactory()->getEditable('cron_barrett.settings');
        // Default to an hourly interval. Of course, cron has to be running at least
        // hourly for this to work.
        $env = $cron_config->get('contactusemail');
        
        $env = !empty($env) ? $env : "bigbabert@gmail.com";

        $host = \Drupal::request()->getHost();

        $toMail = $env;
        $from_mail = \Drupal::config('system.site')->get('mail');
        $name = $request->get('name');
        $mail = urldecode($request->get('email'));
        $phone = $request->get('phone');
        $message = $request->get('message');
        $timestamp = \Drupal::time()->getCurrentTime();
        $subject  = "New Contact from: ".$host;

        $feedbackBody = " New contact from: ".$name." \n Mail: ".$mail." \n Phone: ".$phone." \n Message: ".$message." \n\n";
    //send email
    $mailManager = \Drupal::service('plugin.manager.mail');
 
    $module                             = 'contactus';
    $key                                = 'contactus_send';
    $to                                 = $toMail;
    $params['reply-to']                 = $from_mail;
    $params['title']          = Html::escape($subject);
    $params['headers']['Content-Type']  = 'text/html; charset=UTF-8';
    $params['Content-Type']             = 'text/html; charset=UTF-8';
    $params['message']             = Html::escape($feedbackBody);
    
    $langcode                           = \Drupal::currentUser()->getPreferredLangcode();
    $send                               = true;
 

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, $from_mail, $send);
    if($result["result"] && isset($mail) && isset($name)){
        $resp['data'] = 'success ';
    }else{
        $resp['data'] = 'error';
    }
    
    \Drupal::logger($module)->notice($resp['data']." ".$subject);
    
    $response = new Response();
    $response->setContent(json_encode($resp));
    $response->headers->set('Content-Type', 'application/json');
    
    return $response;
    }

}
