<?php

namespace WebbAndFlow\Profiler;

class Profiler {
	/** @var bool */
	protected static $enabled = false;
	/** @var array */
	protected static $events = [];
	/** @var array */
	protected static $processes = [];
	
	/**
	 * determines profile name from $_GET parameters
	 * PROFILER and PROFILE
	 *
	 * @return null|string
	 */
	public static function getProfile() {
		if (
			isset($_GET['PROFILER'])
			&& $_GET['PROFILER']
		) {
			return $_GET['PROFILER'];
		}
		if (
			isset($_GET['PROFILE'])
			&& $_GET['PROFILE']
		) {
			return $_GET['PROFILE'];
		}
		
		return null;
	}
	
	/**
	 * enable profiler if there is a
	 * profile name specified in $_GET
	 */
	public static function enableIfHasProfile() {
		if (static::getProfile()) {
			static::enable();
		}
	}
	
	/**
	 * @return array
	 */
	public static function getEvents() {
		return static::$events;
	}
	
	/**
	 * @return array
	 */
	public static function getProcesses() {
		return static::$processes;
	}
	
	/**
	 * @return bool
	 */
	public static function isEnabled() {
		return static::$enabled;
	}
	
	/**
	 * enable profiler
	 */
	public static function enable() {
		static::$enabled = true;
	}
	
	/**
	 * disable profiler
	 */
	public static function disable() {
		static::$enabled = false;
	}
	
	/**
	 * tracks an event, if the profiler is enabled
	 * if enabled, returns true
	 *
	 * @param string $event
	 * @param string $param1
	 * @param string $param2
	 *
	 * @return bool
	 */
	public static function addEvent($event, $param1 = null, $param2 = null) {
		if (!static::$enabled) {
			return false;
		}
		if (is_null($param1)) {
			$param1 = 'NULL';
		}
		if (is_null($param2)) {
			$param2 = 'NULL';
		}
		static::$events[] = [
			't' => microtime(true),
			'e' => $event,
			'p1' => $param1,
			'p2' => $param2,
		];
		
		return true;
	}
	
	/**
	 * tracks a process start, if the profiler is enabled
	 * if enabled, returns true
	 *
	 * @param string $process
	 * @param string $param1
	 * @param string $param2
	 *
	 * @return bool
	 */
	public static function startProcess($process, $param1 = null, $param2 = null) {
		if (!static::$enabled) {
			return false;
		}
		if (is_null($param1)) {
			$param1 = 'NULL';
		}
		if (is_null($param2)) {
			$param2 = 'NULL';
		}
		if (!isset(static::$processes[$process])) {
			static::$processes[$process] = [];
		}
		static::$processes[$process][] = [
			'p' => $process,
			's' => microtime(true),
			'p1' => $param1,
			'p2' => $param2,
			'f' => null,
		];
		
		return true;
	}
	
	/**
	 * tracks a process finish, if the profiler is enabled
	 * if enabled, returns true
	 *
	 * @param string $process
	 *
	 * @return bool
	 */
	public static function finishProcess($process) {
		if (!static::$enabled) {
			return false;
		}
		if (!isset(static::$processes[$process])) {
			return true;
		}
		$lastIndex = count(static::$processes[$process]) - 1;
		if (isset(static::$processes[$process][$lastIndex]['f'])) {
			return true;
		}
		static::$processes[$process][$lastIndex]['f'] = microtime(true);
		
		return true;
	}
	
	/**
	 * saves tracked data to Webb & Flow Profiler MicroService,
	 * if the profiler is enabled
	 * if enabled, returns the API Response
	 *
	 * if $profile is empty, it uses the profile name
	 * determined by $_GET parameters
	 *
	 * @param $projectId
	 * @param $profile
	 * @param $entity
	 *
	 * @return array|null
	 */
	public static function saveProfile($projectId, $profile, $entity) {
		if (!static::$enabled) {
			return null;
		}
		
		return NativeClient::add(
			$projectId,
			$profile ?: static::getProfile(),
			$entity,
			static::getEvents(),
			static::getProcesses()
		);
	}
}
