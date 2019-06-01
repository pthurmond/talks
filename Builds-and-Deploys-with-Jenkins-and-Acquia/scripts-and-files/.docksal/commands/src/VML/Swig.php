<?php

namespace VML;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use VML\Util\DocksalCommand;

class Swig extends DocksalCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('swig')
      ->setDescription('Front end build system.')
      ->addOption('theme', 't', InputOption::VALUE_OPTIONAL, 'Specify a target theme.')
      ->addArgument('commands', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Which commands to run');

    $this->title = 'SWIG Task Runner';
  }

  /**
   * {@inheritdoc}
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    $target = $input->getOption('theme');
    $commands = $input->getArgument('commands');
    $config = Yaml::parse(file_get_contents($_ENV['PROJECT_ROOT'] . '/.docksal/configuration.swig.yml'));

    if (isset($config['themes']) && is_array($config['themes'])) {
      foreach ($config['themes'] as $theme) {
        if (!$target || ($target && $theme['name'] === $target)) {
          // Process Theme
          $this->message->step_header('Processing ' . $theme['name']);
          foreach ($commands as $command) {
            if (!in_array($command, array_column($config['commands'], 'name'))) {
              $this->message->warning('Command not found: ' . $command);
              break;
            }
            if (!in_array($command, $theme['cmds'])) {
              $this->message->warning('Command not allowed in theme: ' . $command);
              break;
            }
            $this->message->step_status('Running ' . $command);

            // Change directory.
            chdir($_ENV['PROJECT_ROOT'] . '/' . $theme['path']);

            // Run Command.
            $this->runProc(array_column($config['commands'], 'run', 'name')[$command]);
          }
          $this->message->step_finish();
        }
      }
    }
  }
}
