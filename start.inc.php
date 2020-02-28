<?php
/* --------------------------------------------------------------
  start.inc.php 2019-06-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

use GProtector\GProtector;

if (defined('E_DEPRECATED')) {
    error_reporting(
        E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING
    );
} else {
    error_reporting(
        E_ALL & ~E_NOTICE & ~E_STRICT & ~E_CORE_ERROR & ~E_CORE_WARNING
    );
}

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/GProtectorLogConnectorInterface.inc.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/GProtector.inc.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/Filter.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/FilterCollection.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/FilterReader.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/Key.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/Method.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/ScriptName.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/ScriptNameCollection.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/Severity.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/Variable.php');
require_once(GAMBIO_PROTECTOR_CLASSES_DIR . '/VariableCollection.php');

$gprotector = new GProtector();
$gprotector->start();
unset($gprotector);
