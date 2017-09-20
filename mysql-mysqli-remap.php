<?php

// mysql-mysqli-remap.php
// remapping old style mysql_-functions to corresponding mysqli_-functions
// v 0.1 (alpha)

// This library is free software: you can redistribute it and/or modify
// it under the terms of version 3 of the GNU Lesser General Public
// License as published by the Free Software Foundation.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this library. If not, see <http://www.gnu.org/licenses/>.

// btd@thin.no
// Thin AS (c) 2017
// www.thin.no

// Including this file before doing any database operations should make old
// mysql database operations (<= php 5.5) work with newer php (>= php 7).
// Including this file in older code should be safe as well, but your mileage
// may vary. All procedural. No warranty, PROVIDED AS IS. Do make db backups.

// NB: mysqli_- functions returns objects instead of resources, so any
// code that checks if a result is a resource must be adapted.
// Functions may try to (re)connect automatically, based on php ini settings
// A lot of this is written from documentation without testing every scenario.

// supported:         mysql_affected_rows
// not yet supported: mysql_client_encoding
// supported:         mysql_close
// supported:         mysql_connect (returns object instead of resource)
// not yet supported: mysql_create_db
// supported:         mysql_data_seek
// not yet supported: mysql_db_name
// not yet supported: mysql_db_query
// not yet supported: mysql_drop_db
// not yet supported: mysql_errno
// supported:         mysql_error
// not yet supported: mysql_escape_string
// supported:         mysql_fetch_array
// not yet supported: mysql_fetch_assoc
// not yet supported: mysql_fetch_field
// not yet supported: mysql_fetch_lengths
// not yet supported: mysql_fetch_object
// not yet supported: mysql_fetch_row
// not yet supported: mysql_field_flags
// not yet supported: mysql_field_len
// not yet supported: mysql_field_name
// not yet supported: mysql_field_seek
// not yet supported: mysql_field_table
// not yet supported: mysql_field_type
// not yet supported: mysql_free_result
// not yet supported: mysql_get_client_info
// supported:         mysql_get_host_info (may not return false on failure)
// not yet supported: mysql_get_proto_info
// not yet supported: mysql_get_server_info
// not yet supported: mysql_info
// supported:         mysql_insert_id
// not supported:     mysql_list_dbs (1)
// not supported:     mysql_list_fields (1)
// not supported:     mysql_list_processes (1)
// not supported:     mysql_list_tables (1)
// not yet supported: mysql_num_fields
// supported:         mysql_num_rows (may not return false on failure)
// not yet supported: mysql_pconnect
// supported:         mysql_ping
// supported:         mysql_query (returns object instead of resource)
// supported:         mysql_real_escape_string (may not return false on error)
// not yet supported: mysql_result
// supported:         mysql_select_db
// not yet supported: mysql_set_charset
// supported:         mysql_stat (may return false instead of NULL on error)
// not yet supported: mysql_tablename
// not yet supported: mysql_thread_id
// not yet supported: mysql_unbuffered_query

// (1) original function returns a resource intended for further processing,
//     and there is no matching mysqli_-function to make a drop-in replacement,
//     or the function relies on such a resource

$_tmmrtv_debug = 0; // 0 for disable, 1 for procedural info, 2 for procedural and data
$_tmmrtv_loaded = true; // some external functions may want to know if this lib is loaded
$_tmmrtv_active = false; // some external functions may want to know if this lib is active

if (function_exists('mysql_connect')) {
	if ($_tmmrtv_debug) print "mysql_connect() exists, assuming mysql_-support\n";
}

