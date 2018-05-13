<?php

namespace Drupal\ordersapi\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class OrderCompleteSubscriber.
 *
 * @package Drupal\ordersapi
 */
class OrderCompleteSubscriber implements EventSubscriberInterface {

    /**
     * Drupal\Core\Entity\EntityTypeManager definition.
     *
     * @var \Drupal\Core\Entity\EntityTypeManager
     */
    protected $entityTypeManager;

    /**
     * Constructor.
     */
    public function __construct(EntityTypeManager $entity_type_manager) {
        $this->entityTypeManager = $entity_type_manager;
    }

    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents() {
        $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];

        return $events;
    }

    /**
     * This method is called whenever the commerce_order.place.post_transition event is
     * dispatched.
     *
     * @param WorkflowTransitionEvent $event
     */
    public function orderCompleteHandler(WorkflowTransitionEvent $event) {
        /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
        $order = $event->getEntity();
        $orderID = $order->get('order_number')->value;
        // Order items in the cart.
        $items = $order->getItems();
        $cron_config = \Drupal::configFactory()->getEditable('cron_barrett.settings');
        // Default to an hourly interval. Of course, cron has to be running at least
        // hourly for this to work.
        $env = $cron_config->get('envbarrett');

        $env = !empty($env) ? $env : "test";

        if($env == 0) {
            $env = "test";
        } if($env == 1) {
            $env = "prod";
        }
        //Curl to get order xml info.
        $curl = curl_init();

        $protocol = ($_SERVER['HTTPS'] && ($_SERVER['HTTPS'] != "off")) ? "https" : "http";
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $host = \Drupal::request()->getHost();

        if($host == "www.celebluxury.com" || $host == "celebluxury.com") {
            $curlPath = "https://www.celebluxury.com/api/v2/orders?order_id=" . $orderID;
        } else {
            $curlPath = $protocol."://".$host."/api/v2/orders?order_id=".$orderID;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $curlPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 1000,
            CURLOPT_TIMEOUT => 3000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\n\"name\": { \"value\": \"foodsdffdfBar\" },\n\"mail\": { \"value\": \"fosfdfsdo@bar.com\" }\n}",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic Y2VsZWJsdXh1cnk6Y2VsZWIyMDE4",
                "content-type: application/json",
                "postman-token: eb38c89e-4b54-0153-dd32-22430d63918a"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $date = date("Y_n_j-H_i_s") . "-" . $orderID;

        $log_success = "\n File exported with success\n OrderID:" . $orderID . "\n Date:" . $date . "\n\n--------------------------------------------\n";
        $log_error = "\n FTP connection issue\n OrderID:" . $orderID . "\n Date:" . $date . "\n\n--------------------------------------------\n";
        $ftp_server = "ftp2.barrettdistribution.com";      // FTP Server Address (exlucde ftp://)
        $ftp_user_name = "woo_1333";     // FTP Server Username
        $ftp_user_pass = 'P2M$RGdr';      // Password

        if ($err) {
            $log = "cURL Error #:" . $err;
            // Logs an error
            $message = "Barrett, Curl Error calling orders API (" . $err . "), ".$log_error;
            \Drupal::logger('ordersapi')->error($message);
        } else {
            $log = $response;
            // Logs a notice
            $message = "Barrett, Curl orders API OK";
            \Drupal::logger('ordersapi')->notice($message);
        }

        $filename = 'exp_ord_' . $date . '.xml';
        $filepath = $_SERVER['DOCUMENT_ROOT'] . '/orders/'.$filename;
        file_put_contents($filepath, $log, FILE_APPEND);

        if ($response) {
            //Barrett FTP conection id
            $id_connessione = ftp_connect($ftp_server);
            ftp_pasv($id_connessione, true);
            //Login barrett FTP
            $login = ftp_login($id_connessione, $ftp_user_name, $ftp_user_pass);

            if (!$login) {
                // Logs an error
                $message = "Barrett, FTP connection error login, ".$log_error;
                \Drupal::logger('ordersapi')->error($message);
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/orders/log_exp.txt', $log_error, FILE_APPEND);
                exit();
            }
            //select upload folder
            ftp_chdir($id_connessione, '/'.$env.'/barrett_woo_order/');
            //Upload del File
            if (ftp_put($id_connessione, $filename, $filepath, FTP_ASCII)) {
                $message = "Barrett, File upload OK, ".$log_success;
                \Drupal::logger('ordersapi')->notice($message);
            } else {
                // Logs an error
                $message = "Barrett, FTP connection error while uploading file, ".$log_error;
                \Drupal::logger('ordersapi')->error($message);
                //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/orders/log_exp.txt', $log_error, FILE_APPEND);
            }
            //Close barrett FTP connection
            ftp_close($id_connessione);
        } else {
            // Logs an error
            $message = "Barrett, unknown error (" . $log . ")";
            \Drupal::logger('ordersapi')->error($message);
            //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/orders/log_exp.txt', $log_error, FILE_APPEND);
        }
    }

}
