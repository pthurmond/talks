<?php

namespace VML\Util;

use Symfony\Component\Console\Style\SymfonyStyle;

class Message extends SymfonyStyle
{

  private $overwrite = true;
  private $firstRun = true;
  private $step = 0;
  private $substep = 0;

  /**
   * header message.
   *
   * @param string $message
   *   Header message.
   */
  public function step_header($message)
  {
    $this->step++;
    $this->substep = 0;
    $this->writeln('<info>' . $message . '</info>');
  }

  /**
   * Step status message.
   *
   * @param string $message
   *   Status message.
   */
  public function step_status($message)
  {
    $this->substep++;
    $this->overwrite('<comment> - ' . $message . '</comment>');
  }

  /**
   * Overwrites a previous message to the output.
   *
   * @param string $message The message
   */
  private function overwrite(string $message)
  {
    if ($this->overwrite) {
      if (!$this->firstRun) {
        // Move the cursor to the beginning of the line
        $this->write("\x0D");
        // Erase the line
        $this->write("\x1B[2K");
      }
    }

    $this->firstRun = false;

    if ($message != '') {
      $message .= '...';
    }

    $this->write($message);

    if (!$this->overwrite) {
      $this->writeln('');
    }
  }

  /**
   * Step finish message.
   *
   * @param string $message
   *   Finish message.
   */
  public function step_finish($message = 'Done!')
  {
    $this->overwrite('');
    $this->writeln($message);
  }

  /**
   * Sets whether to overwrite the progressbar, false for new line.
   *
   * @param bool $overwrite
   */
  public function setOverwrite(bool $overwrite)
  {
    $this->overwrite = $overwrite;
  }
}