else {
	if ($_tmmrtv_debug) print "mysql_connect() does not exist, loading remapping functions\n";

	$_tmmrtv_active = true;

	// php 5 defaults
	if (!defined('MYSQL_CLIENT_COMPRESS')) define('MYSQL_CLIENT_COMPRESS', MYSQLI_CLIENT_COMPRESS);
	if (!defined('MYSQL_CLIENT_IGNORE_SPACE')) define('MYSQL_CLIENT_IGNORE_SPACE', MYSQLI_CLIENT_IGNORE_SPACE);
	if (!defined('MYSQL_CLIENT_INTERACTIVE')) define('MYSQL_CLIENT_INTERACTIVE', MYSQLI_CLIENT_INTERACTIVE);
	if (!defined('MYSQL_CLIENT_SSL')) define('MYSQL_CLIENT_SSL', MYSQLI_CLIENT_SSL);
	if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', MYSQLI_ASSOC);
	if (!defined('MYSQL_NUM')) define('MYSQL_NUM', MYSQLI_NUM);
	if (!defined('MYSQL_BOTH')) define('MYSQL_BOTH', MYSQLI_BOTH);

	// sanity checks
	if (function_exists('mysql_affected_rows')) trigger_error('mysql_affected_rows exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_close')) trigger_error('mysql_close exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_data_seek')) trigger_error('mysql_data_seek exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_error')) trigger_error('mysql_error exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_fetch_array')) trigger_error('mysql_fetch_array exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_get_host_info')) trigger_error('mysql_get_host_info exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_insert_id')) trigger_error('mysql_insert_id exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_num_rows')) trigger_error('mysql_num_rows exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_ping')) trigger_error('mysql_ping exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_query')) trigger_error('mysql_query exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_real_escape_string')) trigger_error('mysql_real_escape_string exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_select_db')) trigger_error('mysql_select_db exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);
	if (function_exists('mysql_stat')) trigger_error('mysql_stat exists even if mysql_connect does not, too weird to go on', E_USER_ERROR);

	$_tmmrtv_defaultlink = NULL; // mysqli queries require explicit link

	// remap mysql_affected_rows() to mysqli_affected_rows()
	function mysql_affected_rows(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_affected_rows to mysqli_affected_rows()\n";
		$return = mysqli_affected_rows($link);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_close() to mysqli_close()
	function mysql_close(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_close to mysqli_close()\n";
		$res = mysqli_close($link);
		if ($_tmmrtv_debug >= 2) var_dump($res);

		return $res;

	}

	// remap mysql_connect to mysqli_connect()
	// FIXME make handling for new_link and client_flags
	function mysql_connect(
		$server = NULL,
		$username = NULL,
		$password = NULL,
		$new_link = false,
		$client_flags = 0
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if ($server) {}
		elseif (ini_get('mysql.default_host')) $server = ini_get('mysql.default_host');
		elseif (ini_get('mysqli.default_host')) $server = ini_get('mysqli.default_host');

		if ($username) {}
		elseif (ini_get('mysql.default_user')) $username = ini_get('mysql.default_user');
		elseif (ini_get('mysqli.default_user')) $username = ini_get('mysqli.default_user');

		if ($password) {}
		elseif (ini_get('mysql.default_password')) $password = ini_get('mysql.default_password');
		elseif (ini_get('mysqli.default_pw')) $password = ini_get('mysqli.default_pw');

		if ($_tmmrtv_debug) print "remapping mysql_connect to mysqli_connect()\n";
		$res = mysqli_connect($server, $username, $password);
		$_tmmrtv_defaultlink = $res;
		if ($_tmmrtv_debug >= 2) var_dump($res);

		return $res;

	}

	// remap it mysql_data_seek() mysqli_data_seek()
	function mysql_data_seek(
		$res,
		$row_number
	) {

		global $_tmmrtv_debug;

		if ($_tmmrtv_debug) print "remapping mysql_data_seek to mysqli_data_seek()\n";
		$return = mysqli_data_seek($res, $row_number);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_error() to mysqli_error()
	function mysql_error(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_error to mysqli_error()\n";
		$return = mysqli_error($link);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_fetch_array() to mysqli_fetch_array()
	function mysql_fetch_array(
		$res,
		$result_type = MYSQL_BOTH
	) {

		global $_tmmrtv_debug;

		if ($_tmmrtv_debug) print "remapping mysql_fetch_array to mysqli_fetch_array()\n";
		$return = mysqli_fetch_array($res, $result_type);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_get_host_info() to mysqli_get_host_info()
	function mysql_get_host_info(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_get_host_info to mysqli_get_host_info()\n";
		$return = mysqli_get_host_info($link);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_insert_id() to mysqli_insert_id()
	function mysql_insert_id(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_insert_id to mysqli_insert_id()\n";
		$return = mysqli_insert_id($link);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_num_rows() to mysqli_num_rows()
	function mysql_num_rows(
		$res
	) {

		global $_tmmrtv_debug;

		if ($_tmmrtv_debug) print "remapping mysql_num_rows to mysqli_num_rows()\n";
		$return = mysqli_num_rows($res);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_ping() to mysqli_ping()
	function mysql_ping(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_ping to mysqli_ping()\n";
		$res = mysqli_ping($link);
		if ($_tmmrtv_debug >= 2) var_dump($res);

		if ($res === NULL) $res = false;
		return $res;

	}

	// remap mysql_query() to mysqli_query()
	function mysql_query(
		$query,
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_query to mysqli_query()\n";
		$res = mysqli_query($link, $query);
		if ($_tmmrtv_debug >= 2) var_dump($res);

		return $res;

	}

	// remap mysql_real_escape_string() to mysqli_real_escape_string()
	function mysql_real_escape_string(
		$string,
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_real_escape_string to mysqli_real_escape_string()\n";
		$return = mysqli_real_escape_string($link, $string);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}

	// remap mysql_select_db() to mysqli_select_db()
	function mysql_select_db(
		$select_db,
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_select_db to mysqli_select_db()\n";
		$res = mysqli_select_db($link, $select_db);
		if ($_tmmrtv_debug >= 2) var_dump($res);

		return $res;

	}

	// remap mysql_stat() to mysqli_stat()
	function mysql_stat(
		$link = NULL
	) {

		global $_tmmrtv_defaultlink;
		global $_tmmrtv_debug;

		if (!$link) {
			if ($_tmmrtv_debug) print "falling back to default link\n";
			$link = $_tmmrtv_defaultlink;
		}

		if ($_tmmrtv_debug) print "remapping mysql_stat to mysqli_stat()\n";
		$return = mysqli_stat($link);
		if ($_tmmrtv_debug >= 2) var_dump($return);

		return $return;

	}


}

if ($_tmmrtv_debug) print "EOF";

?>
