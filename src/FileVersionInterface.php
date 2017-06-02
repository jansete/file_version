<?php

namespace Drupal\file_version;

/**
 * Interface FileVersionInterface
 *
 * @package Drupal\file_version
 */
interface FileVersionInterface {

  /**
   * Method that add file version token.
   *
   * @param $uri
   * @param $original_uri
   */
  public function addFileVersionToken(&$uri, $original_uri);

  /**
   * Method that parse a comma separated string to convert into an array.
   *
   * @param $string
   *
   * @return array
   */
  public function parseCommaSeparatedList($string);

  /**
   * @param $uri
   *
   * @return mixed
   */
  public function getFileVersionToken($uri);

  /**
   * @param $data
   *
   * @return mixed
   */
  public function getCryptedToken($data);

  /**
   * @param $protocol
   *
   * @return mixed
   */
  public function isProtocolByPassed($protocol);

  /**
   * @return mixed
   */
  public function getInvalidQueryParameterNames();

}
