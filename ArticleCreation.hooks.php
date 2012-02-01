<?php /* Article Creation Hooks */

class ArticleCreationHooks {
	
	/**
	 * Loads ArticleCreation Modules to the output on ShowMissingArticle Hook
	 *
	 * @param $page Missing Page Object
	 */
	public static function loadArticleCreationModules ( $page ) {
		global $wgOut;
		
		//dev html, need to localize
		$htmlOut = <<<HTML
			<span class="article-creation-heading">I want to...</span>
			<div id="article-creation-panel">
				<div class="ac-button-wrap">
					<a class="ac-article-button ac-button-blue ac-article-create" data-ac-button="normal">
						<div class="ac-arrow ac-arrow-forward">&nbsp;</div>
						<div class="ac-button-text">
							<div class="ac-button-title">Create this article myself</div>
							<div class="ac-button-body">I know what I'm doing</div>
						</div>
					</a>
				</div>
				<div class="ac-button-wrap">
					<a class="ac-article-button ac-button-blue ac-article-wizard" data-ac-button="wizard">
						<div class="ac-arrow ac-arrow-forward">&nbsp;</div>
						<div class="ac-button-text">
							<div class="ac-button-title">Follow a step-by-step process</div>	
							<div class="ac-button-body">I'll use the article creation wizard</div>
						</div>
					</a>
				</div>
				<div class="ac-button-wrap">
					<a class="ac-article-button ac-button-red" data-ac-button="getOut">
						<div class="ac-arrow ac-arrow-backward">&nbsp;</div>
						<div class="ac-button-text">
							<div class="ac-button-title">Get out of here</div>
							<div class="ac-button-body">Oops.  This isn't what I wanted</div>
						</div>
					</a>
				</div>
			</div>
HTML;

		//only loadiong user module now for development
		
		$wgOut->addModules( 'ext.articleCreation.core' );
		$wgOut->addModules( 'ext.articleCreation.user' );
		$wgOut->addHtml( $htmlOut );
		return true;
	}

}