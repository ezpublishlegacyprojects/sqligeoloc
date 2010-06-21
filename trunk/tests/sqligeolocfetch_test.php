<?php
/**
 * Unit tests for SQLIGeoloc fetch
 * @author Jerome Vieilledent
 */
class SQLIGeolocFetchTest extends ezpTestCase
{
	/**
	 * @var eZINI
	 */
	private $sqliGeolocINI;
	
	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->sqliGeolocINI = eZINI::instance('sqligeoloc.ini');
	}
	
	public function testFetchFailWithNullIP()
	{
		$res = eZFunctionHandler::execute('sqligeoloc', 'ip_info', array());
		$this->assertNull($res);
	}
	
	public function providerIPAddresses()
	{
		return array(
			array('81.255.210.187'), // France
			array('41.250.250.26'), // Morocco
			array('17.149.160.49'), // United States
			array('85.214.110.30'), // Germany
		);
	}
	
	/**
	 * Tests for city fetch mode
	 * @dataProvider providerIPAddresses
	 * @param string $ip
	 */
	public function testGetCityInfoByIP($ip)
	{
		$aParams = array(
			'ip'	=> $ip,
			'mode'	=> 'city'
		);
		
		$res = eZFunctionHandler::execute('sqligeoloc', 'ip_info', $aParams);
		$this->assertType('SQLIIPGroupCity', $res, 'Invalid persistent object class for city fetch mode'); // Check type
		$this->assertType('SQLIGeolocObject', $res, 'SQLIIPGroupCity does not extend SQLIGeolocObject'); // Check inheritance
		$this->assertType('eZPersistentObject', $res, 'SQLIIPGroupCity does not extend eZPersistentObject'); // Check inheritance
		
		// Check interface implementation
		$aImplInterfaces = class_implements($res);
		$this->assertArrayHasKey('ISQLIGeoloc', $aImplInterfaces, 'SQLIIPGroupCity does not implement ISQLIGeoloc');
		
		// Check if all fields are present
		$fields = array(
			'ip_start',
			'country_code',
			'country_name',
			'region_code',
			'region_name',
			'city',
			'zipcode',
			'latitude',
			'longitude',
			'metrocode'
		);
		
		foreach($fields as $field)
		{
			$this->assertTrue($res->hasAttribute($field));
		}
	}
	
	/**
	 * Tests for country fetch mode
	 * @dataProvider providerIPAddresses
	 * @param string $ip
	 */
	public function testGetCountryInfoByIP($ip)
	{
		$aParams = array(
			'ip'	=> $ip,
			'mode'	=> 'country'
		);
		
		$res = eZFunctionHandler::execute('sqligeoloc', 'ip_info', $aParams);
		$this->assertType('SQLIIPGroupCountry', $res, 'Invalid persistent object class for country fetch mode'); // Check type
		$this->assertType('SQLIGeolocObject', $res, 'SQLIIPGroupCountry does not extend SQLIGeolocObject'); // Check inheritance
		$this->assertType('eZPersistentObject', $res, 'SQLIIPGroupCountry does not extend eZPersistentObject'); // Check inheritance
		
		// Check interface implementation
		$aImplInterfaces = class_implements($res);
		$this->assertArrayHasKey('ISQLIGeoloc', $aImplInterfaces, 'SQLIIPGroupCountry does not implement ISQLIGeoloc');
		
		// Check if all fields are present
		$fields = array(
			'ip_start',
			'ip_cidr',
			'country_code',
			'country_name'
		);
		
		foreach($fields as $field)
		{
			$this->assertTrue($res->hasAttribute($field));
		}
	}
}