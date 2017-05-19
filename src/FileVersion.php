<?php

namespace Drupal\file_version;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @param \Drupal\Core\PrivateKey $private_key
   */
  public function __construct(PrivateKey $private_key, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->privateKey = $private_key;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * @param      $uri
   * @param null $original_uri
   */
  public function addFileVersionToken(&$uri, $original_uri = NULL) {
    if (!$original_uri) {
      $original_uri = $uri;
    }

    $file_version_settings = $this->configFactory->get('file_version.settings');
    $get_parameter_name = $file_version_settings->get('get_parameter_name');
    $whitelist_extensions = $this->getWhitelistExtensions();
    $extension = pathinfo($uri, PATHINFO_EXTENSION);

    if (
          ($file_version_settings->get('enable_image_styles') && $this->isImageStyleUri($original_uri))
      ||  $file_version_settings->get('enable_all_files')
      ||  in_array($extension, $whitelist_extensions)
    ) {
      $blacklist_extensions = $this->getBlacklistExtensions();

      if (!in_array($extension, $blacklist_extensions)) {
        $url = UrlHelper::parse($uri);

        if (empty($url['query'][$get_parameter_name])) {
          $query = array(
            $get_parameter_name => $this->getFileVersionToken($original_uri)
          );

          $uri .= (strpos($uri, '?') !== FALSE ? '&' : '?') . UrlHelper::buildQuery($query);
        }
      }
    }
  }

  /**
   * Check if the path is image style path.
   *
   * @param $path
   * @return bool
   */
  private function isImageStyleUri($uri) {
    $image_styles_url_prefixes = $this->getImageStylesUrlPrefixes();
    $target = file_uri_target($uri);
    if ($target) {
      // Escape all '/' chars to compose correct regular expression
      $image_styles_url_prefixes = array_map(function($value) {
        return preg_quote($value, '/');
      }, $image_styles_url_prefixes);

      $pattern = '/[^' . implode('|^', $image_styles_url_prefixes) . ']/';
      return preg_match($pattern, $target);
    }
    return FALSE;
  }

  private function getWhitelistExtensions() {
    $extension_whitelist = $this->configFactory->get('file_version.settings')->get('extensions_whitelist');
    return $this->parseCommaSeparatedList($extension_whitelist);
  }

  private function getBlacklistExtensions() {
    $extension_blacklist = $this->configFactory->get('file_version.settings')->get('extensions_blacklist');
    return $this->parseCommaSeparatedList($extension_blacklist);
  }

  public function parseCommaSeparatedList($string) {
    $items = explode(',', $string);
    $items = array_map('trim', $items);
    return array_filter($items, function($value) {
      return $value !== "";
    });
  }

  private function getImageStylesUrlPrefixes() {
    $image_styles_url_prefixes = ['/styles/'];
    $raw_config_prefixes = $this->configFactory->get('file_version.settings')->get('image_styles_url_prefix');
    $config_prefixes = $this->parseLineSeparatedList($raw_config_prefixes);
    $prefixes = array_merge($image_styles_url_prefixes, $config_prefixes);
    return $this->formatImageStylesUrlPrefiex($prefixes);
  }

  private function parseLineSeparatedList($string) {
    return explode("\r\n", $string);
  }

  private function formatImageStylesUrlPrefiex(array $prefixes) {
    return array_map(function($value) {
      $value = trim($value);
      if (strpos($value, '/') === 0) {
        $value = substr($value, 1);
      }
      return $value;
    }, $prefixes);
  }

  /**
   * @param $uri
   *
   * @return string
   */
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

  /**
   * @param $data
   *
   * @return string
   */
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

  /**
   * @param $protocol
   *
   * @return bool
   */
  public function isProtocolByPassed($protocol) {
    $by_passed_protocols = $this->getByPassedProtocols();
    return in_array($protocol, $by_passed_protocols);
  }

  /**
   * @return array
   */
  public function getInvalidQueryParameterNames() {
    $invalid_params = ['q', 'itok', 'file'];
    $this->moduleHandler->invokeAll('file_version_invalid_params', [$invalid_params]);
    return $invalid_params;
  }

}
