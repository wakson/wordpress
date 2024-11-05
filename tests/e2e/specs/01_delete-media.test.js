/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Delete User', () => {

	test( 'Delete Bulk Users', async ( { page, admin,requestUtils } ) => {
        requestUtils.createUser( {
			username: `test${Math.floor(Math.random() * 100000)}`,
			email: `test${Math.floor(Math.random() * 100000)}@gmail.com`,
			password: 'admin',
			roles: 'subscriber',
		} );
		await admin.visitAdminPage( '/users.php' );
        console.log('Session Cookies:', await page.context().cookies());

		await page.getByRole( 'link', { name: 'Subscriber (1)' } ).click();
		await page.getByLabel( 'Select test' ).check();
		await page
			.locator( '#bulk-action-selector-top' )
			.selectOption( 'delete' );
		await page.locator( '#doaction' ).click();
		await page.getByRole( 'button', { name: 'Confirm Deletion' } ).click();

		// Expect successful user deletion
		await expect( page.locator( '#message > p' ) ).toHaveText( /deleted./ );
	} );
} );