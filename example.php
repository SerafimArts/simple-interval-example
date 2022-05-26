<?php

require __DIR__ . '/vendor/autoload.php';

setInterval(function () {
    echo ' wake ';
}, .1);

setInterval(function () {
    echo ' up ';
}, .2);

setInterval(function () {
    echo ' Neo ';
}, .3);