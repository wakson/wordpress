// TODO: Check for `!! window.navigation && 'CSSViewTransitionRule' in window`.

const determineTransitionType = ( oldNavigationEntry, newNavigationEntry ) => {
	if ( ! oldNavigationEntry || ! newNavigationEntry ) {
		return 'unknown';
	}

	const currentURL = new URL( oldNavigationEntry.url );
	const destinationURL = new URL( newNavigationEntry.url );

	const currentPathname = currentURL.pathname;
	const destinationPathname = destinationURL.pathname;

	if ( currentPathname !== destinationPathname ) {
		// If post URLs start with a date, use that to determine "order".
		const currentDateMatches = currentPathname.match( /^\/(\d{4})\/(\d{2})\/(\d{2})\// );
		const destinationDateMatches = destinationPathname.match( /^\/(\d{4})\/(\d{2})\/(\d{2})\// );
		console.log( currentPathname );
		console.log( destinationPathname );
		if ( currentDateMatches && destinationDateMatches ) {
			const currentDate = new Date( parseInt( currentDateMatches[ 1 ] ), parseInt( currentDateMatches[ 2 ] ) - 1, parseInt( currentDateMatches[ 3 ] ) );
			const destinationDate = new Date( parseInt( destinationDateMatches[ 1 ] ), parseInt( destinationDateMatches[ 2 ] ) - 1, parseInt( destinationDateMatches[ 3 ] ) );
			if ( currentDate < destinationDate ) {
				return 'forwards';
			}
			if ( currentDate > destinationDate ) {
				return 'backwards';
			}
			return 'unknown';
		}

		// Otherwise, check URL "hierarchy".
		if ( destinationPathname.startsWith( currentPathname ) ) {
			return 'forwards';
		}
		if ( currentPathname.startsWith( destinationPathname ) ) {
			return 'backwards';
		}
	}

	return 'unknown';
};

window.addEventListener( 'pageswap', async ( e ) => {
	if ( e.viewTransition ) {
		const transitionType = determineTransitionType( e.activation.from, e.activation.entry );

		console.log( `pageSwap: ${ transitionType }` );
		e.viewTransition.types.add( transitionType );
	}
} );

window.addEventListener( 'pagereveal', async ( e ) => {
	console.log( 'pageRevealEvent', e );
	if ( e.viewTransition ) {
		const transitionType = determineTransitionType( navigation.activation.from, navigation.activation.entry );

		console.log( `pageReveal: ${ transitionType }` );
		e.viewTransition.types.add( transitionType );
	}
} );
