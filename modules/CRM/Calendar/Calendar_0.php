<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

class CRM_Calendar extends Module {

	public function body() {
		print($this->pack_module('CRM/Filters',null,null,'xxx')->get());
		$c = $this->init_module('Utils/Calendar',array('Tests/Calendar/Event',array('default_view'=>Base_User_SettingsCommon::get('CRM_Calendar','default_view'),
			'first_day_of_week'=>Utils_PopupCalendarCommon::get_first_day_of_week(),
			'start_day'=>Base_User_SettingsCommon::get('CRM_Calendar','start_day'),
			'end_day'=>Base_User_SettingsCommon::get('CRM_Calendar','end_day'),
			'interval'=>Base_User_SettingsCommon::get('CRM_Calendar','interval'),
			)));
		$this->display_module($c);
	}
	
	public function applet() {
	
	}

	public function caption() {
		return "Calendar";
	}
}
?>
