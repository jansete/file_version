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
   * Don't add other fv parameter get.
   */
  public function testUniqueFileVersionParameter() {
    $uri = 'http://example.com/myfile.doc';
    $token = \Drupal::service('file_version')->getFileVersionToken($uri);
    $uri .= '?fv=' . $token;

    $url = file_create_url($uri);

    $query = parse_url($url, PHP_URL_QUERY);
    $fv_count = substr_count($query, 'fv=');

    $this->assertEquals(1, $fv_count, "Don't add other fv parameter get");
  }

  /**
   * Absolute Urls must keep absolutes.
   */
  public function testAbsoluteUrlsKeepAbsolutes() {
    $uri = 'http://example.com/myfile.doc';
    $token = \Drupal::service('file_version')->getFileVersionToken($uri);
    $uri .= '?fv=' . $token;

    $url = file_create_url($uri);
    $scheme = \Drupal::service('file_system')->uriScheme($url);

    $this->assertTrue(\Drupal::service('file_version')->isProtocolByPassed($scheme), "Absolute Urls must keep absolutes.");
  }

}
