<?php

abstract class PHPBucket {
	/**
	 * Buckets a user into one of a number of buckets at random.
	 * A user is maintained in a bucket using a cookie.
	 *
	 * @param $key String: A unique identifier for this set of buckets.
	 * @param $options Map: An array of options. Valid keys:
	 * buckets: Associative array of bucket name to weight.
	 * version: Version ID, increment to invalidate all buckets.
	 * expires: Expiry, in days, of the bucket cookie.
	 * 
	 * @todo Implement click tracking
	 * @return type description
	 */
	static function getBucket( $key, $options ) {
		global $wgUser, $wgRequest;
		$defaults = array(
			'buckets' => array(),
			'version' => 0,
			'tracked' => false,
			'expires' => 30,
		);

		$options = $options + $defaults;

		$cookieName = 'phpbucket:'.$key.':'.$options['version'];

		$selectedBucket = $wgRequest->getCookie( $cookieName );

		if ( ! $selectedBucket ) {
			$range = 0;
			foreach( $options['buckets'] as $bucket => $weight ) {
				$range += $weight;
			}

			$rand = rand(0, $range);
			$upTo = 0;
			foreach( $options['buckets'] as $bucket => $weight ) {
				$selectedBucket = $bucket;
				$upTo += $weight;

				if ( $upTo >= $rand ) {
					break;
				}
			}

			// Set our cookie
			$exp = time() + ($options['expires'] * 3600 * 24);
			$wgRequest->response()
				->setCookie( $cookieName, $selectedBucket, $exp);
		}

		return $selectedBucket;
	}
}