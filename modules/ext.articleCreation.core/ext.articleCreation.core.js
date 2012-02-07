/* Core JavaScript for the Article Creation Extension */

(function( mw, undefined ) {
	mw.articleCreation = {
		config: mw.config.get('acConfig'),
		tooltip: {
			/* base tooltip template */
			base: '\
				<div class="mw-ac-tip"> \
					<div class="mw-ac-tooltip-pointy"></div>\
					<div class="mw-ac-tooltip-innards">\
					</div>\
				</div>\
				',
			/* tooltip state templates */
			defaultHover: '\
				<div class="mw-ac-tooltip-title"></div>\
				<div class="mw-ac-tooltip-body"></div>\
				',
			createClick: '\
				<a class="mw-ac-help"></a>\
				<div class="mw-ac-tooltip-title"></div>\
				<div class="mw-ac-tooltip-body">\
					<div class="mw-ac-create-verbiage"></div>\
					<div class="ac-button-wrap ac-action-button">\
						<a class="ac-button-green ac-button">\
							<div class="ac-arrow ac-arrow-forward">&nbsp;</div>\
							<div class="ac-button-text">\
								<div class="ac-button-title"></div>\
								<div class="ac-button-text"></div>\
							</div>\
						</a>\
					</div>\
					<input \
						type="checkbox" \
						id="mw-ac-create-dismiss" />\
					<label for="mw-ac-create-dismiss"></label>\
					<div style="clear: both"></div>\
				</div>\
				',
			wizardClick: '' // Same as normalClick
		}
	};

})( window.mw );
