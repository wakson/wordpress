import {
    visitAdminPage,
    createNewPost,
    trashAllPosts,
    publishPost
} from '@wordpress/e2e-test-utils';

describe( 'Restore trash post', () => {
	beforeEach( async () => {
		await trashAllPosts();
	} );
	
    it( 'displays a message in the posts table when no posts are present', async () => {
		await visitAdminPage( '/edit.php' );
		const noPostsMessage = await page.$x(
			'//td[text()="No posts found."]'
		);
		expect( noPostsMessage.length ).toBe( 1 );
	} );

    it( 'Restore trash post', async () => {
    
        //create a Post
        const title = 'Test Title';
        await createNewPost( { title } );
        await publishPost();

        await visitAdminPage( '/edit.php' );  

        // Move one post to trash
        await page.waitForSelector( '#the-list .type-post' );
        await page.hover('.row-title');
        await page.click("a[aria-label='Move “Test Title” to the Trash']");

        // Remove post from trash 
	await page.click("a[href='edit.php?post_status=trash&post_type=post']");
        await page.waitForSelector( '#the-list .type-post' );
        await page.hover('.page-title');
        await page.click(".untrash"); 

        // expect for sucess message for trashed post. 
        const noPostsMessage = await page.waitForSelector(
            "div[id='message'] p:nth-child(1)"
        );

        expect(
            await noPostsMessage.evaluate( ( element ) => element.innerText )
        ).toBe( '1 post restored from the Trash. Edit Post' );
    } );
} );
