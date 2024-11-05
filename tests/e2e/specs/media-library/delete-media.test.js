/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import path from 'path';

test.describe( 'Sort Media', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMedia();
		const files = [ 'tests/e2e/assets/test_data_image1.png', 'tests/e2e/assets/test-data.csv' ];

		for ( const file of files ) {
			await requestUtils.uploadMedia(
				path.resolve( process.cwd(), file )
			);
		}
	} );
	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMedia();
	} );

	test( 'Sort media by type', async ( { page, admin } ) => {
		await admin.visitAdminPage( 'upload.php?mode=list' );

		await page
			.getByRole( 'combobox', { name: 'Filter by type' } )
			.selectOption( 'post_mime_type:image' );
		await page.getByRole( 'button', { name: 'Filter' } ).click();

		await page
			.locator(
				'tr td.title.column-title.has-row-actions.column-primary'
			)
			.first()
			.hover();

		await page
			.locator( "tr[id^='post-'] a[aria-label^='Edit']" )
			.first()
			.click();

		await expect(
			page.locator( 'div.misc-pub-section.misc-pub-filetype' )
		).toHaveText( 'File type: PNG' );
	} );
} );
