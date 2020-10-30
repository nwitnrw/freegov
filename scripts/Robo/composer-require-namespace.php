<?php

/**
 * @file
 * This file is for Bitbucket pipelines.
 */

$file = 'composer.json';
$data = json_decode(file_get_contents($file), TRUE);
$data['autoload-dev']['psr-4'] += [
  'degov\\Scripts\\Robo\\' => 'docroot/profiles/contrib/degov/scripts/Robo',
];
file_put_contents('composer.json', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
