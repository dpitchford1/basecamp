<?php
/**
 * Search form template.
 * Output via get_search_form() — WordPress calls this file automatically.
 *
 * @package basecamp
 */
?>

<form method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<label class="search-form__label">
		<span class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'basecamp' ); ?></span>
		<input
			type="search"
			class="search-form__input"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php echo esc_attr_x( 'Search&hellip;', 'placeholder', 'basecamp' ); ?>"
			autocomplete="off"
		/>
	</label>
	<button type="submit" class="search-form__submit">
		<span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'basecamp' ); ?></span>
		<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M10 2a8 8 0 1 1 0 16A8 8 0 0 1 10 2zm0 2a6 6 0 1 0 0 12A6 6 0 0 0 10 4zm8.293 11.293 1.414 1.414-4.243 4.243-1.414-1.414 4.243-4.243z"/></svg>
	</button>
</form>