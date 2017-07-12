<?php

/*!
 * https://github.com/illogical-ru/lava-php
 *
 * Copyright illogical
 * Released under the MIT license
 */

namespace Lava;

use Lava\Stash;


class Args {

	private $data = array();


	public function __construct () {

		$data = array('get' => $_GET, 'post' => $_POST, array());

		foreach ($data as $method => $args) {
			foreach ($args as &$val)
				$val = $this->_normalize ($val);
			$this->data[$method] = new Stash ($args);
		}
	}


	public function __get ($key) {
		foreach (array_reverse($this->data) as $stash)
			if ($stash->_has_key($key)) return $stash->$key;
	}
	public function __set  ($key, $val) {
		return end($this->data)->$key = $val;
	}

	public function __call ($key, $args) {

		if ($args) return $this->__set($key, $args);

		foreach (array_reverse($this->data) as $stash)
			if ($stash->_has_key($key)) return $stash->$key();

		return  array();
	}

	public function __isset ($key) {
		$val = $this->__get($key);
		return isset($val);
	}
	public function __unset ($key) {
		foreach ($this->data as $stash) unset($stash->$key);
	}

	public function _get () {
		return $this->data['get'];
	}
	public function _post () {
		return $this->data['post'];
	}

	public function _query ($data, $append = FALSE) {

		if (! is_array($data)) parse_str($data, $data);

		$query = $append ? $this->_get()->_data() : array();

		foreach ($data as $key => $val)
			$query[$key] = $this->_normalize($val, FALSE);

		return  http_build_query($query);
	}

	private function _normalize ($val, $gpc = NULL) {

		if   (! isset   ($gpc)) $gpc = get_magic_quotes_gpc();

		if   (  is_array($val)) {

			foreach ($val as &$item)
				$item = $this->_normalize($item, $gpc);

			return	$val;
		}
		else {
			$val = trim($val);

			if ($val != '')
				return $gpc ? stripslashes($val) : $val;
		}
	}
}

?>