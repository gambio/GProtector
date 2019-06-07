<?php
/* --------------------------------------------------------------
  GXLogConnector.inc.php 2019-06-07
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2019 Gambio GmbH
  Released under the GNU General Public License (Version 2)
  [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------*/

class GXLogConnector implements GProtectorLogConnectorInterface
{
	public function log($p_message, $p_group, $p_filename, $p_severity, $p_error_type, $p_message_details)
	{
		if(class_exists('LogControl'))
		{
			LogControl::get_instance()
			          ->notice($p_message, $p_group, $p_filename, $p_severity, $p_error_type, 0, $p_message_details);
			LogControl::get_instance()->write_stack(array('security'));
			
			return true;
		}
		
		return false;
	}
}

$this->log_connectors_array[] = new GXLogConnector();
