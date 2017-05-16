<?php

namespace Drupal\file_version;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PrivateKey;
use Drupal\Core\Site\Settings;

class FileVersion implements FileVersionInterface {

  /**
   * @var \Drupal\Core\PrivateKey
   */
  private $privateKey;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @param \Drupal\Core\PrivateKey $private_key
   */
  public function __construct(PrivateKey $private_key, ConfigFactoryInterface $config_factory) {
    $this->privateKey = $private_key;
    $this->configFactory = $config_factory;
  }

  public function addFileVersionToken(&$uri, $original_uri = NULL) {
    if (!$original_uri) {
      $original_uri = $uri;
    }

    $file_version_settings = $this->configFactory->get('file_version.settings');
    $get_parameter_name = $file_version_settings->get('get_parameter_name');

    if (
          ($file_version_settings->get('enable_image_styles') && $this->isImageStyleUri($original_uri))
      ||  $file_version_settings->get('enable_all_files')
    ) {
      $url = UrlHelper::parse($uri);

      if (empty($url['query'][$get_parameter_name])) {
        $query = array(
          $get_parameter_name => $this->getFileVersionToken($original_uri)
        );

        $uri .= (strpos($uri, '?') !== FALSE ? '&' : '?') . UrlHelper::buildQuery($query);
      }

    }
  }

  /**
   * Check if the path is image style path.
   *
   * @param $path
   * @return bool
   *
   * @todo Add config to support other image style routes like s3fs module
   */
  private function isImageStyleUri($uri) {
    $target = file_uri_target($uri);
    return strpos($target, 'styles/') === 0;
  }

  private function getWhitelistExtensions() {
    return [];
  }

  private function getBlacklistExtensions() {
    return [];
  }

  public function getFileVersionToken($uri) {
    $modified_file = NULL;
    if (file_exists($uri)) {
      $modified_file = filemtime($uri);
    }
    if (!$modified_file) {
      $modified_file = time();
    }

    return $this->getCryptedToken("$uri:$modified_file");
  }

  public function getCryptedToken($data) {
    $private_key = $this->privateKey->get();
    $hash_salt = Settings::getHashSalt();

    // Return the first eight characters.
    return substr(Crypt::hmacBase64($data, $private_key . $hash_salt), 0, 8);
  }

  /**
   * @return array
   *
   * @see file_create_url()
   */
  private function getByPassedProtocols() {
    return ['http', 'https', 'data'];
  }

  public function isProtocolByPassed($protocol) {
    $by_passed_protocols = $this->getByPassedProtocols();
    return in_array($protocol, $by_passed_protocols);
  }

}
