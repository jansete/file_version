<?php

namespace Drupal\Tests\file_version\Kernel;

use Drupal\Component\Utility\UrlHelper;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group FileVersion
 */
class FileVersionTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'config', 'file', 'file_version'];

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
  private function enableAllFiles() {
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
  private function isUrlAbsolute($url) {
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
  private function urlHasFileVersion($url, $get_parameter_name = 'fv') {
    $url_info = UrlHelper::parse($url);
    return !empty($url_info['query'][$get_parameter_name]);
  }

  /**
   * Don't add other fv parameter get.
   */
  public function testUniqueFileVersionParameter() {
    $this->enableAllFiles();

    $uri = 'http://example.com/myfile.doc';
    $token = \Drupal::service('file_version')->getFileVersionToken($uri);
    $uri .= '?fv=' . $token;

    $url = file_create_url($uri);

    $query = parse_url($url, PHP_URL_QUERY);
    $fv_count = substr_count($query, 'fv=');

    $this->assertEquals(1, $fv_count, 'Only one File Version query parameter in the URL.');
  }

  /**
   * Cover absolute URLs. E.g.: http://example.com/myfile.doc.
   */
  public function testAbsoluteUrl() {
    $this->enableAllFiles();
    $uri = 'http://example.com/myfile.doc';
    $url = file_create_url($uri);
    $this->assertTrue($this->isUrlAbsolute($url), 'Absolute URL keep absolute.');
    $this->assertTrue($this->urlHasFileVersion($url), 'Absolute URL have File Version');
  }

  /**
   * Cover scheme URIs. E.g: public://myfile.doc.
   */
  public function testSchemeUri() {
    $this->enableAllFiles();
    $scheme_uri = 'public://myfile.doc';
    $url = file_create_url($scheme_uri);
    $this->assertTrue($this->isUrlAbsolute($url), 'Scheme URI is converted to absolute URL.');
    $this->assertTrue($this->urlHasFileVersion($url), 'Scheme URI have File Version');
  }

  /**
   * Cover relative URLs. E.g.: modules/custom/mymodule/myfile.doc.
   */
  public function testRelativeUrl() {
    $this->enableAllFiles();
    $relative_uri = 'modules/custom/mymodule/myfile.doc';
    $url = file_create_url($relative_uri);
    $this->assertTrue($this->isUrlAbsolute($url), 'Relative URL is converted to absolute URL.');
    $this->assertTrue($this->urlHasFileVersion($url), 'Relative URL have File Version');
  }

  /**
   * Cover root relative URLs. E.g.: /modules/custom/mymodule/myfile.doc.
   */
  public function testRootRelativeUrl() {
    $this->enableAllFiles();
    $root_relative_uri = '/modules/custom/mymodule/myfile.doc';
    $url = file_create_url($root_relative_uri);
    $this->assertTrue(strpos($url, $root_relative_uri) === 0, 'Root relative URL keep root relative.');
    $this->assertTrue($this->urlHasFileVersion($url), 'Root relative URL have File Version');
  }

}
