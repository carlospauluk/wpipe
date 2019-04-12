( function( $, CherryJsCore ) {
	'use strict';


	CherryJsCore.utilites.namespace( 'tmAdminNotice' );
	CherryJsCore.tmAdminNotice = {
		/**
		 * Rendering notice message
		 *
		 * @param  {String} type    Message type
		 * @param  {String} message Message content
		 * @return {Void}
		 */
		noticeCreate: function( type, message, isPublicPage ) {
			var notice,
				rightDelta = 0,
				timeoutId,
				isPublic = isPublicPage || false;

			if ( ! message || 'true' === isPublic ) {
				return false;
			}

			notice = $( '<div class="cherry-handler-notice ' + type + '"><span class="dashicons"></span><div class="inner">' + message + '</div></div>' );

			$( 'body' ).prepend( notice );
			reposition();
			rightDelta = -1 * ( notice.outerWidth( true ) + 10 );
			notice.css( { 'right': rightDelta } );

			timeoutId = setTimeout( function() {
				notice.css( { 'right': 10 } ).addClass( 'show-state' );
			}, 100 );
			timeoutId = setTimeout( function() {
				rightDelta = -1 * ( notice.outerWidth( true ) + 10 );
				notice.css( { right: rightDelta } ).removeClass( 'show-state' );
			}, 15000 );
			timeoutId = setTimeout( function() {
				notice.remove();
				clearTimeout( timeoutId );
			}, 15500 );

			function reposition() {
				var topDelta = 100;

				$( '.cherry-handler-notice' ).each( function() {
					$( this ).css( { top: topDelta } );
					topDelta += $( this ).outerHeight( true );
				} );
			}
		}
	}

	CherryJsCore.utilites.namespace( 'rateForm' );
	CherryJsCore.rateForm = {
		formId: '#tm-rateform',
		sendReviewInstance: null,
		saveButtonId: '.tm-rate-form__btn',
		saveHandlerId: 'tm_rate_form_id',

		init: function() {
			if ( window[ this.saveHandlerId ] ) {
				this.sendReviewInstance = new CherryJsCore.CherryAjaxHandler( {
					handlerId: this.saveHandlerId,
					successCallback: this.sendSuccessCallback.bind( this )
				} );

				this.validateRateForm();
			}
		},

		validateRateForm: function() {

			if ( ! $.isFunction( jQuery.fn.validate ) || ! $( this.formId ).length ) {
				return ! 1;
			}

			var _this = this;

			$( this.formId ).validate({
				debug: true,
				errorElement: 'span',
				rules: {
					tm_rate_title: {
						minlength: 3
					}
				},
				submitHandler: function( form ) {
					_this.sendReviewHandler();
				}
			});
		},

		sendReviewHandler: function() {
			this.disableButton( this.saveButtonId );
			this.sendReviewInstance.sendFormData( this.formId );
		},

		sendSuccessCallback: function() {
			this.enableButton( this.saveButtonId );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' );
		},

		enableButton: function( button ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.addClass( 'success' );

			$( this.formId )[0].reset();

			timer = setTimeout(
				function() {
					$( button ).removeClass( 'success' );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'verifiedTheme' );
	CherryJsCore.verifiedTheme = {

		verifiedThemeId: 'tm_verified_theme',
		themeForm: '.tm-updates__theme-form',
		themeFormControls: '.tm-updates__theme-form-controls',
		submiteButton: '.verified-theme',

		errorClass: '.error-field',

		verifiedTheme: null,

		init: function() {
			if ( window[ this.verifiedThemeId ] ) {
				this.verifiedTheme = new CherryJsCore.CherryAjaxHandler(
						{
							handlerId: this.verifiedThemeId,
							successCallback: this.checkVerifiedThemeCallback.bind( this )
						}
					);

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.themeForm )
				.on( 'click', this.submiteButton, this.checkVerifiedTheme.bind( this ) )
				.on( 'click', this.errorClass, this.removeErrorClass.bind( this ) );
		},

		checkVerifiedTheme: function( event ) {
			var button = event.target,
				form = $( event.delegateTarget ),
				data,
				validate;

			this.disableButton( button );

			data = form.serializeArray();
			validate = this.validateForm(data);

			if ( validate ) {
				$( validate, form ).addClass( 'error-field' );
				this.enableButton( button, 'error' );

				return !1;
			}

			$( '.utb-js' )
				.not( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'disabled' );

			this.verifiedTheme.sendData( data );

			return !1;
		},

		validateForm: function( data ) {
			var lenght = data.length -1,
				className = '',
				value,
				name;

			for (; lenght >= 0; lenght-- ) {
				value = data[ lenght ].value;
				name  = data[ lenght ].name;
				if ( ! value ) {
					className += ',[name="' + name + '"]';
				}
			}
			className = className.replace( /^,/, '' );

			return className;
		},

		removeErrorClass: function( event ) {
			var input = $( event.target ),
				className = this.errorClass.replace( /^\./, '');

			input.removeClass( className );
		},

		checkVerifiedThemeCallback: function( respons ) {
			var data     = respons.data,
				form     = $( '#'+ data.slug ),
				button   = $( this.submiteButton , form),
				controls = $( this.themeFormControls, form ),
				html     = data.htmlForm;

			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			if ( ! data.verify ) {
				CherryJsCore.tmAdminNotice.noticeCreate( 'error-notice', data.message );
				this.enableButton( button, 'error' );
				return !1;
			}

			if ( html ) {
				controls
					.removeClass( 'show-form' )
					.addClass( 'hide-form' )
					.on( 'animationend', this.showControls.bind( this, controls, html ) );
			}

			CherryJsCore.tmAdminNotice.noticeCreate( 'success-notice', data.message );
			this.enableButton( $( button , form), 'success' );

			return !1;
		},

		showControls: function( controls, html ){
			controls.html( html )
				.removeClass( 'hide-form' )
				.addClass( 'show-form' );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' );
		},

		enableButton: function( button, className ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.addClass( className );

			timer = setTimeout(
				function() {
					$( button ).removeClass( className );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.utilites.namespace( 'updateTheme' );
	CherryJsCore.updateTheme = {

		ajaxHandlerId: 'tm_update_theme',
		ajaxHandler: null,

		themeForm: '.tm-updates__theme-form',
		submiteButton: '.tm-update-theme',
		continueUpdateButton: '#update-theme-continue',
		popUP: '.tm-updates-popup',
		closePopUpButton: '#update-theme-cancel',
		popUpBg: '.tm-updates-popup-background',
		themeImage: '.tm-updates__theme-image',
		imageLable: '.tm-notification-image-lable',

		errorClass: '.error-field',

		tempData: null,

		init: function() {
			if ( window[ this.ajaxHandlerId ] ) {
				this.ajaxHandler = new CherryJsCore.CherryAjaxHandler(
						{
							handlerId: this.ajaxHandlerId,
							successCallback: this.ajaxHandlerCallback.bind( this )
						}
					);

				this.addEvents();
			}
		},

		addEvents: function() {
			$( this.themeForm )
				.on( 'click', this.submiteButton, this.checkUpdateTheme.bind( this ) );

			$( 'body' )
				.on( 'click.tm-update', this.popUpBg, this.canselUpdate.bind( this ) )
				.on( 'click.tm-update', this.closePopUpButton, this.canselUpdate.bind( this ) )
				.on( 'click.tm-update', this.continueUpdateButton, this.startUpdate.bind( this ) );
		},

		checkUpdateTheme: function( event ) {
			var button = event.target,
				form = $( event.delegateTarget ),
				data = {
					slug: form.attr( 'name' ),
					version: $( '[name="version"]', form ).val(),
					updateVersion: $( '[name="update-version"]', form ).val()
				};

			this.tempData = data;
			this.disableButton( button );

			$( '.utb-js' )
				.not( button )
				.attr( 'disabled', 'disabled' )
				.addClass( 'disabled' );

			$( this.popUP ).addClass( 'show open' );
			$( window ).one( 'keyup', this.canselUpdate.bind( this ) );

			return !1;
		},

		startUpdate: function() {
			var button;

			if ( this.tempData ) {
				this.ajaxHandler.sendData( this.tempData );
				this.tempData = null;
			} else {
				this.tempData = null;
				button = $( '#check-theme-' + this.tempData.slug );
				this.enableButton( button, 'error' );
			}

			this.hidePopUp();
			return !1;
		},

		canselUpdate: function ( event ){
			if ( 'keyup' === event.type && 27 !== event.keyCode ){
				return !1;
			}

			if ( ! this.tempData ){
				return !1;
			}

			var button = $( '#check-theme-' + this.tempData.slug );
			this.tempData = null;

			this.hidePopUp();
			this.enableButton( button, 'error' );

			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			return !1;
		},

		hidePopUp: function() {
			$( this.popUP ).removeClass( 'open' );
			$( '.tm-updates-popup-inner', this.popUP ).one( 'animationend', this.hidePopUpWrapper.bind( this ) );
		},

		hidePopUpWrapper: function() {
			$( this.popUP ).removeClass( 'show' );
		},

		ajaxHandlerCallback: function( respons ) {
			var data = respons.data,
				form     = $( '#'+ data.slug ),
				controls = $( this.themeFormControls, form ),
				button = $( this.submiteButton , form ),
				timeout,
				imageLable = form.siblings( this.themeImage ).find( this.imageLable );

			if ( data.error ) {
				this.enableButton( button, 'error' );
				CherryJsCore.tmAdminNotice.noticeCreate( 'error-notice', data.message );
				return !1;
			}

			$( '.current-version', form ).html( data.updateVersion );


			$( '.utb-js' )
				.removeAttr( 'disabled' )
				.removeClass( 'disabled' );

			this.enableButton( button, 'success' );
			CherryJsCore.tmAdminNotice.noticeCreate( 'error-notice', data.message );

			timeout = setTimeout( function(){

				button
					.addClass( 'disabled' )
					.attr( 'disabled', 'disabled' )

				imageLable.remove();

				clearTimeout( timeout );
			}, 1000 );

		},

		removeErrorClass: function( event ) {
			var input = $( event.target ),
				className = this.errorClass.replace( /^\./, '');

			input.removeClass( className );
		},

		disableButton: function( button ) {
			$( button )
				.attr( 'disabled', 'disabled' );
		},

		enableButton: function( button, className ) {
			var timer = null;

			$( button )
				.removeAttr( 'disabled' )
				.addClass( className )
				.removeClass( 'utb-js' );

			timer = setTimeout(
				function() {
					$( button ).removeClass( className );
					clearTimeout( timer );
				},
				1000
			);
		}
	};

	CherryJsCore.updateTheme.init();
	CherryJsCore.verifiedTheme.init();
	CherryJsCore.rateForm.init();

}( jQuery, window.CherryJsCore ) );

