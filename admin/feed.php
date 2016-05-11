<?php

class Prompt_Admin_Feed {

	/** @var string */
	protected $url;
	/** @var  SimplePie */
	protected $feed;

	public function __construct( $url ) {
		$this->url = $url;
	}

	public function feed() {
		return $this->fetch_feed();
	}

	public function item_content( $index = 0 ) {

		$this->fetch_feed();

		if ( ! $this->feed )
			return false;

		/** @var SimplePie_Item[] $items */
		$items = $this->feed->get_items( $index, 1 );

		if ( empty( $items ) )
			return false;

		return $items[0]->get_content();
	}

	protected function fetch_feed() {

		if ( ! $this->feed ) {

			$this->feed = fetch_feed( $this->url );

			if ( is_wp_error( $this->feed ) )
				$this->feed = null;

		}

		return $this->feed;
	}
}