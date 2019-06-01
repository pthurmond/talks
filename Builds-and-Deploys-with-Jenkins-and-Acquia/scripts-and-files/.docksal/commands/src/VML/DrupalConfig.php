<?php

namespace VML;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use VML\Util\DrushCommand;

/**
 * Export class.
 */
class DrupalConfig extends DrushCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('drupal-config')
      ->setDescription('Drupal Config System.')
      ->addOption('confirm', 'y', InputOption::VALUE_NONE, 'Auto confirm actions.')
      ->addOption('environment', 'e', InputOption::VALUE_OPTIONAL, 'The environment to use.')
      ->addOption('operation', 'o', InputOption::VALUE_OPTIONAL, 'The operation to use.')
      ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'The site to use.');

    $this->title = "Drupal Configuration";
  }

  /**
   * {@inheritdoc}
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    // Get operation.
    $operation = $input->getOption('operation');
    $operation_options = ['export', 'import'];
    if (!$operation) {
      $operation = $this->message->choice('Please select the operation to preform', $operation_options);
    }
    else {
      if (!in_array($operation, $operation_options)) {
        throw new \Exception("Operation must be one of" . implode(', ', $operation_options));
      }
    }

    $this->message->success('Operation: ' . $operation);

    // Get site.
    $site = $input->getOption('site');
    $site_options = $this->getSites();
    if (!$site || !in_array($site, $site_options)) {
      $site = $this->message->choice("Please select the site to {$operation} as", $site_options, 'unitedrentals');
    }
    $this->message->success('Site: ' . $site);

    // Get environment
    $environment = $input->getOption('environment');
    $environment_options = $this->getSiteEnvironments($site);
    if (!$environment || !in_array($environment, $environment_options)) {
      $environment = $this->message->choice("Please select the environment to {$operation} as", $environment_options, 'local');
    }
    $this->message->success('Environment: ' . $environment);

    // Validate aliases.
    $alias = $this->findDrushAlias("{$site}.$environment");
    $local_alias = $this->findDrushAlias("{$site}.local");

    $confirm = $input->getOption('confirm');
    if (!$confirm) {
      if (!$this->message->confirm('Confirm ' . $operation . '?')) {
        return;
      }
    }

    // Note: cex and cim need to run first to correctly run as an environment.
    // (Not sure why – think it has to do with setting the environment
    // variable and then another command overriding it or something).
    switch ($operation) {
      case 'import':
        $this->message->step_header('Importing configuration as ' . $environment);

        $this->message->step_status('Importing Configuration');
        $this->runDrush('cim -y 2>&1', $local_alias, $environment, $output->isVerbose());

        if ($this->checkDrushAliasEnabledModule($local_alias, 'structure_sync')) {
          $this->message->step_status('Syncing Blocks');
          $this->runDrush('import-blocks --choice=full 2>&1', $local_alias, $environment, $output->isVerbose());
          $this->message->step_status('Syncing Taxonomies');
          $this->runDrush('import-taxonomies --choice=full 2>&1', $local_alias, $environment, $output->isVerbose());
        }

        if ($this->checkDrushAliasEnabledModule($local_alias, 'default_content_deploy')) {
          $this->message->step_status('Deploying Content');
          $this->runDrush('default-content-deploy-import -y 2>&1', $local_alias, $environment, $output->isVerbose());
        }

        $this->message->step_status('Clearing Cache');
        $this->runDrush('cr 2>&1', $local_alias, $environment, $output->isVerbose());
        break;

      case 'export':
        $this->message->step_header('Exporting configuration as ' . $environment);

        $this->message->step_status('Exporting Configuration');
        $this->runDrush('cex -y 2>&1', $local_alias, $environment, $output->isVerbose());

        if ($this->checkDrushAliasEnabledModule($local_alias, 'structure_sync')) {
          $this->message->step_status('Syncing Blocks');
          $this->runDrush('export-blocks --choice=full 2>&1', $local_alias, $environment, $output->isVerbose());

          $this->message->step_status('Syncing Taxonomies');
          $this->runDrush('export-taxonomies --choice=full 2>&1', $local_alias, $environment, $output->isVerbose());
        }

        $this->message->step_status('Clearing Cache');
        $this->runDrush('cr 2>&1', $local_alias, $environment, $output->isVerbose());

        break;
    }

    $this->message->step_finish();

  }
}
