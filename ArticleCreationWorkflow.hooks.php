<?php 

class ArticleCreationHooks {

	/**
	 * Redirect users to a page specified by returnto upon successful account creation
	 * @param $welcome_creation_msg - string
	 * @param $injected_html - html string
	 */
	public static function BeforeWelcomeCreation( &$welcome_creation_msg, &$injected_html ) {
		global $wgRequest, $wgOut;

		$title = Title::newFromText( $wgRequest->getVal( 'returnto' ) );
		if ( !$title instanceof Title ) {
			$title = Title::newMainPage();
		}
		$redirectUrl = $title->getFullURL( $wgRequest->getVal( 'returntoquery' ) );
		global $wgSecureLogin;
		if( $wgSecureLogin && !$wgRequest->getCheck( 'wpStickHTTPS' ) ) {
			$redirectUrl = preg_replace( '/^https:/', 'http:', $redirectUrl );
		}
		$wgOut->redirect( $redirectUrl );
		
		return true;
	}
	
	/**
	 * If the edit page is coming from red link, redirect users to article-non-existing page
	 * @param $editPage - Object
	 */
	public static function AlternateEdit( $editPage ) {
		global $wgRequest, $wgOut;

		if ( ! ArticleCreationUtil::isEnabled() ) {
			return true;
		}

		$title = $editPage->mArticle->getTitle();

		if ( $wgRequest->getBool( 'redlink' ) ) {
			$wgOut->redirect( $title->getFullURL() );
		}

		return true;
	}

	/**
	 * Customized html that shows an article doesn't exist
	 */
	public static function BeforeDisplayNoArticleText( $article, &$text, $errors, $wgUser, &$wikiText ) {
		// global $wgGroupPermissions;
		//
		// // Show the custom page if there is no error or the user is not loggin and anonmyous edit 
		// // is not allowed
		// if ( !count( $errors ) || ( $wgUser->isAnon() && !$wgGroupPermissions['*']['edit'] ) ) {			
		// 	$text = ArticleCreationTemplates::showMissingPage( $article );
		// 	$wikiText = false;
		// }
		// return false;
		global $wgOut;

		if ( ArticleCreationUtil::isEnabled() ) {
			$wgOut->addModules( array( 'ext.articleCreation.init' ) );
		}

		return true;
	}

	public static function resourceLoaderGetConfigVars( &$vars ) {
		global $wgArticleCreationConfig, $wgUser;
		
		$vars['acConfig'] = $wgArticleCreationConfig + 
					array(
						'tracking-turned-on' =>  ArticleCreationUtil::trackingEnabled(),
						'tracking-code-prefix' => ArticleCreationUtil::trackingCodePrefix(),
					);
	
		return true;
	}

}
