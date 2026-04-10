<?php
$arUrlRewrite = array(
  array(
    'CONDITION' => '#^/projects/([a-zA-Z0-9_-]+)/?$#',
    'RULE' => 'ELEMENT_CODE=$1',
    'ID' => '',
    'PATH' => '/projects/detail/index.php',
    'SORT' => 100,
  ),
  array(
    'CONDITION' => '#^/reference/([a-zA-Z0-9_-]+)/?$#',
    'RULE' => 'code=$1',
    'ID' => '',
    'PATH' => '/reference/detail/index.php',
    'SORT' => 110,
  ),
);
