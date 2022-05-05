<?php

namespace WebbAndFlow\Profiler;

use GuzzleHttp\Client;

/**
 * Class NativeClient
 *
 * This class is the native API client for the Webb & Flow Profiler MicroService
 *
 * @package WebbAndFlow\Profiler
 */
class NativeClient {
	/**
	 * Hostname of API server
   *
   * @var string
	 */
	const API_HOST = 'https://api-profiler-services-wnf.appspot.com';

	/**
	 * HTTP request timeout in seconds
	 *
   * After API_TIMEOUT seconds the methods will return a 0 HTTP status
   *
   * @var int
	 */
	const API_TIMEOUT = 5;

  /**
   * Add profiler data
   *
   * @param string $projectId The identifier of the Profiler MicroService project
   * @param string $profile The name of the profile
   * @param string $entity The name of the entity, to which the data should be saved
   * @param array $events The list of the events to be sent
   * @param array $processes The list of the processes to be sent
   *
   * @return array
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
	public static function add($projectId, $profile, $entity, $events, $processes)
	{
		$url = static::API_HOST.'/projects/'.$projectId.'/profiler/add';

		return static::requestHttp(
			'post',
			null,
			null,
			$url,
			[
				'profile' => $profile,
				'entity' => $entity,
				'events' => $events,
				'processes' => $processes
			]
		);
	}

  /**
   * Sends a HTTP request
   *
   * @param string $method The HTTP method of the request
   * @param string|null $user The HTTP authorization username
   * @param string|null $password The HTTP authorization password
   * @param string $url The url to be requested
   * @param array $postData The optional request body
   *
   * @return array
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
	protected static function requestHttp($method, $user, $password, $url, $postData = array())
	{
		$client = new Client();

		$options = [
			'allow_redirects' => true,
			'connect_timeout' => 30,
			'timeout' => 30,
			'verify' => false,
			'http_errors' => false,
		];
		if ($user) {
			$options['auth'] = [
				$user,
				$password,
			];
		}
		if ('POST' === strtoupper($method)) {
			$options['form_params'] = $postData;
		} elseif ('PUT' === strtoupper($method)) {
			$options['form_params'] = $postData;
		}
		$response = $client->request(
			$method,
			$url,
			$options
		);

		$headers = [];
		foreach ($response->getHeaders() as $name => $values) {
			$headers[$name] = implode(', ', $values);
		}

		$result = [
			'status' => $response->getStatusCode(),
			'response' => (string)$response->getBody(),
			'responseHeaders' => $headers,
		];

		return $result;
	}
}
