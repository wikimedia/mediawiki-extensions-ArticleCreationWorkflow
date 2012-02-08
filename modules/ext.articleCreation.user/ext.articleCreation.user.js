/* JavaScript for the Article Creation Extension */

(function( $, mw, undefined ) {
	
	var ac = mw.articleCreation;

	$.extend(ac, {
		init: function() {
			//store a reference to the panel
			ac.panel = $('#article-creation-panel');

			//setup button hover states
			ac.panel
				.find( '.ac-article-button' )
					.each( function (i, e) {
						var		button = $(this).data('ac-button'),
								$tooltip;

						$(this)
							.after( ac.setupTooltips( button ) )
							//attach other events here, just making first tooltip for now
							//testing hover effects
							.hover (function (){
								if ( $(this).hasClass('ac-button-selected') )
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
							});

						//set the pointy position
						var $button = $(this);
						
						$button.parent().find('.mw-ac-tip').each(
							function() {
								ac.setupTipHeights( $(this), $button );
							} );
					});

			// setup button click states
			ac.panel
				.find('.ac-article-button')
				.click (function () {
						
					$('.ac-article-button')
						//remove green states and hide their tooltips
						.removeClass('ac-button-green')
						.removeClass('ac-button-selected')
						.each ( function (i, e) {
							$(this) .parent()
								.find('.mw-ac-tooltip')
								.hide();
						});
						
					ac.panel
						.find('.mw-ac-clicktip')
						.hide();

					if ( ! $(this).parent().find('.mw-ac-clicktip').length ) {
						ac.executeAction( $(this).data('ac-button' ) );
						return;
					}

					$( this )
						//make it green
						.addClass('ac-button-green')
						.addClass('ac-button-selected')
						.parent()
						.find('.mw-ac-tooltip' )
							.hide()
							.end()
						.find('.mw-ac-clicktip')
							.show();
					
				});

			//setup hover / fade effects
			ac.panel
				.find('.ac-article-button')
				.hover (function (){
					$( '.ac-article-button' )
						.not( this )
						.addClass( 'ac-faded' );
				}, function(){
					$( '.ac-article-button' )
						.removeClass( 'ac-faded' );
				});

		},
		
		setupTooltips: function ( button ) {

			var $tooltip = $( ac.tooltip.base );
			var $tooltipInnards = $( ac.tooltip[button+'Hover'] );
			var $clicktip = $( ac.tooltip.base );
			var $clicktipInnards = $( ac.tooltip[button+'Click'] );

			if ( ! $tooltipInnards.length ) {
				$tooltipInnards = $( ac.tooltip['defaultHover'] );
			}

			$tooltip
				.find ( '.mw-ac-tooltip-innards')
					.html( $tooltipInnards )
				.end()
				.find( '.mw-ac-tooltip-title' )
					.text( mw.msg( 'ac-hover-tooltip-title' ) )
					.end()
				.find( '.mw-ac-tooltip-body' )
					.html( mw.msg ( 'ac-hover-tooltip-body-' + button ) )
					.end()
				.hide()
				.addClass( 'mw-ac-tooltip' );
			
			if ( $clicktipInnards.length ) {
				$clicktip
					.find( '.mw-ac-tooltip-innards' )
						.html( $clicktipInnards )
						.end()
					.find( '.mw-ac-tooltip-title' )
						.text( mw.msg('ac-click-tip-title-'+button) )
						.end()
					.addClass( 'mw-ac-clicktip' )
					.hide();
					
				if ( button == 'create' ) {
					$clicktip
						.find('.ac-button-title')
							.html( mw.msg( 'ac-create-button' ) )
							.end()
						.find('.mw-ac-create-verbiage')
							.html( mw.msg( 'ac-create-warning-'+button ) )
							.end()
						.find('label')
							.html( mw.msg( 'ac-create-dismiss' ) )
							.end()
						.find('.ac-action-button')
							.click( function(e) {
								e.preventDefault();
								ac.executeAction(button);
							} )
							.end()
						.find('.mw-ac-help')
							.html( mw.msg( 'ac-create-help' ) )
							.attr( 'href', ac.config['create-help-url'])
							.end();
				}
			} else {
				$clicktip = $('');
			}
			return $tooltip.add( $clicktip );
		},
		
		setupTipHeights : function( $tooltip, $button ) {
			$tooltip
				.find( '.mw-ac-tooltip-pointy' )
				.css('top', (( $tooltip.height() / 2) -10) + 'px' )
				.end();
			//set the tooltip position
			var newPosition = ($button.height() / 2) -
					($tooltip.height() / 2 ) + 10;
			$tooltip.css('top',  newPosition+'px');
		},
		
		executeAction : function( action ) {
			var article = wgPageName.substr( wgPageName.indexOf('/') + 1 );
			var urlTemplate = ac.config['action-url'][action];

			urlTemplate = urlTemplate.replace( '{{PAGE}}', encodeURIComponent( article ) );
			urlTemplate = urlTemplate.replace( '{{USER}}', encodeURIComponent( wgUserName ) );
			urlTemplate = urlTemplate.replace( '{{SCRIPT}}', wgScript );

			window.location.href = urlTemplate;
		}

	});

	ac.init();

})( jQuery, window.mw );
