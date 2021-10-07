<?php declare(strict_types = 1);
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Update controller for "Geographical maps" administration screen.
 */
class CControllerGeomapsUpdate extends CController {

	protected function init(): void {
		$this->disableSIDValidation();
	}

	protected function checkInput(): bool {
		$fields = [
			'geomaps_tile_provider'		=> 'required|db config.geomaps_tile_provider',
			'geomaps_tile_url'			=> 'required|db config.geomaps_tile_url|not_empty',
			'geomaps_max_zoom'			=> 'required|db config.geomaps_max_zoom',
			'geomaps_attribution'		=> 'db config.geomaps_attribution'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			switch ($this->GetValidationError()) {
				case self::VALIDATION_ERROR:
					$response = new CControllerResponseRedirect(
						(new CUrl('zabbix.php'))
							->setArgument('action', 'geomaps.edit')
							->getUrl()
					);

					$response->setFormData(array_filter($this->getInputAll()) + [
						'geomaps_tile_provider' => '',
						'geomaps_tile_url' => '',
						'geomaps_max_zoom' => 0
					]);
					CMessageHelper::setErrorTitle(_('Cannot update configuration'));
					$this->setResponse($response);
					break;

				case self::VALIDATION_FATAL_ERROR:
					$this->setResponse(new CControllerResponseFatal());
					break;
			}
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		return $this->checkAccess(CRoleHelper::UI_ADMINISTRATION_GENERAL);
	}

	protected function doAction(): void {
		$settings = [
			CSettingsHelper::GEOMAPS_TILE_PROVIDER => $this->getInput('geomaps_tile_provider'),
			CSettingsHelper::GEOMAPS_TILE_URL => $this->getInput('geomaps_tile_url'),
			CSettingsHelper::GEOMAPS_MAX_ZOOM => $this->getInput('geomaps_max_zoom'),
			CSettingsHelper::GEOMAPS_ATTRIBUTION => $this->getInput('geomaps_attribution', '')
		];

		$result = API::Settings()->update($settings);

		$response = new CControllerResponseRedirect(
			(new CUrl('zabbix.php'))
				->setArgument('action', 'geomaps.edit')
				->getUrl()
		);

		if ($result) {
			CMessageHelper::setSuccessTitle(_('Configuration updated'));
		}
		else {
			$response->setFormData($this->getInputAll());
			CMessageHelper::setErrorTitle(_('Cannot update configuration'));
		}

		$this->setResponse($response);
	}
}
