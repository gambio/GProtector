<?php
/* --------------------------------------------------------------
  G-Protector v1.2
  Gambio GmbH
  http://www.gambio.de
  Copyright (c) 2017 Gambio GmbH
  --------------------------------------------------------------
*/

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
