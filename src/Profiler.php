<?php

namespace WebbAndFlow\Profiler;

/**
 * Class Profiler
 *
 * This class collects runtime analytical data of the system,
 * and sends to the Webb & Flow Profiler MicroService at the end of the process, if needed.
 *
 * This class does anything only if the request has the PROFILE or PROFILER query parameter and it has a valid value,
 * to prevent any unnecessary memory usage.
 *
 * This class should be used as static, so one process can have one profile at a time.
 * Also it helps to prevent the need of dependency injection into every object and method which should be analysed.
 *
 * @package WebbAndFlow\Profiler
 */
class Profiler {
	/**
   * Is the profiler enabled?
   *
   * @var bool
   */
	protected static $enabled = false;

	/**
   * The list of the events to be sent
   *
   * @var array
   */
	protected static $events = [];

	/**
   * The list of the processes to be sent
   *
   * @var array
   */
	protected static $processes = [];

	/**
	 * Determines the profile name from $_GET parameters PROFILER and PROFILE
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
	 * Enables profiler if there is a profile name specified in $_GET
	 */
	public static function enableIfHasProfile() {
		if (static::getProfile()) {
			static::enable();
		}
	}

	/**
   * Getter for the static::$events property
   *
	 * @return array
	 */
	public static function getEvents() {
		return static::$events;
	}

	/**
   * Getter for the static::$processes property
   *
	 * @return array
	 */
	public static function getProcesses() {
		return static::$processes;
	}

	/**
   * Getter for the static::$enabled property
   *
	 * @return bool
	 */
	public static function isEnabled() {
		return static::$enabled;
	}

	/**
	 * Enables profiler manually
	 */
	public static function enable() {
		static::$enabled = true;
	}

	/**
	 * Disables profiler manually
	 */
	public static function disable() {
		static::$enabled = false;
	}

	/**
	 * Tracks an event, if the profiler is enabled
	 *
   * Returns true, if the event is tracked.
	 *
	 * @param string $event The name of the event
	 * @param string $param1 The primary parameter of the event
	 * @param string $param2 The secondary parameter of the event
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
	 * Tracks a process start, if the profiler is enabled
	 *
   * Returns true, if the process is tracked.
	 *
	 * @param string $process The name of the process
   * @param string $param1 The primary parameter of the process
   * @param string $param2 The secondary parameter of the process
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
	 * Tracks a process finish, if the profiler is enabled
   *
   * Returns true, if the process is tracked.
   *
   * @param string $process The name of the process
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
	 * Saves tracked data to Webb & Flow Profiler MicroService, if the profiler is enabled
	 *
   * If enabled, returns the API Response.
	 *
	 * If the $profile parameter is empty, it uses the profile name determined by $_GET parameters.
	 *
	 * @param string $projectId The identifier of the Profiler MicroService project
	 * @param string $profile The name of the profile
	 * @param string $entity The name of the entity, to which the data should be saved
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
