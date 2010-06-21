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
 * SQLIGeoloc fetch functions
 */
class SQLIGeolocFunctionCollection
{
	/**
	 * Returns Geoloc infos by IP, depending requested mode
	 * @param string $mode Expecting 'city' or 'country'
	 * @param string $ip IP Address to fetch infos for. If not provided, will try to guess it.
	 */
    public static function getInfoByIP($mode=SQLIGeolocObject::MODE_CITY, $ip=null)
	{
		try
		{
			if(!$ip) // Get remote IP address if IP not given
				$ip = eZFunctionHandler::execute('sqligeoloc', 'remote_ip', array());
				
			switch($mode)
			{
				case SQLIGeolocObject::MODE_CITY :
					$infos = SQLIIPGroupCity::fetchByIP($ip);
					break;
				case SQLIGeolocObject::MODE_COUNTRY :
					$infos = SQLIIPGroupCountry::fetchByIP($ip);
					break;
				default:
					throw new InvalidArgumentException("Invalid mode '$mode'. Expecting 'city' or 'country'");
					break;
			}
			
			return array('result' => $infos);
		}
		catch(Exception $e)
		{
			$errMsg = $e->getMessage();
			eZDebug::writeError($errMsg, 'SQLIGeoloc');
			return array('error' => $errMsg);
		}
	}
	
	/**
	 * Returns visitor IP address
	 */
	public static function getRemoteIP()
	{
		$ip = null;
		if(isset($_SERVER['X-Forwarded-For']))
		  $ip = $_SERVER['X-Forwarded-For'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			throw new InvalidArgumentException('IP Address not provided and/or not available');
			
		return array('result' => $ip);
	}
}