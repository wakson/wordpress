/**
 * WordPress inline HTML embed
 *
 * @since 4.4.0
 * @output wp-includes/js/wp-embed.js
 *
 * This file cannot have ampersands in it. This is to ensure
 * it can be embedded in older versions of WordPress.
 * See https://core.trac.wordpress.org/changeset/35708.
 */
(function ( window, document ) {
	'use strict';

	// Abort for ancient browsers.
	if ( ! document.querySelector || ! window.addEventListener ) {
		return;
	}

	/** @namespace wp */
	window.wp = window.wp || {};

	// Abort if script was already executed.
	if ( !! window.wp.receiveEmbedMessage ) {
		return;
	}

	/**
	 * Receive embed message.
	 *
	 * @param {MessageEvent} e
	 */
	window.wp.receiveEmbedMessage = function( e ) {
		var data = e.data;

		if ( ! data ) {
			return;
		}

		if ( ! ( data.secret || data.message || data.value ) ) {
			return;
		}

		if ( /[^a-zA-Z0-9]/.test( data.secret ) ) {
			return;
		}

		var iframes = document.querySelectorAll( 'iframe[data-secret="' + data.secret + '"]' ),
			blockquotes = document.querySelectorAll( 'blockquote[data-secret="' + data.secret + '"]' ),
			allowedProtocols = new RegExp( '^https?:$', 'i' ),
			i, source, height, sourceURL, targetURL;

		for ( i = 0; i < blockquotes.length; i++ ) {
			blockquotes[ i ].style.display = 'none';
		}

		for ( i = 0; i < iframes.length; i++ ) {
			source = iframes[ i ];

			if ( e.source !== source.contentWindow ) {
				continue;
			}

			source.removeAttribute( 'style' );

			/* Resize the iframe on request. */
			if ( 'height' === data.message ) {
				height = parseInt( data.value, 10 );
				if ( height > 1000 ) {
					height = 1000;
				} else if ( ~~height < 200 ) {
					height = 200;
				}

				source.height = height;
			}

			/* Link to a specific URL on request. */
			if ( 'link' === data.message ) {
				sourceURL = new URL( source.getAttribute( 'src' ) );
				targetURL = new URL( data.value );

				/* Only follow link if the protocol is in the allow list. */
				if ( ! allowedProtocols.test( targetURL.protocol ) ) {
					continue;
				}

				/* Only continue if link hostname matches iframe's hostname. */
				if ( targetURL.host === sourceURL.host ) {
					if ( document.activeElement === source ) {
						window.top.location.href = data.value;
					}
				}
			}
		}
	};

	function onLoad() {
		var iframes = document.querySelectorAll( 'iframe.wp-embedded-content' ),
			i, source, secret;

		for ( i = 0; i < iframes.length; i++ ) {
			/** @var {IframeElement} */
			source = iframes[ i ];

			secret = source.getAttribute( 'data-secret' );
			if ( ! secret ) {
				/* Add secret to iframe */
				secret = Math.random().toString( 36 ).substring( 2, 12 );
				source.src += '#?secret=' + secret;
				source.setAttribute( 'data-secret', secret );
			}

			/*
			 * Let post embed window know that the parent is ready for receiving the height message, in case the iframe
			 * loaded before wp-embed.js was loaded. When the ready message is received by the post embed window, the
			 * window will then (re-)send the height message right away.
			 */
			source.contentWindow.postMessage( {
				message: 'ready',
				secret: secret
			}, '*' );
		}
	}

	window.addEventListener( 'message', window.wp.receiveEmbedMessage, false );
	document.addEventListener( 'DOMContentLoaded', onLoad, false );
})( window, document );
