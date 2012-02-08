<?php

/**
 * Object for creating HTML template
 */
class ArticleCreationTemplates {

	public static function getLandingPage( $page ) {
		$action = wfMessage( 'ac-action-indicator' )->escaped();
		global $wgUser, $wgArticleCreationButtons;

		$title = Title::newFromText( $page );

		$html = '';
		$buttons = array();
		if ( ! $title->userCan('create') || ! $title->userCan('edit') ) {
			$html .= wfMessage( 'ac-landing-login-required' )->parse();
			$buttons = $wgArticleCreationButtons['anonymous'];
		} else {
			$buttons = $wgArticleCreationButtons['logged-in'];
		}

		$buttons = self::formatButtons( $buttons, $page );

		$html .= <<<HTML
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
				$info,
				$page
			);
		}

		return $buttons;
	}

	public static function formatButton( $button, $info, $page ) {
		global $wgArticleCreationConfig, $wgScript, $wgUser;
		
		// Figure out title
		if ( empty($info['title'] ) ) {
			throw new MWException( "Buttons require a title" );
		}

		$buttonTitle = wfMessage($info['title'])->escaped();

		// Figure out help text
		if ( !empty($info['text']) ) {
			$buttonText = wfMessage($info['text'])->escaped();
		} else {
			$buttonText = '';
		}

		// Ensure colour is set correctly
		$color = 'blue';
		if ( !empty($info['color']) ) {
			$color = $info['color'];
		}

		// Work out tooltips
		$tips = '';
		if ( !empty($info['tooltip'] ) ) {
			$tips .= self::formatTooltip(
				$info['tooltip']['title'],
				$info['tooltip']['text']
			);
		}

		if ( ! empty($info['interstitial'] ) ) {
			$content = $info['interstitial'];
			$tips .= self::formatInterstitial( $content );
		}

		// Get the action URL
		$target = htmlspecialchars( $wgArticleCreationConfig['action-url'][$button] );

		$replacements = array(
			'{{SCRIPT}}' => $wgScript,
			'{{USER}}' => $wgUser,
			'{{PAGE}}' => $page,
		);

		$target = strtr( $target, $replacements );

		return <<<HTML
		<div class="ac-button-wrap">
			<a class="ac-article-button ac-button ac-button-$color ac-article-$button" data-ac-button="$button" href="$target">
				<div class="ac-arrow ac-arrow-forward">&nbsp;</div>
				<div class="ac-button-text">
					<div class="ac-button-title">$buttonTitle</div>	
					<div class="ac-button-body">$buttonText</div>
				</div>
			</a>
			$tips
		</div>
HTML;
	}

	public static function formatTooltip( $titleMsg, $bodyMsg ) {
		if ( ! $titleMsg ) {
			return '';
		}

		$title = wfMessage( $titleMsg )->parse();

		if ( $bodyMsg ) {
			$contents = wfMessage( $bodyMsg )->parse();
		} else {
			$contents = '';
		}

		return <<<HTML
			<div class="mw-ac-tip mw-ac-tooltip" style="display: none;">
				<div class="mw-ac-tooltip-pointy"></div>
				<div class="mw-ac-tooltip-innards">
					<div class="mw-ac-tooltip-title">$title</div>
					<div class="mw-ac-tooltip-body">$contents</div>
				</div>
			</div>
HTML;
	}

	public static function formatInterstitial( $content ) {
		if ( ! $content ) {
			return '';
		}

		return <<<HTML
			<div class="mw-ac-tip mw-ac-interstitial" style="display: none;">
				<div class="mw-ac-tooltip-pointy"></div>
				<div class="mw-ac-tooltip-innards">
				$content
				</div>
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



