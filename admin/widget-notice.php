<?php

class Prompt_Admin_Widget_Notice extends Prompt_Admin_Conditional_Notice {

	/** @var string override the option key */
	protected $skip_option_key = 'skip_widget_intro';

	/**
	 * Render content appropriate to current widget usage.
	 *
	 * If the subscribe widget has been placed, dismiss and render nothing.
	 *
	 * If there are no sidebars, render a message about that.
	 *
	 * If there are sidebars, suggest placing the widget and provide a link.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function render() {

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return '';
		}

		if ( $this->is_subscribe_widget_in_use() ) {
			$this->dismiss();
			return '';
		}

		$sidebars = wp_get_sidebars_widgets();

		if ( empty( $sidebars ) ) {
			$message =  __(
				'Your current theme is missing widget areas. This means you\'ll have to use the template tag to display the Postmatic Subscription widget.',
				'Postmatic'
			);

			$message .=  '&nbsp' . html( 'pre class="code"',
				htmlentities(
					'<?php the_widget( \'Prompt_Subscribe_Widget\', array( \'title\' => \'Subscribe by email\', \'collect_name\' => false ) ); ?>'
				)
			);
		} else {
			$message =  __(
				'To get started now, place the Postmatic Subscribe widget where people can use it to subscribe!',
				'Postmatic'
			);
			$message .= '&nbsp;' . html( 'a',
				array( 'href' => admin_url( 'widgets.php' ), 'class' => 'button' ),
				__( 'Visit Your Widgets', 'Postmatic' )
			);
		}

		return $this->render_message( $message );
	}

	/**
	 * @since 1.2.3
	 *
	 * @return bool
	 */
	protected function is_subscribe_widget_in_use() {
		$sidebars_widgets = wp_get_sidebars_widgets();

		if ( !$sidebars_widgets )
			return false;

		$subscribe_widget_in_use = false;
		foreach( $sidebars_widgets as $widgets ) {

			if ( $this->contains_subscribe_widget( $widgets ) )
				$subscribe_widget_in_use = true;

		}
		return $subscribe_widget_in_use;
	}

	/**
	 * @since 1.2.3
	 * @param $widgets
	 * @return bool
	 */
	protected function contains_subscribe_widget( $widgets ) {

		if ( !is_array( $widgets ) )
			return false;

		foreach ( $widgets as $widget ) {
			if ( strpos( $widget, 'prompt_subscribe_widget' ) === 0 )
				return true;
		}

		return false;
	}

}