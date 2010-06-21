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

$FunctionList = array();

/**
 * Function ip_info
 * Returns geo info for a given IP address
 */
$FunctionList['ip_info'] = array(      	   'name' => 'ip_info',
                                           'operation_types' => 'read',
                                           'call_method' => array( 'class' => 'SQLIGeolocFunctionCollection',
                                                                   'method' => 'getInfoByIP' ),
                                           'parameter_type' => 'standard',
                                           'parameters' => array( array( 'name' => 'mode',
                                                                         'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => 'city' ),
                                                                  array( 'name' => 'ip',
                                                                         'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => null ) ) );

/**
 * Function remote_ip
 * Returns remote IP address
 */
$FunctionList['remote_ip'] = array(        'name' => 'remote_ip',
                                           'operation_types' => 'read',
                                           'call_method' => array( 'class' => 'SQLIGeolocFunctionCollection',
                                                                   'method' => 'getRemoteIP' ),
                                           'parameter_type' => 'standard',
                                           'parameters' => array() );
