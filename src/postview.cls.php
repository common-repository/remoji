<?php
/**
 * Postview class
 *
 * @since 1.0
 */
namespace remoji;
defined( 'WPINC' ) || exit;

class Postview extends Instance {
	protected static $_instance;

	const POST_META = 'remoji.postview';

	/**
	 * Init
	 *
	 * @access public
	 */
	public function init() {
		add_action( 'remoji_postview', array( $this, 'show_num' ) );

		add_shortcode( 'views', array( $this, 'shortcode' ) );

		// Show counter in theme navigation bar
		if ( Conf::val( 'postview_show_in_themebar' ) ) {
			add_action( 'twentytwenty_end_of_post_meta_list', array( $this, 'show_num_in_theme' ) );
		}

		// Show counter under the content
		if ( Conf::val( 'postview_show_in_content' ) ) {
			add_filter( 'the_content', array( $this, 'show_in_content' ), 9 );
		}
	}

	/**
	 * Shortcode
	 */
	public function shortcode( $attrs ) {
		$post_id = ! empty( $attrs[ 'id' ] ) ? (int) $attrs[ 'id' ] : 0;

		return '<span class="remoji_counter_shortcode">' . $this->get_num( $post_id ) . '</span>';
	}

	/**
	 * Show counter under content
	 */
	public function show_in_content( $content ) {
		$content .= '<p class="remoji_counter_content">' . $this->show_num( false, true ) . '</p>';

		return $content;
	}

	/**
	 * Show counter in theme
	 */
	public function show_num_in_theme( $post_id ) {
		echo '<li class="post-view meta-wrapper">' . $this->show_num( $post_id, true ) . '</li>';
	}

	/**
	 * Get post number
	 */
	public function get_num( $post_id = false ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		if ( ! $post_id ) {
			return false;
		}

		$num = (int) get_post_meta( $post_id, self::POST_META, true );
		$num = number_format_i18n( $num );

		return $num;
	}

	/**
	 * Show the post counter
	 */
	public function show_num( $post_id = false, $return_only = false ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		if ( ! $post_id ) {
			return false;
		}

		$num = $this->get_num( $post_id );

		$tpl = Conf::val( 'postview_tpl' );
		$tpl = str_replace( array( '{post_id}', '{num}' ), array( $post_id, $num ), $tpl );
		$tpl = apply_filters( 'the_views', $tpl );

		if ( $return_only ) {
			return $tpl;
		}
		echo $tpl;
	}

	/**
	 * Count post visit
	 */
	public function count_num() {
		if ( empty( $_POST[ 'post_id' ] ) ) {
			return REST::err( 'no_id' );
		}
		$post_id = (int) $_POST[ 'post_id' ];

		$num = get_post_meta( $post_id, self::POST_META, true );
		$num ++;
		update_post_meta( $post_id, self::POST_META, $num );

		return REST::ok( array( 'num' => $num ) );
	}

}