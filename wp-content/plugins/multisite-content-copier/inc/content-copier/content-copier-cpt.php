<?php

class Multisite_Content_Copier_CPT_Copier extends Multisite_Content_Copier_Page_Copier {

	private $post_type;
	private $copy_terms;

	public function __construct( $orig_blog_id, $args ) {
		parent::__construct( $orig_blog_id, $args );

		$settings = wp_parse_args( $args, $this->get_defaults_args() );

		if ( empty( $settings['post_type'] ) )
			return false;

		extract( $settings );

		$this->post_type = $post_type;
		$this->copy_terms = $copy_terms;
	}

	protected function get_defaults_args() {
		$defaults = parent::get_defaults_args();
		$defaults['post_type'] = false;
		$defaults['copy_terms'] = false;
		return $defaults;
	}

	public function copy( $post_id ) {
		$results = parent::copy( $post_id );

		$new_post_id = $results['new_post_id'];
		$new_parent_post_id = $results['new_parent_post_id'];

		// Copy terms?
		if ( $this->copy_terms ) {

			if ( absint( $new_parent_post_id ) ) {
				// Copy parents terms
				$parent_post_id = $this->get_orig_post_parent( $post_id );
				if ( $parent_post_id )
					$this->copy_terms( $parent_post_id, $new_parent_post_id );
			}

			// Copy child terms
			$this->copy_terms( $post_id, $new_post_id );

		}

		return $results;
	}

}