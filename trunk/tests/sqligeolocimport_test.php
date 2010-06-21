<?php
/**
 * Unit tests for SQLIGeoloc import process
 * @author Jerome Vieilledent
 */
class SQLIGeolocImportTest extends ezpTestCase
{
	/**
	 * @var eZINI
	 */
	private $sqliGeolocINI;
	
	/**
	 * @var SQLIGeolocImportHandler
	 */
	private $importHandler;
	
	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->sqliGeolocINI = eZINI::instance('sqligeoloc.ini');
		$this->sqliGeolocINI->setVariable('General', 'Debug', 'disabled');
		
		$this->importHandler = new SQLIGeolocImportHandler();
	}
	
	/**
	 * Requirements tests
	 */
	public function testRequirements()
	{
		$this->assertTrue(function_exists('curl_init'), 'cURL extension is not loaded');
		$this->assertTrue(class_exists('ezcArchive'), 'Class ezcArchive not loaded ! SQLIGeoloc needs eZ Components to work');
		
		// Test file permissions
		$varDir = eZSys::varDirectory();
		$this->assertTrue(eZFile::isWriteable($varDir));
	}
	
	/**
	 * Force download failure
	 */
	public function testDownloadFailure()
	{
		$this->setExpectedException('RuntimeException');
		$this->importHandler->zipArchive = $this->importHandler->downloadArchive('http://foo.bar/dummy.zip');
	}
	
	/**
	 * Archive download
	 */
	public function testDownload()
	{
		$url = $this->sqliGeolocINI->variable('General', 'IPDatabaseURL');
		$this->importHandler->zipArchive = $this->importHandler->downloadArchive($url);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_STRING, $this->importHandler->zipArchive, 'SQLIGeolocImportHandler::downloadArchive() must return a string !');
		$this->assertFileExists($this->importHandler->zipArchive, 'Downloaded file is not present on filesystem');
		
		// Check archive integrity
		$archive = ezcArchive::open($this->importHandler->zipArchive);
		$this->assertType('ezcArchive', $archive, 'IP database archive is corrupted');
		
		// Check if files contained in the archive are those expected
		$aExpectedFiles = array(SQLIGeolocImportHandler::CSV_CITY_FILENAME, SQLIGeolocImportHandler::CSV_COUNTRY_FILENAME);
		foreach($archive as $entry)
		{
			$filename = $entry->getPath();
			$this->assertContains($filename, $aExpectedFiles, 
								  "Unknown file '$filename'. Should be ".SQLIGeolocImportHandler::CSV_CITY_FILENAME.' or '.SQLIGeolocImportHandler::CSV_COUNTRY_FILENAME);
		}
		
		$archive->close();
		$this->importHandler->cleanup();
		$this->assertFileNotExists($this->importHandler->zipArchive, 'Archived has not been deleted by SQLIGeolocImportHandler::cleanup() method');
	}
	
	/**
	 * @dataProvider providerEntryHasNotChanged
	 * @param array $row
	 * @param SQLIGeolocObject $entry
	 */
	public function testEntryHasNotChanged(array $row, SQLIGeolocObject $entry)
	{
		$this->assertFalse(SQLIGeolocObject::entryHasChanged($row, $entry));
	}
	
	public function providerEntryHasNotChanged()
	{
		
		$rowCountry = array(
			'ip_start'			=> 50331648,
			'ip_cidr'			=> '3.0.0.0/8',
			'country_code'		=> 'US',
			'country_name'		=> 'United States'
		);
		$entryCountry = new SQLIIPGroupCountry($rowCountry);
		
		$rowCity = array(
			'ip_start'			=> 204562176,
			'country_code'		=> 'US',
			'country_name'		=> 'United States',
			'region_code'		=> '06',
			'region_name'		=> 'California',
			'city'				=> 'Cupertino',
			'zipcode'			=> '95014',
			'latitude'			=> 37.3042,
			'longitude'			=> -122.095,
			'metrocode'			=> '807'
		);
		$entryCity = new SQLIIPGroupCity($rowCity);
		
		$data = array(
			array($rowCountry, $entryCountry),
			array($rowCity, $entryCity)
		);
		return $data;
	}
	
	/**
	 * @dataProvider providerEntryHasChanged
	 * @param array $row
	 * @param SQLIGeolocObject $entry
	 */
	public function testEntryHasChanged(array $row, SQLIGeolocObject $entry)
	{
		$this->assertTrue(SQLIGeolocObject::entryHasChanged($row, $entry));
	}
	
	public function providerEntryHasChanged()
	{
		
		$rowCountry = array(
			'ip_start'			=> 50331648,
			'ip_cidr'			=> '3.0.0.0/8',
			'country_code'		=> 'US',
			'country_name'		=> 'United States'
		);
		$entryCountry = new SQLIIPGroupCountry($rowCountry);
		$rowCountry['country_code'] = 'USA'; // Assume a difference in country code
		
		$rowCity = array(
			'ip_start'			=> 204562176,
			'country_code'		=> 'US',
			'country_name'		=> 'United States',
			'region_code'		=> '06',
			'region_name'		=> 'California',
			'city'				=> 'Cupertino',
			'zipcode'			=> '95014',
			'latitude'			=> 37.3042,
			'longitude'			=> -122.095,
			'metrocode'			=> '807'
		);
		$entryCity = new SQLIIPGroupCity($rowCity);
		$rowCity['country_code'] = 'USA'; // Assume a difference in country code
		
		$data = array(
			array($rowCountry, $entryCountry),
			array($rowCity, $entryCity)
		);
		return $data;
	}
	
	/**
	 * Tests update for country
	 */
	public function testUpdateIPInfoCountry()
	{
		$rowCountry = array(
			'ip_start'			=> 50331648,
			'ip_cidr'			=> '3.0.0.0/8',
			'country_code'		=> 'US',
			'country_name'		=> 'United States'
		);
		
		$entry = SQLIGeolocObject::updateIPInfo(SQLIIPGroupCountry::definition(), $rowCountry);
		$this->assertType('SQLIIPGroupCountry', $entry);
		unset($entry);
		
		// Now test with a field value really different
		$rowCountry['country_code'] = 'USA';
		$entry = SQLIGeolocObject::updateIPInfo(SQLIIPGroupCountry::definition(), $rowCountry);
		$this->assertEquals($rowCountry['country_code'], $entry->attribute('country_code'), 'Update IP Info country failed');
		
		// Rollback the modification
		$entry->setAttribute('country_code', 'US');
		$entry->store();
	}
	
	/**
	 * Tests update for city
	 */
	public function testUpdateIPInfoCity()
	{
		$rowCity = array(
			'ip_start'			=> 204562176,
			'country_code'		=> 'US',
			'country_name'		=> 'United States',
			'region_code'		=> '06',
			'region_name'		=> 'California',
			'city'				=> 'Cupertino',
			'zipcode'			=> '95014',
			'latitude'			=> 37.3042,
			'longitude'			=> -122.095,
			'metrocode'			=> '807'
		);
		
		$entry = SQLIGeolocObject::updateIPInfo(SQLIIPGroupCity::definition(), $rowCity);
		$this->assertType('SQLIIPGroupCity', $entry);
		unset($entry);
		
		// Now test with a field value really different
		$rowCity['city'] = 'Apple City';
		$entry = SQLIGeolocObject::updateIPInfo(SQLIIPGroupCity::definition(), $rowCity);
		$this->assertEquals($rowCity['city'], $entry->attribute('city'), 'Update IP Info city failed');
		
		// Rollback the modification
		$entry->setAttribute('city', 'Cupertino');
		$entry->store();
		
	}
}