<?php
/**
 * Footer template for Healthedia.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get customization if any
$copyright_text = get_option( 'healthedia_copyright_text', '© 2026 Healthedia. All Rights Reserved. Permanent Open-Access Repository.' );
?>
<footer class="healthedia-footer">
	<div class="healthedia-footer-container">
		<!-- Left Side: Copyright -->
		<div class="healthedia-footer-copyright">
			<?php echo esc_html( $copyright_text ); ?>
		</div>

		<!-- Right Side: Links -->
		<div class="healthedia-footer-links">
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" class="healthedia-footer-link">Privacy Policy</a>
			<span class="healthedia-footer-separator">•</span>
			<a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>" class="healthedia-footer-link">Terms & Conditions</a>
			<span class="healthedia-footer-separator">•</span>
			<a href="<?php echo esc_url( home_url( '/publication-policies/' ) ); ?>" class="healthedia-footer-link">Publication Policies</a>
			<span class="healthedia-footer-separator">•</span>
			<a href="<?php echo esc_url( home_url( '/certificate-verification/' ) ); ?>" class="healthedia-footer-link">Certificate Verification</a>
			<span class="healthedia-footer-separator">•</span>
			<a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class="healthedia-footer-link">Support</a>
		</div>
	</div>
</footer>
