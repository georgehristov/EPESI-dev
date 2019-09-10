<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_RecordBrowser_BrowseMode_Favourites extends Utils_RecordBrowser_BrowseMode {
	protected static $key = 'favorites';
	protected static $label = 'Favourites';
	
	public function isAvailable(Utils_RecordBrowser_Recordset $recordset) {
		return $recordset->getProperty('favorites');
	}
	
	public function crits() {
		return [':fav' => true];
	}
	
	public function userSettings() {
		$ret = [];
		foreach (Utils_RecordBrowserCommon::list_installed_recordsets() as $tab => $caption) {
			$recordset = Utils_RecordBrowser_Recordset::create($tab);
			
			if (! $recordset->getUserAccess('browse') || ! $this->isAvailable($recordset)) continue;

			$ret[] = [
					'name' => $tab . '_auto_fav',
					'label' => $caption,
					'type' => 'select',
					'values' => [
							__('Disabled'),
							__('Enabled')
					],
					'default' => 0
			];
		}
		
		return $ret? array_merge([
				[
						'name' => 'header_auto_fav',
						'label' => __('Automatically add to favorites records created by me'),
						'type' => 'header'
				]
		], $ret): [];
	}
	
	public function moduleSettings(Utils_RecordBrowser_Recordset $recordset) {
		return [
				[
						'name' => 'favorites',
						'label' => __('Favorites'),
						'type' => 'select',
						'values' => [
								__('Disabled'),
								__('Enabled')
						],
						'default' => $this->isAvailable($recordset)? 1: 0
				]
		];
	}
	
	public function process($values, $mode, $tab) {
		switch ($mode) {
			case 'added':
				if (Base_User_SettingsCommon::get('Utils_RecordBrowser', $tab . '_auto_fav')) {
					DB::Execute("INSERT INTO {$tab}_favorite (user_id, {$tab}_id) VALUES (%d, %d)", [Acl::get_user(), $values[':id']]);
				}
			break;
			case 'destroyed':
				DB::Execute('DELETE FROM ' . $tab . '_favorite WHERE ' . $tab . '_id = %d', [$values[':id']]);
			break;
		}
		
		return $values;
	}
	
	public function recordActions(Module $module, Utils_RecordBrowser_Recordset_Record $record, $mode) {
		if (! $this->isAvailable($record->getRecordset())) return;
		
		if (in_array($mode, ['add', 'history', 'browse'])) return;
		
		return Utils_RecordBrowserCommon::get_fav_button($record->getTab(), $record[':id']);
	}
	
}



