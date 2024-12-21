/**
 * WordPress dependencies
 */
import { test } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { camelCaseDashes, locales } from '../utils';

const results = {
	timeToFirstByte: [],
};

test.describe( 'Admin', () => {
	for ( const locale of locales ) {
		test.describe( `Locale: ${ locale }`, () => {
			test.beforeAll( async ( { requestUtils } ) => {
				await requestUtils.activateTheme( 'twentytwentyone' );
				await requestUtils.updateSiteSettings( {
					language: 'en_US' === locale ? '' : locale,
				} );
			} );

			test.afterAll( async ( { requestUtils }, testInfo ) => {
				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );

				await requestUtils.updateSiteSettings( {
					language: '',
				} );

				results.timeToFirstByte = [];
			} );

			test.afterAll( async ( {}, testInfo ) => {
				await testInfo.attach( 'results', {
					body: JSON.stringify( results, null, 2 ),
					contentType: 'application/json',
				} );
			} );

			const iterations = Number( process.env.TEST_RUNS );
			for ( let i = 1; i <= iterations; i++ ) {
				test( `Measure load time metrics (${ i } of ${ iterations })`, async ( {
					page,
					admin,
					metrics,
				} ) => {
					// Clear caches using the clear-cache.php mu-plugin. Not actually loading the page.
					await page.goto( '/?clear_cache' );

					// This is the actual page to test.
					await admin.visitAdminPage( '/' );

					const serverTiming = await metrics.getServerTiming();

					for ( const [ key, value ] of Object.entries(
						serverTiming
					) ) {
						results[ camelCaseDashes( key ) ] ??= [];
						results[ camelCaseDashes( key ) ].push( value );
					}

					const ttfb = await metrics.getTimeToFirstByte();
					results.timeToFirstByte.push( ttfb );
				} );
			}
		} );
	}
} );
