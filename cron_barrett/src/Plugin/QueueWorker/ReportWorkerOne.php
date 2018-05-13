<?php

namespace Drupal\cron_barrett\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "cron_barrett_queue_1",
 *   title = @Translation("First worker in cron_barrett"),
 *   cron = {"time" = 1}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as cron_barrett_queue_1 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_barrett.module
 */
class ReportWorkerOne extends ReportWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->reportWork(1, $data);
  }

}
