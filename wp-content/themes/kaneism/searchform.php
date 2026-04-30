<?php
/*
* This is the basic search form that will get shown when you use get_search_form() anywhere in your theme.
* Updated with new HTMl5 goodness.
*
*/
/*
 * Generate a unique ID for each form and a string containing an aria-label
 * if one was passed to get_search_form() in the args array.
 */
$kaneism_unique_id = wp_unique_id( 'search-form-' );
$kaneism_aria_label = ! empty( $args['aria_label'] ) ? 'aria-label="' . esc_attr( $args['aria_label'] ) . '"' : '';

?>
<form id="searchform" role="search" <?php echo $kaneism_aria_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?> method="get" class="search-form cf" action="<?php echo esc_url( home_url( '/' ) ); ?>">
<fieldset class="fieldset">
	<legend class="hide-text">What are you looking for today?</legend>
	<label class="hide-text" for="<?php echo esc_attr( $kaneism_unique_id ); ?>"><?php _e( 'Search&hellip;', 'kaneism' ); // phpcs:ignore: WordPress.Security.EscapeOutput.UnsafePrintingFunction -- core trusts translations ?></label>
	<input type="search" id="<?php echo esc_attr( $kaneism_unique_id ); ?>" class="text-field search-field" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" size="25" placeholder="Place Your Bets...">
	<button type="submit" value="Search" class="search-submit ico i-m i--search">Search</button>
</fieldset>
</form>