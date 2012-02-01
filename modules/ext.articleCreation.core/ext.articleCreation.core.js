/* Core JavaScript for the Article Creation Extension */

(function( mw, undefined ) {
	mw.articleCreation = {
		tooltip: {
			/* base tooltip template */
			base: '\
				<div class="mw-ac-tooltip"> \
					<div class="mw-ac-tooltip-pointy"></div>\
					<div class="mw-ac-tooltip-innards">\
					</div>\
				</div>\
				',
			/* tooltip state templates */
			normalHover: '\
				<div class="mw-ac-tooltip-title"></div>\
				<div class="mw-ac-tooltip-body"></div>\
				',
			normalClick: '',

			wizardHover: '\
				<div class="mw-ac-tooltip-title"></div>\
				<div class="mw-ac-tooltip-body"></div>\
				',
			wizardClick: '',
			
			getOutHover: '\
				<div class="mw-ac-tooltip-title"></div>\
				<div class="mw-ac-tooltip-body"></div>\
				'

		}
	};

})( window.mw );