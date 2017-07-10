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
   * Disable file version for all files.
   */
  protected function disableAllFiles() {
    $this->config('file_version.settings')->set('enable_all_files', FALSE)->save();
  }

  /**
   * Enable file version for image styles.
   */
  protected function enableImageStyles() {
    $this->config('file_version.settings')->set('enable_image_styles', TRUE)->save();
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
   * Check if URL has query parameter.
   *
   * @param        $url
   * @param string $query_param
   *
   * @return bool
   */
  protected function urlHasQueryParam($url, $query_param = 'fv') {
    $url_info = UrlHelper::parse($url);
    return !empty($url_info['query'][$query_param]);
  }

}
