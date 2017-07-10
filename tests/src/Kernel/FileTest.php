<?php

namespace Drupal\Tests\file_version\Kernel;

/**
 * @group FileVersion
 */
class FileTest extends FileVersionTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'config', 'file', 'file_version'];

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

  /**
   * Check if URLs works with custom File Version query parameter.
   */
  public function testCustomFileVersionParameter() {
    $this->enableAllFiles();
    $custom_query_parameter = 'jv';
    $this->config('file_version.settings')->set('get_parameter_name', $custom_query_parameter)->save();
    $uri = 'http://example.com/myfile.doc';
    $url = file_create_url($uri);
    $this->assertTrue($this->urlHasFileVersion($url, $custom_query_parameter), 'URL works with custom query parameter.');
  }

}
