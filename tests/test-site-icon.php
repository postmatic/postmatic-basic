<?php

class SiteIconTest extends WP_UnitTestCase {

	function test_default() {
		$this->assertContains(
			'prompt-site-icon-64.png',
			Prompt_Site_Icon::url(),
			'Expected fallback site icon image.'
		);
	}
}