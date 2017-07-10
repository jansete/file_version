<?php

namespace Drupal\Tests\file_version\Kernel;
use Drupal\image\Entity\ImageStyle;

/**
 * @group FileVersion
 */
class ImageStyleTest extends FileVersionTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'config', 'file', 'image', 'file_version'];

  /**
   * @var \Drupal\image\ImageStyleInterface;
   */
  private $imageStyle;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->imageStyle = ImageStyle::create(['name' => 'image_style_test', 'label' => 'Image Style Test']);
  }

  /**
   * Cover image style URL.
   */
  public function testImageStyleUrl() {
    $uri = 'public://image.png';

    $this->enableAllFiles();
    $url = $this->imageStyle->buildUrl($uri);
    $this->assertTrue($this->urlHasQueryParam($url), 'Image style has File Version for all files config.');
    $this->assertTrue($this->urlHasQueryParam($url, 'itok'), 'Image style has itok for all files config.');

    $this->disableAllFiles();
    $this->enableImageStyles();
    $url = $this->imageStyle->buildUrl($uri);
    $this->assertTrue($this->urlHasQueryParam($url), 'Image style has File Version for image styles config.');
    $this->assertTrue($this->urlHasQueryParam($url, 'itok'), 'Image style has itok for image styles config.');
  }

  /**
   * Cover extensions whitelist.
   */
  public function testExtensionsWhitelist() {
    $image_uri = 'public://image.png';
    $doc_uri = 'http://example.com/myfile.doc';
    $pdf_uri = 'http://example.com/myfile.pdf';

    $this->enableImageStyles();

    $this->config('file_version.settings')->set('extensions_whitelist', 'doc')->save();
    $image_url = $this->imageStyle->buildUrl($image_uri);
    $doc_url = file_create_url($doc_uri);
    $pdf_url = file_create_url($pdf_uri);

    $this->assertTrue($this->urlHasQueryParam($image_url), 'Image style has File Version when extensions whitelist is setted: single value.');
    $this->assertTrue($this->urlHasQueryParam($image_url, 'itok'), 'Image style has when extensions whitelist is setted: single value.');
    $this->assertTrue($this->urlHasQueryParam($doc_url), 'Whitelisted extension has File Version: single value.');
    $this->assertFalse($this->urlHasQueryParam($pdf_url), "Other extensions don't have File Version: single value.");

    $this->config('file_version.settings')->set('extensions_whitelist', 'doc, xml')->save();
    $image_url = $this->imageStyle->buildUrl($image_uri);
    $doc_url = file_create_url($doc_uri);
    $pdf_url = file_create_url($pdf_uri);

    $this->assertTrue($this->urlHasQueryParam($image_url), 'Image style has File Version when extensions whitelist is setted: list.');
    $this->assertTrue($this->urlHasQueryParam($image_url, 'itok'), 'Image style has when extensions whitelist is setted: list.');
    $this->assertTrue($this->urlHasQueryParam($doc_url), 'Whitelisted extension has File Version: list.');
    $this->assertFalse($this->urlHasQueryParam($pdf_url), "Other extensions don't have File Version: list.");
  }

}
