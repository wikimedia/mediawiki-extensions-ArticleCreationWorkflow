<?php
/* 	MediaWiki ArticleCreation Extension
	Authors: Rob Moen, Benny Situ, Brandon Harris 
*/

$wgExtensionCredits['other'][] = array(
	'author' => array( 'Rob Moen', 'Benny Situ' ),
	'descriptionmsg' => 'article-creation-desc',
	'name' => 'ArticleCreation',
	'url' => 'http://www.mediawiki.org/wiki/Article_Creation_Landing_System',
	'version' => '0.1',
	'path' => __FILE__,
);

$articleCreationDir = dirname( __FILE__ ) . '/';

/* Object model */
$wgAutoloadClasses['ArticleCreationTemplates'] = $articleCreationDir . 'includes/ArticleCreationTemplates.php';

/* Special Pages */
$wgAutoloadClasses['SpecialArticleCreationLanding'] = $articleCreationDir . 'SpecialArticleCreationLanding.php';
$wgSpecialPages['ArticleCreationLanding'] = 'SpecialArticleCreationLanding';

/* Hooks */
$wgAutoloadClasses['ArticleCreationHooks'] = $articleCreationDir . 'ArticleCreationWorkflow.hooks.php';
$wgHooks['ShowMissingArticle'][] = 'ArticleCreationHooks::loadArticleCreationModules';

/* Internationalization */
$wgExtensionMessagesFiles['ArticleCreation'] = $articleCreationDir . 'ArticleCreationWorkflow.i18n.php';

/* Resources */
$acResourceTemplate = array(
	'localBasePath' => $articleCreationDir . 'modules',
	'remoteExtPath' => 'ArticleCreation/modules'
);

$wgResourceModules['ext.articleCreation.core'] = $acResourceTemplate + array (
	'styles' 	=> 'ext.articleCreation.core/ext.articleCreation.core.css',
	'scripts'	=> 'ext.articleCreation.core/ext.articleCreation.core.js',
	'dependencies' => array(
		'mediawiki.util',
		'jquery.localize',
		'user.tokens',
	),
);

$wgResourceModules['ext.articleCreation.user'] = $acResourceTemplate + array (
	'styles' 	=> 'ext.articleCreation.user/ext.articleCreation.user.css',
	'scripts'	=> 'ext.articleCreation.user/ext.articleCreation.user.js',
	'messages'  => array(
		'ac-hover-tooltip-title',
		'ac-hover-tooltip-body-normal',
		'ac-hover-tooltip-body-wizard',
		'ac-hover-tooltip-body-getOut',
	),
	'dependencies' => array(
		'ext.articleCreation.core',
	),
);

