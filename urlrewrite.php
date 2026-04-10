<?php
$arUrlRewrite = array(
  array(
    'CONDITION' => '#^/projects/([a-zA-Z0-9_-]+)/?$#',
    'RULE' => 'ELEMENT_CODE=$1',
    'ID' => '',
    'PATH' => '/projects/detail/index.php',
    'SORT' => 100,
  ),
);
