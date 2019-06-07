<?php
/* --------------------------------------------------------------
  GProtectorLogConnectorInterface.inc.php 2019-06-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

interface GProtectorLogConnectorInterface
{
	public function log($p_message, $p_group, $p_filename, $p_severity, $p_error_type, $p_message_details);
}