<?php
/**
 * Footer template for Healthedia - Dark, Multi-Column Editorial Edition.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright_text = get_option( 'healthedia_copyright_text', '© 2026 Healthedia. All Rights Reserved. Permanent Open-Access Repository.' );
?>
<footer class="healthedia-footer-new">
	<div class="healthedia-footer-main">
		<div class="healthedia-footer-grid">

			<!-- Left Section: Branding & Identity -->
			<div class="healthedia-footer-brand-section">
				<div class="healthedia-footer-logo">
					<div class="healthedia-editorial-emblem emblem-light">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
							<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
							<path d="M12 8v8M9 12h6"/>
						</svg>
					</div>
					<span class="healthedia-footer-brand-title">Healthedia Journal</span>
				</div>
				<p class="healthedia-footer-mission">
					Empowering global health research through peer-reviewed excellence, advanced data indexing, and open-access knowledge exchange.
				</p>
				<div class="healthedia-footer-meta-info">
					<div class="healthedia-meta-item">
						<span class="meta-label">Frequency:</span>
						<span class="meta-value">Published Monthly</span>
					</div>
					<div class="healthedia-meta-item">
						<span class="meta-label">Indexing:</span>
						<span class="meta-value">PubMed, Scopus, & PMC</span>
					</div>
					<div class="healthedia-meta-item">
						<span class="meta-label">DOI Metadata:</span>
						<span class="meta-value text-blue">10.1016/healthedia</span>
					</div>
				</div>
			</div>

			<!-- Column 1: Manuscript Submissions -->
			<div class="healthedia-footer-column">
				<h3 class="healthedia-footer-heading-new">Manuscript Submissions</h3>
				<ul class="healthedia-footer-links-new">
					<li><a href="<?php echo esc_url( home_url( '/submit-paper/' ) ); ?>">Submit Paper</a></li>
					<li><a href="<?php echo esc_url( home_url( '/author-guidelines/' ) ); ?>">Author Guidelines</a></li>
					<li><a href="<?php echo esc_url( home_url( '/processing-charges/' ) ); ?>">Article Processing Charges</a></li>
					<li><a href="<?php echo esc_url( home_url( '/call-for-papers/' ) ); ?>">Call for Papers</a></li>
				</ul>
			</div>

			<!-- Column 2: Peer Review Guidelines -->
			<div class="healthedia-footer-column">
				<h3 class="healthedia-footer-heading-new">Peer Review</h3>
				<ul class="healthedia-footer-links-new">
					<li><a href="<?php echo esc_url( home_url( '/peer-review-process/' ) ); ?>">Peer Review Process</a></li>
					<li><a href="<?php echo esc_url( home_url( '/reviewer-resources/' ) ); ?>">Reviewer Resources</a></li>
					<li><a href="<?php echo esc_url( home_url( '/journal-scope/' ) ); ?>">Journal Scope</a></li>
					<li><a href="<?php echo esc_url( home_url( '/editorial-board-process/' ) ); ?>">Editorial Board</a></li>
				</ul>
			</div>

			<!-- Column 3: Editorial Board Directory -->
			<div class="healthedia-footer-column">
				<h3 class="healthedia-footer-heading-new">Editorial Board</h3>
				<ul class="healthedia-footer-links-new">
					<li><a href="<?php echo esc_url( home_url( '/editor-in-chief/' ) ); ?>">Editor-in-Chief</a></li>
					<li><a href="<?php echo esc_url( home_url( '/associate-editors/' ) ); ?>">Associate Editors</a></li>
					<li><a href="<?php echo esc_url( home_url( '/advisory-board/' ) ); ?>">Advisory Board</a></li>
					<li><a href="<?php echo esc_url( home_url( '/regional-editors/' ) ); ?>">Regional Editors</a></li>
				</ul>
			</div>

			<!-- Column 4: Ethical Policies -->
			<div class="healthedia-footer-column">
				<h3 class="healthedia-footer-heading-new">Ethical Policies</h3>
				<ul class="healthedia-footer-links-new">
					<li><a href="<?php echo esc_url( home_url( '/publishing-ethics/' ) ); ?>">Publishing Ethics</a></li>
					<li><a href="<?php echo esc_url( home_url( '/conflict-of-interest/' ) ); ?>">Conflict of Interest</a></li>
					<li><a href="<?php echo esc_url( home_url( '/plagiarism-policy/' ) ); ?>">Plagiarism Policy</a></li>
					<li><a href="<?php echo esc_url( home_url( '/open-access-policy/' ) ); ?>">Open Access Policy</a></li>
				</ul>
			</div>

			<!-- Column 5: Archive Taxonomies -->
			<div class="healthedia-footer-column">
				<h3 class="healthedia-footer-heading-new">Archive Taxonomies</h3>
				<ul class="healthedia-footer-links-new">
					<li><a href="<?php echo esc_url( home_url( '/taxonomy/physiology/' ) ); ?>">Physiology</a></li>
					<li><a href="<?php echo esc_url( home_url( '/taxonomy/biomechanics/' ) ); ?>">Biomechanics</a></li>
					<li><a href="<?php echo esc_url( home_url( '/taxonomy/kinesiology/' ) ); ?>">Kinesiology</a></li>
					<li><a href="<?php echo esc_url( home_url( '/taxonomy/rehabilitation/' ) ); ?>">Rehabilitation</a></li>
				</ul>
			</div>

		</div>
	</div>

	<!-- Bottom Utility Strip -->
	<div class="healthedia-footer-bottom">
		<div class="healthedia-footer-bottom-container">

			<!-- Left Side: Copyright and System Status -->
			<div class="healthedia-footer-bottom-left">
				<span class="healthedia-copyright-text-new"><?php echo esc_html( $copyright_text ); ?></span>
				<span class="healthedia-footer-dot-divider">•</span>
				<span class="healthedia-uptime-indicator">
					<span class="uptime-green-dot"></span>
					System Status: 99.98% Uptime
				</span>
			</div>

			<!-- Center Side: Academic Network Icons -->
			<div class="healthedia-academic-networks">
				<span class="network-badge-tag" title="Crossref Indexed">Crossref</span>
				<span class="network-badge-tag" title="ORCID Integration">ORCID</span>
				<span class="network-badge-tag" title="DOAJ Registry">DOAJ</span>
				<span class="network-badge-tag" title="PubMed Central">PMC</span>
			</div>

			<!-- Right Side: RSS and Legal compliance links -->
			<div class="healthedia-footer-bottom-right">
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" class="healthedia-bottom-link-new">Privacy Policy</a>
				<span class="healthedia-footer-dot-divider">•</span>
				<a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>" class="healthedia-bottom-link-new">Terms & Conditions</a>
				<span class="healthedia-footer-dot-divider">•</span>
				<a href="<?php echo esc_url( home_url( '/feed/' ) ); ?>" class="healthedia-rss-link" title="RSS Feed">
					<svg class="healthedia-rss-icon" viewBox="0 0 24 24" fill="currentColor">
						<path d="M5 3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5zm1.5 12c.83 0 1.5.67 1.5 1.5S7.33 18 6.5 18 5 17.33 5 16.5 5.67 15 6.5 15zm0-4.5c2.48 0 4.5 2.02 4.5 4.5H9c0-1.38-1.12-2.5-2.5-2.5v-2zm0-4c4.69 0 8.5 3.81 8.5 8.5H13c0-3.59-2.91-6.5-6.5-6.5V6.5z"/>
					</svg>
					RSS
				</a>
			</div>

		</div>
	</div>
</footer>
