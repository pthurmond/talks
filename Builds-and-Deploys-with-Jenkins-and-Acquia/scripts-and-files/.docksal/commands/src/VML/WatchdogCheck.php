<?php

namespace VML;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VML\Util\DrushCommand;

class WatchdogCheck extends DrushCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('watchdog-check')
      ->setDescription('Watchdog Error Checker.');

    $this->title = 'Watchdog Checker';
  }

  /**
   * {@inheritdoc}
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    $this->message->step_header('Checking Watchdog Logs');

    $this->message->step_status('Checking: errors');
    list($cmd, $res, $out) = $this->runDrush('wd-show --severity=3 --count=10 --format=php', $output->isVerbose());
    $errors = unserialize($out[0]);

    if (count($errors) > 0) {
      foreach ($errors as $error) {
        $this->message->error($error['message']);
      }
      exit(1);
    }

    $this->message->step_finish();
  }
}
