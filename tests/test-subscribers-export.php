<?php

class SubscribersExportTest extends WP_UnitTestCase {

	function test_export() {
		$prompt_site = new Prompt_Site();

		$subscriber_with_origin = $this->factory->user->create_and_get( array(
			'first_name' => 'With',
			'last_name' => 'Origin'
		) );
		$prompt_site->subscribe( $subscriber_with_origin->ID );
		$prompt_user = new Prompt_User( $subscriber_with_origin );
		$subscriber_origin_fields = array(
			'source_label' => 'Unit Tests',
			'source_url' => $prompt_site->subscription_url(),
			'agreement' => 'test agreement'
		);
		$prompt_user->set_subscriber_origin( new Prompt_Subscriber_Origin( $subscriber_origin_fields ));

		$subscriber_without_origin = $this->factory->user->create_and_get();
		$prompt_site->subscribe( $subscriber_without_origin->ID );

		$export = new Prompt_Admin_Subscribers_Export( array( 'Prompt_Site' ) );

		$csv = $export->csv();

		$this->assertStringStartsWith( 'Email Address', $csv, 'Expected CSV to begin with email address field.' );
		$this->assertContains( $subscriber_with_origin->user_email, $csv, 'Expected CSV to contain origin subscriber email.' );
		$this->assertContains( $subscriber_with_origin->first_name, $csv, 'Expected CSV to contain origin subscriber first name.' );
		$this->assertContains( $subscriber_with_origin->last_name, $csv, 'Expected CSV to contain origin subscriber first name.' );
		$this->assertContains( $subscriber_origin_fields['source_label'], $csv, 'Expected CSV to contain origin subscriber source label.' );
		$this->assertContains( $subscriber_origin_fields['source_url'], $csv, 'Expected CSV to contain origin subscriber source URL.' );
		$this->assertNotContains( $subscriber_origin_fields['agreement'], $csv, 'Expected CSV not to contain origin subscriber agreement.' );

		$this->assertContains( $subscriber_without_origin->user_email, $csv, 'Expected CSV to contain originless subscriber email.' );
		$this->assertContains(
			date( 'c', strtotime( $subscriber_without_origin->user_registered ) ),
			$csv,
			'Expected CSV to contain originless subscriber creation date.'
		);
	}
}
