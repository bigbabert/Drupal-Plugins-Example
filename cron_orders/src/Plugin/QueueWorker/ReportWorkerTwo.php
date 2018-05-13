<?php

namespace Drupal\cron_orders\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "cron_orders_queue_2",
 *   title = @Translation("Second worker in cron_orders"),
 *   cron = {"time" = 20}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as cron_orders_queue_2 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_example.module
 */
class ReportWorkerTwo extends ReportWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->reportWork(2, $data);
  }

}
