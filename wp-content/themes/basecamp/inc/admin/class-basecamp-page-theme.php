<?php
/**
 * Page → Theme assignment.
 *
 * Adds a "Theme" meta box to the page editor and a sortable "Theme" column
 * to the Pages list table so editors can see at a glance which installed
 * theme each page belongs to.
 *
 * Meta key: _basecamp_page_theme  (stores theme stylesheet slug, e.g. "kaneism")
 *
 * @package Basecamp\Admin
 */

declare(strict_types=1);

namespace Basecamp\Admin;

final class PageTheme {

	private const META_KEY = '_basecamp_page_theme';

	public function __construct() {
		add_action( 'add_meta_boxes_page',        [ $this, 'add_meta_box' ] );
		add_action( 'save_post_page',             [ $this, 'save_meta' ], 10, 2 );
		add_filter( 'manage_pages_columns',       [ $this, 'add_column' ] );
		add_action( 'manage_pages_custom_column', [ $this, 'render_column' ], 10, 2 );
		add_filter( 'manage_edit-page_sortable_columns', [ $this, 'sortable_column' ] );
		add_action( 'pre_get_posts',              [ $this, 'handle_sort' ] );
	}

	// -------------------------------------------------------------------------
	// Meta box
	// -------------------------------------------------------------------------

	public function add_meta_box(): void {
		add_meta_box(
			'basecamp_page_theme',
			__( 'Assigned Theme', 'basecamp' ),
			[ $this, 'render_meta_box' ],
			'page',
			'side',
			'default'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		$current = get_post_meta( $post->ID, self::META_KEY, true );
		$themes  = wp_get_themes( [ 'errors' => false ] );

		wp_nonce_field( 'basecamp_page_theme_nonce', 'basecamp_page_theme_nonce' );
		?>
		<label for="basecamp_page_theme" class="screen-reader-text"><?php esc_html_e( 'Select theme', 'basecamp' ); ?></label>
		<select name="basecamp_page_theme" id="basecamp_page_theme" style="width:100%">
			<option value=""><?php esc_html_e( '— None —', 'basecamp' ); ?></option>
			<?php foreach ( $themes as $slug => $theme ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
					<?php echo esc_html( $theme->get( 'Name' ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		if (
			! isset( $_POST['basecamp_page_theme_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['basecamp_page_theme_nonce'] ), 'basecamp_page_theme_nonce' ) ||
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
			! current_user_can( 'edit_page', $post_id )
		) {
			return;
		}

		$value = isset( $_POST['basecamp_page_theme'] ) ? sanitize_text_field( wp_unslash( $_POST['basecamp_page_theme'] ) ) : '';

		if ( '' === $value ) {
			delete_post_meta( $post_id, self::META_KEY );
		} else {
			update_post_meta( $post_id, self::META_KEY, $value );
		}
	}

	// -------------------------------------------------------------------------
	// Column
	// -------------------------------------------------------------------------

	/** @param array<string,string> $columns */
	public function add_column( array $columns ): array {
		// Insert after 'title'
		$out = [];
		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'title' === $key ) {
				$out['page_theme'] = __( 'Theme', 'basecamp' );
			}
		}
		return $out;
	}

	public function render_column( string $column, int $post_id ): void {
		if ( 'page_theme' !== $column ) {
			return;
		}

		$slug = get_post_meta( $post_id, self::META_KEY, true );
		if ( ! $slug ) {
			echo '<span aria-hidden="true">—</span>';
			return;
		}

		$theme = wp_get_theme( $slug );
		echo $theme->exists()
			? '<span title="' . esc_attr( $slug ) . '">' . esc_html( $theme->get( 'Name' ) ) . '</span>'
			: '<span style="color:#999">' . esc_html( $slug ) . '</span>';
	}

	/** @param array<string,string> $columns */
	public function sortable_column( array $columns ): array {
		$columns['page_theme'] = 'page_theme';
		return $columns;
	}

	public function handle_sort( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'page_theme' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', self::META_KEY );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
