<?php

/**
 * @file
 * Interface FileVersionInterface
 */

namespace Drupal\file_version;

/**
 * Interface FileVersionInterface
 *
 * @package Drupal\file_version
 */
interface FileVersionInterface {

  /**
   * Implements the logic to when add file version token.
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
   * Return file version token.
   *
   * @param $uri
   *
   * @return string
   */
  public function getFileVersionToken($uri);

  /**
   * Crypt data to get the final token.
   *
   * @param $data
   *
   * @return string
   */
  public function getCryptedToken($data);

  /**
   * Determine if the current protocol is by passed.
   *
   * @param $protocol
   *
   * @return boolean
   */
  public function isProtocolByPassed($protocol);

  /**
   * Define an array list of query parameters that can make conflicts like q,
   * file, etc.
   *
   * @return array
   */
  public function getInvalidQueryParameterNames();

}
