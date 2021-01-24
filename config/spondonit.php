<?php

return [
	'module_manager_model' => App\Models\InfixModuleManager::class,
	'module_manager_table' => 'infix_module_managers',

	'settings_model' => App\Models\SmGeneralSettings::class,
	'module_model' => Nwidart\Modules\Facades\Module::class,
	'settings_table' => 'sm_general_settings'
];