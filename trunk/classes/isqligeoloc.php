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
 * Main interface for Geoloc info classes
 */
interface ISQLIGeoloc
{
	/**
	 * Method inherited from eZPersistentObject
	 */
    public static function definition();
	
	/**
	 * Fetches an entry (city or country) by IPv4 address
	 * @param string $ip
	 */
	public static function fetchByIP($ip);
	
	/**
	 * Handles a CSV line as array returned by fgetcsv
	 * @param array $csvLine
	 */
	public static function handleCSVLine(array $csvLine);
}