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
							.after( ac.setupHoverTooltip( button ) )
							//attach other events here, just making first tooltip for now
							//testing hover effects
							.hover (function (){
								$( this ).next().show();
							}, function(){
								$( this ).next().hide();	
							});

						//set the pointy position 
						$tooltip = $(this).next();
						$tooltip
							.find( '.mw-ac-tooltip-pointy' )
							.css('top', (( $tooltip.height() / 2) -10) + 'px' )
							.end();
						//set the tooltip position
						$tooltip
							.css('top',  '-'+( ( ($tooltip.height() / 2 ) - ( $(this).height() / 2) ) - 10 ) + 'px');						

					});

			// setup button click states
			ac.panel
				.find('.ac-article-wizard, .ac-article-create')
				.click (function () {
						
					$('.ac-article-wizard, .ac-article-create')
						//remove green states and hide their tooltips
						.removeClass('ac-button-green')
						.each ( function (i, e) {
							$(this).next().hide();
						});

					$( this )
						//make it green
						.addClass('ac-button-green');
						//build click state here.
					
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
		
		setupHoverTooltip: function ( button ) {

			var $tooltip = $( ac.tooltip.base );
			var $tooltipInards = $( ac.tooltip[button+'Hover']);

			$tooltip
				.find ( '.mw-ac-tooltip-innards')
				.html( $tooltipInards )
				.end()
				.find( '.mw-ac-tooltip-title' )
					.text( mw.msg( 'ac-hover-tooltip-title' ) )
					.end()
				.find( '.mw-ac-tooltip-body' )
					.html( mw.msg ( 'ac-hover-tooltip-body-' + button ) )
					.end()
				.hide();

			console.log( mw.msg ('ac-hover-tooltip-title' ));

			return $tooltip;
		}

	});

	ac.init();

})( jQuery, window.mw );