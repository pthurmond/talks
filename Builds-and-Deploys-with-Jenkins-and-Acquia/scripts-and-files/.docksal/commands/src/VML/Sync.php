<?php

namespace VML;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use VML\Util\DrushCommand;

/**
 * Sync class.
 *
 */
class Sync extends DrushCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('sync')
      ->setDescription('Drupal Synchronization System.')
      ->addOption('confirm', 'y', InputOption::VALUE_NONE, 'Auto confirm actions.')
      ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'The site to sync.')
      ->addOption('environment_from', 'ef', InputOption::VALUE_OPTIONAL, 'The environment to import from.')
      ->addOption('environment_as', 'ea', InputOption::VALUE_OPTIONAL, 'The environment to import as.', 'local')
      ->addOption('skip-db-sync', 'sdb', InputOption::VALUE_NONE, 'Skip DB sync process.')
      ->addOption('skip-dump', 'sd', InputOption::VALUE_NONE, 'Skip MySQL dump file creation.')
      ->addOption('skip-dump-recent', 'sdr', InputOption::VALUE_NONE, 'Skip MySQL dump file creation if file is recent.')
      ->addOption('skip-import', 'si', InputOption::VALUE_NONE, 'Skip database import.')
      ->addOption('skip-files', 'sf', InputOption::VALUE_NONE, 'Skip file rsync.')
      ->addOption('skip-composer', 'sc', InputOption::VALUE_NONE, 'Skip composer dependency install.')
      ->addOption('skip-reset', 'sr', InputOption::VALUE_NONE, 'Skip Drupal reset.')
      ->addOption('dump-dir', 'dd', InputOption::VALUE_REQUIRED, 'Specify dump directory.');

    $this->title = 'Environment Synchronization';
  }

  /**
   * {@inheritdoc}
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    // Get site.
    $site = $input->getOption('site');
    $site_options = $this->getSites();

    if (!$site || !in_array($site, $site_options)) {
      $site = $this->message->choice('Please select the source site', $site_options, 'unitedrentals');
    }

    $this->message->success('Site: ' . $site);

    // Get source environment,
    $environment_from = $input->getOption('environment_from');
    $environment_options = $this->getSiteEnvironments($site);

    if (!$environment_from || !in_array($environment_from, $environment_options)) {
      $environment_from = $this->message->choice('Please select the source environment', $environment_options, 'dev0');
    }

    $this->message->success('Environment from: ' . $environment_from);

    // Get reset environment.
    $environment_as = $input->getOption('environment_as');
    $this->message->success('Environment as: ' . $environment_as);

    if (!$environment_as || !in_array($environment_as, $environment_options)) {
      $environment_from = $this->message->choice('Please select the environment to import as', $environment_options, 'local');
    }

    // Validate aliases.
    $alias = $this->findDrushAlias("{$site}.{$environment_from}");
    $local_alias = $this->findDrushAlias("{$site}.local");

    // Confirm that the user wants to continue.
    $confirm = $input->getOption('confirm');

    if (!$confirm && !$this->message->confirm('Confirm overwrite of database "' . $local_alias . '"?')) {
      return;
    }

    // Run composer install.
    if (!$input->getOption('skip-composer')) {
      $this->message->step_header('Composer Install');
      chdir($_SERVER['PROJECT_ROOT']);
      $this->message->step_status('Installing Dependencies');

      list($cmd, $res, $out) = $this->runner('composer install --prefer-dist -v -o -n 2>&1', $output->isVerbose());
      $this->message->step_finish();
    }

    // Run database functions.
    $dump_directory = $input->getOption('dump-dir') ? : '/tmp';
    $dump_file = $_SERVER['PROJECT_ROOT'] . '/' . $dump_directory . '/' . $site . '.' . $environment_from . '.sql';

    if ($dump_directory === '/tmp') {
      $dump_file = '/tmp/' . $site . '.' . $environment_from . '.sql';
    }

    // Database sync.
    if (!$input->getOption('skip-db-sync')) {
        chdir($_SERVER['PROJECT_ROOT']);
        $this->message->step_header('Database Sync');
        $this->runDrush('sql-sync ' . $alias . ' ' . $local_alias . ' --source-dump=/tmp/tmp.sql -y', NULL, $output->isVerbose());

        $this->message->step_status('Finishing');
        $this->message->step_finish();
    }

    // Sync files from source environment.
    if (!$input->getOption('skip-files')) {
      $this->message->step_header('File Import');
      chdir($_SERVER['PROJECT_ROOT'] . '/' . $_SERVER['DOCROOT']);

      $this->message->step_status('Syncing files');
      $this->runDrush('-y --exclude-paths=files/styles:files/js:files/css rsync ' . $alias . ':%files/ ' . $local_alias . ':%files', NULL, NULL, $output->isVerbose());

      $this->message->step_finish();
      chdir($_SERVER['PROJECT_ROOT']);
    }

    // Run environment reset.
    if (!$input->getOption('skip-reset')) {
      $this->message->step_header('Drupal Reset');

      chdir($_SERVER['PROJECT_ROOT']);
      $reset_config = Yaml::parse(file_get_contents('.docksal/configuration.reset.yml'));

      chdir($_SERVER['PROJECT_ROOT'] . '/' . $_SERVER['DOCROOT']);

      // Go through each available command.
      foreach ($reset_config['commands'] as $command) {
        // Condition Check
        // @todo where is this information is coming from. Drush? What's there? Is this
        if (array_key_exists('condition', $command) && $command['condition'] != '') {
          $condition = explode('.', $command['condition']);

          switch ($condition[0]) {
            case 'site':
              break;
            case 'environment':
              break;
          }
        }

        // Let the user know which command is running.
        if (!array_key_exists('name', $command) || empty($command['name'])) {
          $command['name'] = 'Missing command name';
        }

        $this->message->step_status($command['name']);

        // Run drush command if not empty.
        if (array_key_exists('drush', $command) && $command['drush'] !== '') {
          // Token Replacement.
          $environment_id = $environment_as;
          $find = ['%ah_environment_id%', '%environment_id%', '%site_id%'];

          $replace = [$environment_id, $environment_id, $site];
          $command['run'] = str_replace($find, $replace, $command['run']);
          list($cmd, $res, $out) = $this->runDrush($command['drush'] . ' 2>&1', $local_alias, $environment_as, $output->isVerbose());
        }
        // Or run another command if not empty.
        elseif (array_key_exists('run', $command) && $command['run'] !== '') {
          // Token Replacement.
          $environment_id = $environment_as;
          $site_uri = $this->getDrushAliasData($local_alias, 'uri');

          $find = ['%ah_environment_id%', '%environment_id%', '%site_id%', '%site_uri%', '%drush_alias%'];
          $replace = [$environment_id, $environment_id, $site, $site_uri, $local_alias];
          $command['run'] = str_replace($find, $replace, $command['run']);

          list($cmd, $res, $out) = $this->runner($command['run'] . ' 2>&1', $output->isVerbose());
        }
        // Otherwise skip.
        else {
          $res = 0;
        }

        if ($res > 0) {
          $this->message->error($out);
          $this->message->step_finish();
          exit(1);
        }
      }

      $this->message->step_finish();
      chdir($_SERVER['PROJECT_ROOT']);
    }
  }
}
