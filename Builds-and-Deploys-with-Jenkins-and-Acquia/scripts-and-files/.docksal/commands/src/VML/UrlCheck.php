<?php

namespace VML;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VML\Util\DocksalCommand;

class UrlCheck extends DocksalCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('url-check')
      ->setDescription('URL Checker.');

    $this->title = 'URL Checker';
  }

  /**
   * {@inheritdoc}
   */
  protected function exec(InputInterface $input, OutputInterface $output) {
    $config = Yaml::parse(file_get_contents('.docksal/configuration.urlcheck.yml'));

    $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER         => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_AUTOREFERER    => true,
      CURLOPT_CONNECTTIMEOUT => 120,
      CURLOPT_TIMEOUT        => 120,
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_NOBODY         => true
    );

    $this->message->step_header('Checking URLs');

    $base_url = 'https://web';
    foreach ($config['urls'] as $url) {
      $this->message->step_status('Checking: ' . $url);
      $ch      = curl_init($base_url .  $url );
      curl_setopt_array( $ch, $options );
      curl_exec( $ch );
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close( $ch );

      if($httpcode != 200) {
        $this->message->error('HTTP Code: ' . $httpcode . ' for ' . $url);
        exit(1);
      }
      $this->message->success($httpcode);
    }

    $this->message->step_finish();
  }
}
