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
 * Import handler for geoloc data. Used to update geoloc tables
 */
class SQLIGeolocImportHandler
{
	const CSV_LINE_LENGTH = 100000,
		  CSV_CITY_FILENAME = 'ip_group_city.csv',
		  CSV_COUNTRY_FILENAME = 'ip_group_country.csv';
	
	/**
	 * @var eZINI
	 */
	protected $geolocINI;
	
	/**
	 * @var eZCLI
	 */
	protected $cli;
	
	public $zipArchive;
	
	/**
	 * @var array
	 */
	protected $aCSV = array();
	
	public function __construct()
	{
		$this->geolocINI = eZINI::instance('sqligeoloc.ini');
		$this->cli = eZCLI::instance();
	}
	
	/**
	 * Main method. Triggers IP database update
	 */
	public function updateDatabase()
	{
		$archiveURL = $this->geolocINI->variable('General', 'IPDatabaseURL');
		$varDir = eZSys::varDirectory();
		$this->zipArchive = $this->downloadArchive($archiveURL);
		$archive = ezcArchive::open($this->zipArchive);
		foreach($archive as $entry)
		{
			$csvPath = $varDir.'/'.$entry->getPath();
			$this->aCSV[] = $csvPath;
			
			$this->cli->warning('Inflating '.$csvPath);
			$archive->extractCurrent($varDir);
			$this->handleCSVFile($csvPath);
		}
	}
	
	/**
	 * Downloads archive containing IP database (CSV files) and returns local path of downloaded zip file
	 * @param string $archiveURL
	 * @return string Local downloaded archive path
	 * @throws RuntimeException
	 */
	public function downloadArchive($archiveURL)
	{
		if(!function_exists('curl_init'))
			throw new RuntimeException('[SQLIGeoloc] cURL extension is not installed');
		
		$this->cli->warning('Downloading archive');
		$ch = curl_init($archiveURL);
		$siteINI = eZINI::instance();
		$debug = $this->geolocINI->variable('General', 'Debug') === 'enabled';
		
		// Preparing target file in FS
		$varDir = eZSys::varDirectory();
		$tmpFile = 'sqligeoloc_'.md5(microtime()).'.zip';
		$localPath = $varDir.'/'.$tmpFile;
		$fp = fopen($localPath, 'w+');
		
		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
		curl_setopt( $ch, CURLOPT_NOPROGRESS, !$debug );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->geolocINI->variable('General', 'DownloadTimeout') );

		// Check proxy settings
		$proxy = $siteINI->hasVariable( 'ProxySettings', 'ProxyServer' ) ? $siteINI->variable( 'ProxySettings', 'ProxyServer' ) : false;
		if($proxy)
		{
			curl_setopt ( $ch, CURLOPT_PROXY , $proxy );
			$userName = $ini->hasVariable( 'ProxySettings', 'User' ) ? $ini->variable( 'ProxySettings', 'User' ) : false;
			$password = $ini->hasVariable( 'ProxySettings', 'Password' ) ? $ini->variable( 'ProxySettings', 'Password' ) : false;
			if($userName)
			{
				curl_setopt ( $ch, CURLOPT_PROXYUSERPWD, "$userName:$password" );
			}
		}
		
		if(!curl_exec($ch)) // Error while executing cURL request
		{
			$error = curl_error($ch);
			curl_close($ch);
			fclose($fp);
			unlink($localPath);
			throw new RuntimeException('[SQLIGeoloc] Download error : '.$error);
		}
		
		curl_close($ch);
		fclose($fp);
		
		return $localPath;
	}
	
	/**
	 * Cleanup method
	 */
	public function cleanup()
	{
		if(file_exists($this->zipArchive))
			unlink($this->zipArchive);
			
		foreach($this->aCSV as $csvFile)
		{
			if(file_exists($csvFile))
				unlink($csvFile);
		}
	}
	
	/**
	 * Handles given CSV file
	 * @param string $pathToFile
	 * @param bool $ignoreFirstLine Tells if we ignore first line of CSV (generally field description). True by default
	 * @throws RuntimeException
	 */
	protected function handleCSVFile($pathToFile, $ignoreFirstLine=true)
	{
		$basename = basename($pathToFile);
		$className = null;
		
		// Define which handler to use
		switch($basename)
		{
			case self::CSV_CITY_FILENAME:
				$className = 'SQLIIPGroupCity';
				break;
				
			case self::CSV_COUNTRY_FILENAME:
				$className = 'SQLIIPGroupCountry';
				break;
				
			default:
				throw new RuntimeException("Unknown file '$basename'. Should be ".self::CSV_CITY_FILENAME.' or '.self::CSV_COUNTRY_FILENAME);
				break;
		}
		
		$this->cli->notice('##########');
		$this->cli->notice('# '.$className);
		$this->cli->notice('##########');
		$this->cli->notice('Buferring CSV file '.$pathToFile);
		$fp = fopen($pathToFile, 'r'); // Buffering CSV File
		
		// Update database with CSV file
		$this->cli->notice('Now updating IP database');
		$i = 0;
		while($data = fgetcsv($fp, self::CSV_LINE_LENGTH , ';', '"'))
		{
			if($i == 0 && $ignoreFirstLine) // Ignore first line
			{
				$i++;
				continue;
			}

			try
			{
				// Send CSV line to the right handler (ex. SQLIIPGroupCity::handleCSVLine($data))
				call_user_func(array($className, 'handleCSVLine'), $data);
				$this->cli->notice('.', false);
			}
			catch(Exception $e)
			{
				eZLog::write($e->getMessage(), 'error.log');
				$this->cli->notice('E', false);
				continue;
			}
			
			$i++;
		}
		
		$this->cli->notice(); // Blank line
		fclose($fp);
		return true;
	}
}
