<?php

namespace WebbAndFlow\Profiler;

use GuzzleHttp\Client;

class NativeClient {
	/**
	 * @const Hostname of API server
	 */
	const API_HOST = 'https://api-profiler-services-wnf.appspot.com';
	
	/**
	 * HTTP request timeout in seconds
	 * After API_TIMEOUT seconds the methods will return 0 HTTP status
	 */
	const API_TIMEOUT = 5;
	
	/**
	 * Add profiler data
	 *
	 * @param string $projectId
	 * @param string $profile
	 * @param string $entity
	 * @param array  $events
	 * @param array  $processes
	 *
	 * @return array HTTP response data
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