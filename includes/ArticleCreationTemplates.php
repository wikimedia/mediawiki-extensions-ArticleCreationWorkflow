<?php

/**
 * Object for creating HTML template
 */
class ArticleCreationTemplates {

	public static function getLandingPage( $page ) {
		$action = wfMessage( 'ac-action-indicator' )->escaped();

		global $wgUser, $wgArticleCreationButtons;

		$buttons = array();
		if ( $wgUser->isAnon() ) {
			$buttons = $wgArticleCreationButtons['anonymous'];
		} else {
			$buttons = $wgArticleCreationButtons['logged-in'];
		}

		$buttons = self::formatButtons( $buttons, $page );

		$html = <<<HTML
			<span class="article-creation-heading">$action</span>
			<div id="article-creation-panel">
				$buttons
			</div>
HTML;

		return $html;
	}

	public static function formatButtons( $description, $page ) {
		$buttons = '';

		foreach ( $description as $button => $info ) {
			$buttons .= self::formatButton(
				$button,
				wfMessage($info['title']),
				wfMessage($info['text']),
				$page
			);
		}

		return $buttons;
	}

	public static function formatButton( $button, $buttonTitle, $buttonText, $page ) {
		if ( $buttonTitle instanceof Message ) {
			$buttonTitle = $buttonTitle->escaped();
		}

		if ( $buttonText instanceof Message ) {
			$buttonText = $buttonText->escaped();
		}

		global $wgArticleCreationConfig, $wgScript, $wgUser;

		$target = htmlspecialchars( $wgArticleCreationConfig['action-url'][$button] );

		$replacements = array(
			'{{SCRIPT}}' => $wgScript,
			'{{USER}}' => $wgUser,
			'{{PAGE}}' => $page,
		);

		$target = strtr( $target, $replacements );

		return <<<HTML
		<div class="ac-button-wrap">
			<a class="ac-article-button ac-button ac-button-blue ac-article-$button" data-ac-button="$button" href="$target">
				<div class="ac-arrow ac-arrow-forward">&nbsp;</div>
				<div class="ac-button-text">
					<div class="ac-button-title">$buttonTitle</div>	
					<div class="ac-button-body">$buttonText</div>
				</div>
			</a>
		</div>
HTML;
	}
	
	public static function showMissingPage( $article ) {
		$link = wfMessage( 'ac-link-create-article' )->params( 
					SpecialPage::getTitleFor( 'ArticleCreationLanding', $article->getTitle()->getPrefixedText() 
					)->getPrefixedText() )->parse();
		return <<<HTML
				<div>
					$link
				</div>
HTML;
	}
	
}



