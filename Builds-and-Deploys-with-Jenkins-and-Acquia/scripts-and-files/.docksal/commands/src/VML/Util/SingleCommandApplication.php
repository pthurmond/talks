<?php

namespace VML\Util;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Single Command Application.
 */
class SingleCommandApplication extends Application {
  private $singleCommandName;

  /**
   * Constructor.
   */
  public function __construct(Command $command, $name = 'UNKNOWN', $version = 'UNKNOWN') {
    parent::__construct($name, $version);

    // Add the given command as single (publicly accessible) command.
    $this->add($command);
    $this->singleCommandName = $command->getName();

    // Override the Application's definition so that it does not
    // require a command name as first argument.
    $this->getDefinition()->setArguments();
  }

  /**
   * Function getCommandName.
   *
   * @param InputInterface $input
   *   Input.
   *
   * @return Object
   *   Return command.
   */
  protected function getCommandName(InputInterface $input) {
    return $this->singleCommandName;
  }

}
