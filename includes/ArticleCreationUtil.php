<?php

/**
 * Utility class for Article Creation
 */
class ArticleCreationUtil {

	/**
	 * Is ArticleCreation enabled for the current user?
	 *
	 * 
	 * @return bool whether or not it is.
	 */
	public static function isEnabled() {
		global $wgUser, $wgArticleCreationRegistrationCutoff,
			$wgArticleCreationBucketConfig;

		$userRegistration = wfTimestamp( TS_MW, $wgUser->getRegistration() );

		if ( !$userRegistration ||
			$userRegistration > $wgArticleCreationRegistrationCutoff
		) {
			$bucket = PHPBucket::getBucket( 'ac-enabled', $wgArticleCreationBucketConfig );

			return $bucket === 'on';
		} else {
			return false;
		}
	}

	/**
	 * Check if tracking is enabled, in this case - ClickTracking
	 * @return bool
	 */
	public static function trackingEnabled() {
		return class_exists( 'ApiClickTracking' );
	}

	/**
	 * Generate tracking code prefix for this campaign
	 * @return string - the prefix text for clickTracking
	 */
	public static function trackingCodePrefix() {
		global $wgExtensionCredits;
		return 'ext.articlecreationworkflow@' . $wgExtensionCredits['other'][0]['version'] . '-';
	}

	/**
	 * Track the page stats to the special article creation landing page
	 * @param $request Object
	 * @param $user Object
	 * @param $par string - the title for the non-existing article
	 */
	public static function TrackSpecialLandingPage( $request, $user, $par ) {
		if ( $user->isAnon() ) {
			$event = 'landingpage-anonymous';
		} else {
			$event = 'landingpage-loggedin';

			if ( $request->getBool( 'fromlogin' ) ) {
				$event .= '-fromlogin';
			} elseif ( $request->getBool( 'fromsignup' ) ) {
				$event .= '-fromsignup';
			}
		}

		self::clickTracking( $event, Title::newFromText( $par ) );
	}

	/**
	 * Tracking code that calls ClickTracking
	 * @param $event string the event name
	 * @param $title Object
	 */
	public static function clickTracking( $event, $title ) {
		// check if ClickTracking API is enabled
		if ( !self::trackingEnabled() ) {
			return;
		}

		global $wgRequest;

		// try to grab the token generated from clickTracking
		$token = $wgRequest->getCookie( 'clicktracking-session', '' );

		if ( !$token ) {
			$token = wfGenerateToken();
		}

		$pageId = $title->getArticleID();

		$params = new FauxRequest( array(
			'action' => 'clicktracking',
			'eventid' => self::trackingCodePrefix() . $event,
			'token' => $token,
			'namespacenumber' => $title->getNamespace(),
			'additional' => $title->getDBkey() . ( $pageId ? '|' . $pageId : '' ),
		) );
		$api = new ApiMain( $params, true );
		$api->execute();
	}

}
