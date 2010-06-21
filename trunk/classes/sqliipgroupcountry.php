<?php
//
// SOFTWARE NAME: SQLi Geoloc
// SOFTWARE RELEASE: @@@VERSION@@@
// COPYRIGHT NOTICE: Copyright (C) 2010 SQLi Agency
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

/**
 * SQLIGeoloc info object for country level location
 */
class SQLIIPGroupCountry extends SQLIGeolocObject implements ISQLIGeoloc
{
	/**
	 * Persistent object definition
	 * @return array
	 */
	public static function definition(){
		return array(
			'fields'				=> array(
				'ip_start'		=> array(
					'name'		=> 'ip_start',
					'datatype'	=> 'integer',
					'default'	=> 'NULL',
					'required'	=> 'true'
				),
				'ip_cidr'		=> array(
					'name'		=> 'ip_cidr',
					'datatype'	=> 'string',
					'default'	=> 'NULL',
					'required'	=> 'true'
				),
				'country_code'	=> array(
					'name'		=> 'country_code',
					'datatype'	=> 'string',
					'default'	=> 'NULL',
					'required'	=> 'true'
				),
				'country_name'	=> array(
					'name'		=> 'country_name',
					'datatype'	=> 'string',
					'default'	=> 'NULL',
					'required'	=> 'true'
				)
			),
			'keys'					=> array('ip_start'),
			'function_attributes'	=> array(),
			'class_name'			=> 'SQLIIPGroupCountry',
			'name'					=> 'ip_group_country'
		);
	}
	
	/**
     * Fetches infos by IP
	 */
	public static function fetchByIP($ip)
	{
		return parent::fetchByIP(self::definition(), $ip);
	}
	
	/**
	 * Handles a CSV line as array returned by fgetcsv
	 * @param array $csvLine
	 */
	public static function handleCSVLine(array $csvLine)
	{
		list($ipStart, $ipCidr, $countryCode, $countryName) = $csvLine;
		$row = array(
			'ip_start'			=> $ipStart,
			'ip_cidr'			=> $ipCidr,
			'country_code'		=> $countryCode,
			'country_name'		=> $countryName
		);
		
		$entry = self::updateIPInfo(self::definition(), $row);
	}
}