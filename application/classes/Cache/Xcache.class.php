<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Xcache.php 24593 2012-01-05 20:35:02Z matthew $
 */

class Cache_Xcache extends Cache_Backend implements Cache_Interface
{

	/**
	 * Available options
	 *
	 * =====> (string) user :
	 * xcache.admin.user (necessary for the clean() method)
	 *
	 * =====> (string) password :
	 * xcache.admin.pass (clear, not MD5) (necessary for the clean() method)
	 *
	 * @var array available options
	 */
	protected $_options = array('user' => 'xcache', 'password' => 'password');

	/**
	 * Constructor
	 *
	 * @param  array $options associative array of options
	 * @throws Zend_Cache_Exception
	 * @return void
	 */
	public function __construct(array $options = array())
	{
		if (!extension_loaded('xcache')) {
			throw new Exception_Cache('The xcache extension must be loaded for using this backend !');
		}
	}

	/**
	 * Test if a cache is available for the given id and (if yes) return it (false else)
	 *
	 * WARNING $doNotTestCacheValidity=true is unsupported by the Xcache backend
	 *
	 * @param  string  $id                     cache id
	 * @param  boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
	 * @return string cached datas (or false)
	 */
	public function load($id)
	{
		$tmp = xcache_get($id);
		if (is_array($tmp)) {
			return $tmp[0];
		}
		return false;
	}

	/**
	 * Test if a cache is available or not (for the given id)
	 *
	 * @param  string $id cache id
	 * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
	 */
	public function test($id)
	{
		if (xcache_isset($id)) {
			$tmp = xcache_get($id);
			if (is_array($tmp)) {
				return $tmp[1];
			}
		}
		return false;
	}

	/**
	 * Save some string datas into a cache record
	 *
	 * Note : $data is always "string" (serialization is done by the
	 * core not by the backend)
	 *
	 * @param string $data datas to cache
	 * @param string $id cache id
	 * @param array $tags array of strings, the cache record will be tagged by each string entry
	 * @param int $specificLifetime if != false, set a specific lifetime for this cache record (null => infinite lifetime)
	 * @return boolean true if no problem
	 */
	public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		$lifetime = $this->getLifetime($specificLifetime);
		$result = xcache_set($id, array($data, time()), $lifetime);
		if (count($tags) > 0) {
			throw new Cache_Exception("Les Tags ne sont pas supportés par XCache");
		}
		return $result;
	}

	/**
	 * Remove a cache record
	 *
	 * @param  string $id cache id
	 * @return boolean true if no problem
	 */
	public function remove($id)
	{
		return xcache_unset($id);
	}

	/**
	 * Clean some cache records
	 *
	 * Available modes are :
	 * 'all' (default)  => remove all cache entries ($tags is not used)
	 * 'old'            => unsupported
	 * 'matchingTag'    => unsupported
	 * 'notMatchingTag' => unsupported
	 * 'matchingAnyTag' => unsupported
	 *
	 * @param  string $mode clean mode
	 * @param  array  $tags array of tags
	 * @throws Zend_Cache_Exception
	 * @return boolean true if no problem
	 */
	public function clean()
	{
		// Necessary because xcache_clear_cache() need basic authentification
		$backup = array();
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$backup['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
		}
		if (isset($_SERVER['PHP_AUTH_PW'])) {
			$backup['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
		}
		if ($this->_options['user']) {
			$_SERVER['PHP_AUTH_USER'] = $this->_options['user'];
		}
		if ($this->_options['password']) {
			$_SERVER['PHP_AUTH_PW'] = $this->_options['password'];
		}

		$cnt = xcache_count(XC_TYPE_VAR);
		for ($i = 0; $i < $cnt; $i++) {
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}

		if (isset($backup['PHP_AUTH_USER'])) {
			$_SERVER['PHP_AUTH_USER'] = $backup['PHP_AUTH_USER'];
			$_SERVER['PHP_AUTH_PW'] = $backup['PHP_AUTH_PW'];
		}
		return true;
	}
}
