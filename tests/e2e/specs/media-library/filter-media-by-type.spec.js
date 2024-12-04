/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );
import path from 'path';

test.describe( 'Filter wp-media-library by type test', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		// delete all media
		await requestUtils.deleteAllMedia();

		// upload files
		const files = [
			'tests/e2e/assets/test-data.jpg',
			'tests/e2e/assets/test-data1.jpg',
		];

		for ( const file of files ) {
			await requestUtils.uploadMedia(
				path.resolve( process.cwd(), file )
			);
		}
	} );

	test( 'Should be able to filter the media based on media type', async ( {
		page,
		admin,
	} ) => {
		// navigate to url
		await admin.visitAdminPage( '/upload.php?mode=grid' );

		// validate media by audio
		await page
			.getByRole( 'combobox', { name: 'Filter by type' } )
			.selectOption( 'audio' );

		// validate media does not exist
		await expect( page.locator( '.no-media' ) ).toHaveText(
			'No media items found.'
		);

		// validate filter by image
		await page
			.getByRole( 'combobox', { name: 'Filter by type' } )
			.selectOption( 'image' ); // Select filter as a Image

		await page.locator( '.thumbnail' ).first().click(); // Open the Image.

		await expect( page.locator( "div[class='file-type']" ) ).toHaveText(
			'File type: image/jpeg'
		);
	} );
} );
