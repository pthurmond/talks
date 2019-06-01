<?php

namespace VML\Util;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * A Docksal command class.
 */
class DocksalCommand extends Command {
  /**
   * The title of the command.
   *
   * @var string
   */
  public $title;

  /**
   * The message class.
   *
   * @var \VML\Util\Message
   */
  public $message;

  /**
   * The input.
   *
   * @var InputInterface
   */
  public $input;

  /**
   * The output
   *
   * @var OutputInterface
   */
  public $output;

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;

    $this->message = new Message($input, $output);

    if ($output->isVerbose()) {
      $this->message->setOverwrite(FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Print Title.
    if ($this->title) {
      $this->message->title($this->title);
    }

    try {
      $this->exec($input, $output);
    }
    catch (\Exception $e) {
      $this->message->error($e->getMessage());
      exit;
    }
  }

  /**
   * Executes the current sub command.
   *
   * This method is not abstract because you can use this class
   * as a concrete class. In this case, instead of defining the
   * execute() method, you set the code to execute by passing
   * a Closure to the setCode() method.
   *
   * @return null|int null or 0 if everything went fine, or an error code
   *
   * @throws LogicException When this abstract method is not implemented
   *
   * @see setCode()
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    // Replaced by concrete exec.
  }


  /**
   * Command runner.
   *
   * @param string $cmd
   *   Command to be ran.
   * @param bool $passthru
   *   Whether to passthru the command or capture the output.
   *
   * @return array
   *   Command results.
   */
  public function runner($cmd, $passthru = FALSE) {
    if ($this->output->isVerbose()) {
      $this->message->text('Running: ' . $cmd);
    }

    if ($passthru) {
      $result = 0;
      passthru($cmd, $result);

      return [$cmd, $result, []];
    }
    else {
      $output = '';
      $result = 0;

      ob_start();
      exec($cmd, $output, $result);
      ob_end_clean();

      if ($result > 0) {
        $this->message->error('COMMAND FAILURE: ' . $result . ' :  ' . $cmd);
        $this->message->text($output);
        exit(1);
      }

      return [$cmd, $result, $output];
    }
  }

  /**
   * Procedure runner.
   *
   * @param string $cmd
   *   Command to be ran.
   *
   * @return array
   *   Success.
   */
  public function runProc($cmd) {
    if ($this->output->isVerbose()) {
      $this->message->text('Running Proc: ' . $cmd);
    }

    $process = new Process($cmd);
    $process->setTty(TRUE);
    $process->setTimeout(3600);

    $output = [];

    $status = $process->run(
      function ($type, $buffer) use (&$output, $process) {
        $process->checkTimeout();
        $output[] = $buffer;
      }
    );

    $process->wait();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    return [$cmd, $status, $output];
  }
}
