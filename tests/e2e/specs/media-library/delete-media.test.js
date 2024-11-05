/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import path from 'path';

test.describe( 'Delete Media', () => {
    test.setTimeout(30000);
	test.beforeAll( async ( { requestUtils } ) => {
        await requestUtils.deleteAllMedia();
        const files = [
            'tests/e2e/assets/test_data_image1.png',
            'tests/e2e/assets/test_data_image2.png',
            'tests/e2e/assets/test_data_image3.png'
        ];

        for (const file of files) {
            await requestUtils.uploadMedia(
                path.resolve(process.cwd(), file)
            );
        }
	} );
    test.beforeEach( async ( { page } ) => {
		await page.goto("wp-admin/upload.php?mode=list")
        await page.waitForTimeout(2000);
	} );
	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllMedia();
	} );

	test( 'delete single media', async ( { page, admin } ) => {
		// Hover on the first media.
		await page
			.locator(
				'tr td.title.column-title.has-row-actions.column-primary'
			)
			.first()
			.hover();
		page.once( 'dialog', ( dialog ) => {
			dialog
				.accept()
				.catch( ( err ) =>
					console.error( 'Dialog accept failed:', err )
				);
		} );
		await page
			.locator( "tr[id^='post-'] a[aria-label^='Delete']" )
			.first()
			.click();
        
            
        const deletionMessage = await page.locator('#message p').evaluate((element) => element.innerHTML);
        expect(deletionMessage).toContain('Media file permanently deleted.');
	} );

	test( 'delete Bulk media', async ( { page, admin } ) => {

		// Select the multiple media from the list.
		await page.locator( 'input[name="media[]"]' ).first().click();
		await page.locator( 'input[name="media[]"]' ).nth( 1 ).click();

		await page
			.locator( '#bulk-action-selector-top' )
			.selectOption( 'delete' );

		page.once( 'dialog', ( dialog ) => {
			dialog
				.accept()
				.catch( ( err ) =>
					console.error( 'Dialog accept failed:', err )
				);
		} );

		await page.getByRole( 'button', { name: 'Apply' } ).first().click();
     
        const deletionMessage = await page.locator('#message p').evaluate((element) => element.innerHTML);
        expect(deletionMessage).toContain('2 media files permanently deleted.');
	} );
} );