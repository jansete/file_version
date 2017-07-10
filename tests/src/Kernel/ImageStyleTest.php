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
    $path = 'public://image.png';

    $this->enableAllFiles();
    $url = $this->imageStyle->buildUrl($path);
    $this->assertTrue($this->urlHasQueryParam($url), 'Image style have File Version for all files config.');
    $this->assertTrue($this->urlHasQueryParam($url, 'itok'), 'Image style have itok for all files config.');

    $this->disableAllFiles();
    $this->enableImageStyles();
    $url = $this->imageStyle->buildUrl($path);
    $this->assertTrue($this->urlHasQueryParam($url), 'Image style have File Version for image styles config.');
    $this->assertTrue($this->urlHasQueryParam($url, 'itok'), 'Image style have File Version for image styles config.');
  }

}
