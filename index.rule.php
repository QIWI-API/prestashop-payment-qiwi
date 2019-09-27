<?php

if (!isset($iterator)) {
    exit;
}

use WannaBePro\Composer\Plugin\Release\Mapper\FiltredFile;
use WannaBePro\Composer\Plugin\Release\Mapper\TargetIterator;
use WannaBePro\Composer\Plugin\Release\Mapper\File;

/** @var TargetIterator $iterator */

$iterator->getInnerIterator()->append(
    new ArrayIterator(
        array_map(
            function ($source) {
                $file = new FiltredFile(__DIR__ . DIRECTORY_SEPARATOR . 'index.php', 'index.php');
                $file->setTarget($source);
                return $file;
            },
            array_unique(
                array_reduce(
                    array_values(iterator_to_array($iterator->getMapper()->getIterator())),
                    function ($carry, File $item) {
                        $path = '';
                        return array_merge($carry, array_reduce(
                            array_filter(explode('/', str_replace(['/', '\\'], '/', dirname($item)))),
                            function ($carry, $item) use (&$path) {
                                $path .= $item . '/';
                                $carry[] = $path . 'index.php';
                                return $carry;
                            },
                            []
                        ));
                    },
                    []
                )
            )
        )
    )
);
