<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_RecordBrowser_Recordset_Field_Special_VisitedOn extends Utils_RecordBrowser_Recordset_Field {
	public static function desc($tab = null, $name = null) {
		return [
				'id' => 'visited_on',
				'field' => _M('Visited on'),
				'type' => 'visited_on',
				'active' => true,
				'visible' => false,
				'export' => true,
				'processing_order' => -600,
		];
	}
	
	public static function defaultDisplayCallback($record, $nolink = false, $desc = null, $tab = null) {
		$value = DB::GetOne("SELECT
								MAX(visited_on)
							FROM 
								{$tab}_recent
							WHERE 
								{$tab}_id=%d AND
								user_id=%d", [$record[':id'], Acl::get_user()]);
		
		return Utils_RecordBrowser_Recordset_Field_Date::getDateValues()[$value]?? Base_RegionalSettingsCommon::time2reg($value);
	}
	
	public function getName() {
		return _M('Visited on');
	}
	
	public function validate(Utils_RecordBrowser_Recordset_Query_Crits_Basic $crits, $value) {
		$critsCheck = clone $crits;
		
		$crit_value = Base_RegionalSettingsCommon::reg2time($critsCheck->getValue()->getValue(), false);
		
		$critsCheck->getValue()->setValue(date('Y-m-d H:i:s', $crit_value));
		
		return parent::validate($critsCheck, $value);
	}
	
	public function getSqlId() {
		return false;
	}
	
	public function getSqlType() {
		return false;
	}
	
	public function getSqlOrder($direction) {
		return ' (SELECT 
					MAX(visited_on) 
				FROM '.
					$this->getTab().'_recent 
				WHERE '.
					$this->getTab().'_id=' . $this->getRecordset()->getDataTableAlias().' . id AND 
					user_id='.Acl::get_user() . ') ' . $direction;
	}
	
	public function processGet($values, $options = []) {
		return [];
	}
}
