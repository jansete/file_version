<?php

namespace Drupal\Tests\file_version\Kernel;

use Drupal\Component\Utility\UrlHelper;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group FileVersion
 */
abstract class FileVersionTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['file_version']);
  }

  /**
   * Enable file version for all files.
   */
  protected function enableAllFiles() {
    $this->config('file_version.settings')->set('enable_all_files', TRUE)->save();
  }

  /**
   * Check if URL is absolute.
   *
   * Reuse \Drupal\file_version\FileVersion::isProtocolByPassed() to check it.
   *
   * @param $url
   *
   * @return bool
   */
  protected function isUrlAbsolute($url) {
    $scheme = \Drupal::service('file_system')->uriScheme($url);
    return $scheme && \Drupal::service('file_version')->isProtocolByPassed($scheme);
  }

  /**
   * Check if URL has File Version parameter.
   *
   * @param        $url
   * @param string $get_parameter_name
   *
   * @return bool
   */
  protected function urlHasFileVersion($url, $get_parameter_name = 'fv') {
    $url_info = UrlHelper::parse($url);
    return !empty($url_info['query'][$get_parameter_name]);
  }

}
