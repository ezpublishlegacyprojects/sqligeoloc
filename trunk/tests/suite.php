<?php

class SQLIGeolocTestSuite extends ezpTestSuite
{
	public function __construct()
	{
		parent::__construct();
		$this->setName( "SQLIGeoloc Test Suite" );
		$this->addTestSuite( 'SQLIGeolocFetchTest' );
		$this->addTestSuite( 'SQLIGeolocImportTest' );
	}
	
	public static function suite()
	{
		return new self();
	}
}