<?php

define('ROOT_DIR', __DIR__ . '/');
define('CONTROLLER_DIR', 'Controller/');
define('CONTROLLER_SUFFIX', 'Controller');
define('MODULE_LIST', 'Home,Admin');
define('DEFAULT_MODULE', 'Home');

require __DIR__ . '/System/Lib/XlController.php';
require __DIR__ . '/System/Lib/Dispacker.php';

Dispacher::forward();

