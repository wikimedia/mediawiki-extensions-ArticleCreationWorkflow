/* JavaScript for the Article Creation Extension */

(function( $, mw, undefined ) {
	
	var ac = mw.articleCreation;

	$.extend(ac, {
		init: function() {
			//store a reference to the panel
			ac.panel = $('#article-creation-panel');

			ac.setupTooltips();

			$(document).click( function(e) {
				if ( $(e.target).is('.mw-ac-interstitial *' ) ) {
					return;
				}

				ac.hideInterstitial( $('.ac-article-button') );
					
				ac.panel
					.find('.mw-ac-interstitial')
					.hide();
			} );

			$(document).keydown( function(e) {
				if ( e.keyCode == 27 ) {// ESC
					ac.hideInterstitial( $('.ac-article-button') );
				}
			});

			ac.panel.find('.ac-button').button( {} );
			ac.panel.find('.ac-action-button').addClass('ui-button-green');

			//setup button hover states
			ac.panel
				.find( '.ac-article-button' )
					.addClass('ui-button-blue')
					//attach other events here, just making first tooltip for now
					//testing hover effects
					.hover (function (){
						if ( $('.ac-button-selected') )
							return;
							
						$( this ).parent()
						.find('.mw-ac-tooltip')
						.show();
					}, function(){
						if ( $(this).hasClass('ac-button-selected') )
							return;
						$( this ).parent()
						.find('.mw-ac-tooltip')
						.hide();
					})
					.each( function (i, e) {
						var		button = $(this).data('ac-button');

						//set the pointy position
						var $button = $(this);
						
						$button.parent().find('.mw-ac-tip').each(
							function() {
								ac.setupTipHeights( $(this), $button );
							} );
					})
				// Click states
				.click (function (e) {
					e.preventDefault();
					e.stopPropagation();

					var alreadySelected = $(this)
						.hasClass('ac-button-selected');

					ac.hideInterstitial($('.ac-article-button'));
					$('.ac-article-button').not($(this))
								.addClass('ac-faded');
					
					ac.panel
						.find('.mw-ac-interstitial')
						.hide();

					if ( alreadySelected ) {
						return;
					}

					if ( ! $(this).parent().find('.mw-ac-interstitial').length ||
						ac.isInterstitialDisabled($(this).data('ac-button'))
					) {
						ac.executeAction( $(this).data('ac-button' ) );
						return;
					}

					var article = wgPageName.substr( wgPageName.indexOf('/') + 1 );
					ac.trackAction( article, $(this).data('ac-button' ) + '_button_click' );

					$( this )
						//make it green
						.removeClass('ui-button-blue')
						.addClass('ui-button-green')
						.addClass('ac-button-selected')
						.parent()
						.find('.mw-ac-tooltip' )
							.hide()
							.end()
						.find('.mw-ac-interstitial')
							.show();
					
				})
				// Hover states
				.hover (function (){
					$( '.ac-article-button' )
						.not( this )
						.removeClass( 'ac-button-hover' );
					$(this).addClass('ac-button-hover');
				}, function(){
					$( '.ac-article-button' )
						.removeClass( 'ac-button-hover' );
				});

		},
		
		setupTooltips: function ( ) {

			ac.panel.find('.mw-ac-interstitial')
				.each( function() {
					var button = $(this)
						.parent()
						.find('.ac-article-button')
						.data('ac-button');

					var $content = $( ac.config.buttons[ac.config.variant][button].interstitial );

					$content.localize();

					$(this).find('.mw-ac-tooltip-innards')
						.append($content);
				} );
			
			ac.panel.find('.mw-ac-interstitial')
				.find('.ac-action-button')
				.click( function(e) {
					e.preventDefault();
					e.stopPropagation();
					ac.executeAction($(this).data('ac-action'));
				} );
		},
		
		setupTipHeights : function( $tooltip, $button ) {
			$tooltip
				.find( '.mw-ac-tooltip-pointy' )
				.css('top', (( $tooltip.height() / 2) -10) + 'px' )
				.end();
			//set the tooltip position
			var newPosition = ($tooltip.height() / 2 ) -
					($button.height() / 2) - 10;
			$tooltip.css('top',  newPosition+'px');
		},
		
		executeAction : function( action ) {
			if ( $('.ac-dismiss-interstitial').is(':checked') ) {
				ac.disableInterstitial( action );
			}
			
			var acwsource = '';
			var buttonType = 'button_click';
			if ( action === 'create' ) {
				if ( ac.panel.find('.mw-ac-interstitial').is(':visible') ) {
					buttonType = 'submit';
					if ( $('.ac-dismiss-interstitial').is(':checked') ) {
						acwsource = 'skip';
						buttonType = acwsource + '_' + buttonType;
					}
				} else {
					acwsource = 'direct';
					buttonType = acwsource + '_' + buttonType;
				}
			}

			var article = wgPageName.substr( wgPageName.indexOf('/') + 1 );
			var urlTemplate = ac.config['action-url'][action];

			urlTemplate = urlTemplate.replace( '{{PAGE}}', encodeURIComponent( article ) );
			urlTemplate = urlTemplate.replace( '{{USER}}', encodeURIComponent( wgUserName ) );
			urlTemplate = urlTemplate.replace( '{{SCRIPT}}', wgScript );
			if ( action === 'create' ) {
				urlTemplate = urlTemplate.replace( '{{BUCKETID}}', encodeURIComponent( ac.config['acwbucket'] ) );
				urlTemplate = urlTemplate.replace( '{{SOURCE}}', encodeURIComponent( acwsource ) );
			}
			
			ac.trackAction(article, action + '_' + buttonType)
				.complete( function() {
					window.location.href = urlTemplate;
				});
		},

		disableInterstitial : function(button) {
			$.cookie( 'mw:ac:disabled-interstitial:'+button, 1,
				{ expires : 365, path : '/' } );
		},

		isInterstitialDisabled : function(button) {
			if ( $.cookie('mw:ac:disabled-interstitial:'+button) ) {
				return true;
			}

			return false;
		},

		trackAction : function(article, action) {
			if ( ac.config['tracking-turned-on'] ) {
				// Split up article into namespace and title
				var	namespace = article.substr( 0, article.indexOf(':') ),
					title = article.substr( article.indexOf(':') + 1 ),
					namespaceNumber;

				namespace = namespace.toLowerCase();
				namespaceNumber = mw.config.get('wgNamespaceIds')[namespace];

				if ( typeof namespaceNumber === 'undefined' ) {
					namespace = '';
					namespaceNumber = 0;
					title = article;
				}

				// Normalise title
				title = title.charAt(0).toUpperCase() + title.substr(1);
				title = title.replace(' ', '_' );

				return jQuery.trackActionWithOptions( {
					id : ac.config['tracking-code-prefix'] + ac.config['acwbucket'] + '-' + action,
					namespace : namespaceNumber,
					info : title
				} );
			}
		},

		hideInterstitial : function($elements) {
			//remove green states and hide their tooltips
			$elements
				.removeClass('ui-button-green')
				.removeClass('ac-button-selected')
				.each ( function (i, e) {
					var color = $(this).data('ac-color');
					$(this) .addClass( 'ui-button-'+color )
						.parent()
						.find('.mw-ac-tooltip,.mw-ac-interstitial')
						.hide();
				});
			$('.ac-article-button').removeClass('ac-faded');
		}

	});

	ac.init();

})( jQuery, window.mw );
