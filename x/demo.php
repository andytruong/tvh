<?php

use Drupal\vina_migrate\TruyenCuaTuiNet\Source\ChapterSource;

require_once __DIR__ . '/vendor/autoload.php';

$source = new ChapterSource();
$source->performRewind();
while ($row = $source->getNextRow()) {
    dump($row['title']);
}
