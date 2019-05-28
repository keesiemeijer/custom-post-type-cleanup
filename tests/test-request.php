<?php
/**
 * Test case for request.
 *
 * @group Request
 */
class CPTC_Test_Request extends CPTC_Post_Type_Cleanup_UnitTestCase {

	/**
	 * Test default batch size.
	 */
	function test_default_batch_size() {
		$this->assertEquals( 100, cptc_get_batch_size( 'cpt' ) );
	}

	/**
	 * Test batch size set with $_POST.
	 */
	function test_default_batch_size_post() {
		$this->mock_admin_page_globals();
		$_POST['cptc_batch_size'] = 200;
		$this->assertEquals( 200, cptc_get_batch_size( 'cpt' ) );
	}

	/**
	 * Test invalid batch size.
	 */
	function test_default_batch_size_post_invalid() {
		$this->mock_admin_page_globals();
		$_POST['cptc_batch_size'] = 'invalid';
		$this->assertEquals( 100, cptc_get_batch_size( 'cpt' ) );
	}
}
