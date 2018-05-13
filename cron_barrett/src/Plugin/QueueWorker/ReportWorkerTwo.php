<?php

namespace Drupal\cron_barrett\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "cron_barrett_queue_2",
 *   title = @Translation("Second worker in cron_barrett"),
 *   cron = {"time" = 20}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as cron_barrett_queue_2 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_barrett.module
 */
class ReportWorkerTwo extends ReportWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->reportWork(2, $data);
  }

}
