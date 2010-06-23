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
 * Abstract class for geoloc infos
 */
abstract class SQLIGeolocObject extends eZPersistentObject
{
	
	const MODE_CITY = 'city',
		  MODE_COUNTRY = 'country'; 
	
	/**
	 * Constructor
	 * @param array $row
	 * @see kernel/classes/ezpersistentobject.php
	 */
	public function __construct($row)
	{
		parent::eZPersistentObject($row);
	}
	
	/**
	 * Common method for SQLIIPGroupCity and SQLIIPGroupCountry to fetch data regarding given IP address
	 * Example taken from SQLIIPGroupCountry :
	 * <code>
	 * $data = SQLIGeolocObject::fetchByIP(SQLIIPGroupCountry::definition());
	 * </code>
	 * @param array $definition PersistentObject definition
	 * @param IP Address $ip
	 */
	public static function fetchByIP(array $definition, $ip)
	{
		$result = null;
		$ipLong = ip2long($ip);
		if($ipLong === false)
			throw new InvalidArgumentException("IP adress '$ip' is not considered valid");
			
		// Convert to string as unsigned integer because of integer limitation on 32bits systems
		// See http://php.net/manual/en/function.intval.php
		$ipLong = sprintf('%u', $ipLong);
		
		
		$conds = array('ip_start' => array('<=', $ipLong));
		$sort = array('ip_start' => 'desc');
		$limit = array('offset' => 0, 'limit' => 1);
		$aResult = parent::fetchObjectList($definition, null, $conds, $sort, $limit);
		if(count($aResult))
			$result = $aResult[0];
		
		return $result;
	}
	
	/**
	 * Checks if a given SQLIGeolocObject entry (or any child class instance) has changed regarding values given in $row
	 * @param array $row
	 * @param SQLIGeolocObject $entry
	 * @return bool
	 */
	public static function entryHasChanged(array $row, SQLIGeolocObject $entry)
	{
		$hasChanged = false;
		foreach($row as $field => $value)
		{
			if($entry->attribute($field) != $value)
			{
				$hasChanged = true;
				break;
			}
		}
		
		return $hasChanged;
	}
	
	/**
	 * Updates or inserts a row in IP database.
	 * Updates are made only if necessary (checks modification)
	 * @param array $definition PersistentObject definition
	 * @param array $row
	 * @return SQLIGeolocObject
	 * @throws InvalidArgumentException
	 */
	public static function updateIPInfo(array $definition, array $row)
	{
		if(!isset($row['ip_start']))
			throw new InvalidArgumentException('[SQLiGeoloc] Row update failed ! "ip_start" key is not present !');
			
		if(!isset($definition['fields']) || !isset($definition['class_name']))
			throw new InvalidArgumentException('[SQLiGeoloc] Row update failed ! Invalid PersistentObject definition');
		
		// Check if an entry already exists
		$entry = eZPersistentObject::fetchObject($definition, null, array('ip_start' => $row['ip_start']));
		if($entry instanceof eZPersistentObject)
		{
			if(self::entryHasChanged($row, $entry)) // Update entry only if necessary
			{
				$entry->fill($row);
				$entry->store();
			}
		}
		else // No existing entry. Creating a new one
		{
			$className = $definition['class_name'];
			$entry = new $className($row);
			$entry->store();
		}
		
		return $entry;
	}
}