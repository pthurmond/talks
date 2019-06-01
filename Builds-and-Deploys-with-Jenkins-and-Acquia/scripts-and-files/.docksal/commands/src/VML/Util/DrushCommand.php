<?php

namespace VML\Util;

use Drupal\Component\Utility\NestedArray;

/**
 * Export class.
 */
class DrushCommand extends DocksalCommand {

  /**
   * Drush version.
   *
   * @var string
   */
  protected $drushVersion;

  /**
   * All available Drush aliases.
   *
   * @var array
   */
  protected $drushAliases;

  /**
   * Enabled modules by alias.
   *
   * @var array
   */
  protected $enabledModules;

  /**
   * Run a Drush command.
   *
   * @param string $command
   *   The Drush command to run.
   * @param null|string $alias
   *   The site alias
   * @param null|string $environment
   *   Run the Drush command as a certain environment.
   * @param bool $passthru
   *   Whether to passthru the command or capture the output.
   *
   * @return array
   *   The response from the drush command.
   */
  public function runDrush($command, $alias = NULL, $environment = NULL, $passthru = FALSE) {
    // Attach alias to command.
    if (is_string($alias)) {
      $alias = '@' . str_replace('@', '', $alias);
      $command = $alias . ' ' . $command;

      // Get URI from the passed-in alias.
      if ($uri = $this->getDrushAliasData($alias, 'uri')) {
        $command = "$command --uri={$uri}";
      }
    }

    // Add drush to command.
    $command = '/var/www/vendor/drush/drush/drush ' . $command;

    // Set the site's environment before the command is run.
    if (!is_null($environment)) {
      $command = "SITE_ENVIRONMENT={$environment} $command";
      $_SERVER['SITE_ENVIRONMENT'] = $environment;
      $_ENV['SITE_ENVIRONMENT'] = $environment;
    }

    chdir($_SERVER['PROJECT_ROOT'] . '/' . $_SERVER['DOCROOT']);
    $return = $this->runner($command, $passthru);
    chdir($_SERVER['PROJECT_ROOT']);
    return $return;
  }

  /**
   * A list of all available Drush aliases.
   *
   * @return array|mixed
   *   The array of drush alias information, keyed by alias key.
   */
  public function getDrushAliases() {
    if (!is_array($this->drushAliases)) {
      list($cmd, $res, $out) = $this->runDrush('sa --format=php');
      $this->drushAliases = unserialize(implode('', $out));
    }
    return $this->drushAliases;
  }

  /**
   * Find the most applicable Drush alias from partial.
   *
   * @param string $search
   *   Dev, Test, Live.
   *
   * @return string
   *   The formatted Drush alias.
   */
  public function findDrushAlias($search) {
    $matches = array_filter(array_keys($this->getDrushAliases()), function ($var) use ($search) {
      return preg_match("/\b$search\b/i", $var);
    });

    if (count($matches) == 0) {
      $this->message->error("drush alias '$search' not found. Use drush sa to view available aliases.");
      exit(1);
    }

    if (count($matches) > 1) {
      $this->message->error("drush alias '$search' not unique. Use drush sa to view available aliases.");
      exit(1);
    }

    return array_shift($matches);
  }

  /**
   * Gets the alias' site.
   *
   * @param string $alias
   *   The Drush alias.
   *
   * @return string
   *   The site of the alias.
   */
  public function getDrushAliasSite($alias) {
    return explode('.', str_replace('@', '', $alias), 2)[0];
  }

  /**
   * Gets the alias' environment.
   *
   * @param string $alias
   *   The Drush alias.
   *
   * @return string
   *   The environment of the alias.
   */
  public function getDrushAliasEnvironment($alias) {
    return explode('.', $alias, 2)[1];
  }

  /**
   * Get specific data from a Drush alias.
   *
   * @param string $alias
   *   The Drush alias.
   * @param string|string[] $search
   *   Either a key or an array of keys to find the alias's specific data value.
   *
   * @return mixed|null
   *   The key aliases information if found. Null otherwise.
   */
  public function getDrushAliasData($alias, $search) {
    // Prepare array search value.
    if (!is_array($search)) {
      $search = [$search];
    }
    // Prepend alias to beginning of the array.
    array_unshift($search, $alias);
    return NestedArray::getValue($this->getDrushAliases(), $search);
  }

  /**
   * Get enabled modules from a specific alias.
   *
   * @param string $alias
   *   The Drush alias.
   *
   * @return mixed
   *   An array of enabled module IDs.
   */
  public function getDrushAliasEnabledModules($alias) {
    $alias = '@' . str_replace('@', '', $alias);
    if (!is_array($this->enabledModules) || !array_key_exists($alias, $this->enabledModules)) {
      $command = 'pm-list --status=enabled --format=php';
      list($cmd, $res, $out) = $this->runDrush($command, $alias, 'local');
      $this->enabledModules[$alias] = unserialize(implode('', $out));
    }
    return $this->enabledModules[$alias];
  }

  /**
   * Checks if a module is installed.
   *
   * @param string $alias
   *   The Drush alias.
   * @param string $module
   *   The module name.
   *
   * @return bool
   *   True if module is enabled. False otherwise.
   */
  public function checkDrushAliasEnabledModule($alias, $module) {
    return array_key_exists($module, $this->getDrushAliasEnabledModules($alias));
  }

  /**
   * A list of all available site keys.
   *
   * @return string[]
   *   An array of site keys.
   */
  public function getSites() {
    return array_unique(array_map(function ($key) {
      return $this->getDrushAliasSite($key);
    }, array_keys($this->getDrushAliases())));
  }

  /**
   * Get a site's environment keys from its Drush aliases.
   *
   * @param string
   *   The site key.
   *
   * @return string[]
   *   An array of a site's environment options.
   */
  public function getSiteEnvironments($site) {
    $matches = array_filter(array_keys($this->getDrushAliases()), function ($var) use ($site) {
      return preg_match("/@\b$site\b/i", $var);
    });

    return array_unique(array_map(function ($key) {
      return explode('.', $key, 2)[1];
    }, $matches));
  }

  /**
   * Return Drush version.
   *
   * @return string[]
   *   An array of site keys.
   */
  public function getDrushVersion() {
    $command = 'version';
    $drushVersionOutput = $this->runDrush($command);
    $parsedDrushVersion = explode(' : ', $drushVersionOutput[2][0])[1];
    $this->drushVersion = $parsedDrushVersion;
    return $this->drushVersion;
  }

}



