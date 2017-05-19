<?php

namespace Drupal\file_version;

interface FileVersionInterface {

  public function addFileVersionToken(&$uri, $original_uri);

  public function parseCommaSeparatedList($string);

  public function getFileVersionToken($uri);

  public function getCryptedToken($data);

  public function isProtocolByPassed($protocol);

  public function getInvalidQueryParameterNames();

}
