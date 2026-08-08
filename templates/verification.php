<?php
/**
 * Template Name: Verification Portal
 */

$serial_query = isset( $_GET['serial'] ) ? sanitize_text_field( trim( $_GET['serial'] ) ) : '';
$record = null;
$searched = ! empty( $serial_query );

// Initialize Default Certificate options if empty
$certificates = get_option( 'healthedia_certificates' );
if ( ! is_array( $certificates ) ) {
    $certificates = [
        'HE-8492-X' => [
            'recipient' => 'Dr. Mabrouk',
            'doc_type' => 'Clinical Hydration Certificate',
            'date_issued' => '2026-03-12',
            'status' => 'Active',
            'notes' => 'Authorized by Sports Physiology & Hydration division.'
        ],
        'HE-2041-Y' => [
            'recipient' => 'Professor Alan Turing',
            'doc_type' => 'Elite Athlete Biomechanical Performance Record',
            'date_issued' => '1952-06-23',
            'status' => 'Archived',
            'notes' => 'Historic scientific credential reference.'
        ]
    ];
    update_option( 'healthedia_certificates', $certificates );
}

if ( $searched ) {
    if ( is_array( $certificates ) && isset( $certificates[$serial_query] ) ) {
        $record = $certificates[$serial_query];
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Portal | Healthedia</title>
    <?php wp_head(); ?>
    <style>
        .healthedia-verification-wrapper {
            max-width: 800px;
            margin: 60px auto;
            padding: 0 20px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .healthedia-verification-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .healthedia-verification-title {
            font-size: 32px;
            font-weight: 800;
            color: #000000;
            letter-spacing: -1px;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }

        .healthedia-verification-subtitle {
            font-size: 14px;
            color: #666666;
            margin: 0;
            font-weight: 500;
        }

        /* Search Bar Panel */
        .healthedia-verification-panel {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            margin-bottom: 30px;
        }

        .healthedia-verification-form {
            display: flex;
            gap: 12px;
        }

        .healthedia-verification-input {
            flex: 1;
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 16px;
            color: #111111;
            outline: none;
            height: 52px;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }

        .healthedia-verification-input:focus {
            border-color: #000000;
        }

        .healthedia-verification-submit {
            background: #000000;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 0 28px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            height: 52px;
            box-sizing: border-box;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .healthedia-verification-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Results Card */
        .healthedia-verification-result {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
            animation: resultFadeIn 0.3s ease;
        }

        @keyframes resultFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .healthedia-result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .healthedia-result-status-badge {
            background: #e6f6ec;
            color: #1b8a4f;
            border: 1px solid #1b8a4f;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .healthedia-result-serial-title {
            font-size: 14px;
            color: #888888;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .healthedia-result-serial-value {
            font-size: 18px;
            font-weight: 800;
            color: #000000;
            margin: 4px 0 0 0;
        }

        .healthedia-result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .healthedia-result-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .healthedia-result-label {
            font-size: 11px;
            font-weight: 700;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .healthedia-result-value {
            font-size: 15px;
            font-weight: 600;
            color: #111111;
        }

        /* Not Found / Error View */
        .healthedia-result-not-found {
            text-align: center;
            padding: 40px 20px;
        }

        .healthedia-not-found-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .healthedia-not-found-title {
            font-size: 18px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
            margin: 0 0 8px 0;
        }

        .healthedia-not-found-text {
            font-size: 14px;
            color: #666666;
            line-height: 1.5;
            margin: 0;
        }

        @media (max-width: 600px) {
            .healthedia-verification-form {
                flex-direction: column;
            }
            .healthedia-verification-submit {
                width: 100%;
            }
            .healthedia-result-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="healthedia-verification-wrapper">
        <div class="healthedia-verification-header">
            <h1 class="healthedia-verification-title">Verification Portal</h1>
            <p class="healthedia-verification-subtitle">Verify institutional certificates, professional credentials, and official records.</p>
        </div>

        <!-- Search Input Card -->
        <div class="healthedia-verification-panel">
            <form class="healthedia-verification-form" method="GET" action="">
                <input type="text" name="serial" class="healthedia-verification-input" placeholder="Enter Unique Certificate Serial Number" value="<?php echo esc_attr( $serial_query ); ?>" required id="verification-serial-input">
                <button type="submit" class="healthedia-verification-submit" id="verification-search-btn">VERIFY DOCUMENT</button>
            </form>
        </div>

        <!-- Verification Results View -->
        <?php if ( $searched ) : ?>
            <div class="healthedia-verification-result" id="verification-result-container">
                <?php if ( $record ) : ?>
                    <div class="healthedia-result-header">
                        <div>
                            <p class="healthedia-result-serial-title">Verification Reference</p>
                            <h3 class="healthedia-result-serial-value"><?php echo esc_html( $serial_query ); ?></h3>
                        </div>
                        <div class="healthedia-result-status-badge">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Verified ✅
                        </div>
                    </div>

                    <div class="healthedia-result-grid">
                        <div class="healthedia-result-row">
                            <span class="healthedia-result-label">Recipient Name</span>
                            <span class="healthedia-result-value" id="res-recipient"><?php echo esc_html( $record['recipient'] ); ?></span>
                        </div>
                        <div class="healthedia-result-row">
                            <span class="healthedia-result-label">Document / Certificate Type</span>
                            <span class="healthedia-result-value" id="res-doc-type"><?php echo esc_html( $record['doc_type'] ); ?></span>
                        </div>
                        <div class="healthedia-result-row">
                            <span class="healthedia-result-label">Date Issued</span>
                            <span class="healthedia-result-value" id="res-date-issued"><?php echo esc_html( $record['date_issued'] ); ?></span>
                        </div>
                        <div class="healthedia-result-row">
                            <span class="healthedia-result-label">Status</span>
                            <span class="healthedia-result-value" id="res-status"><?php echo esc_html( $record['status'] ); ?></span>
                        </div>
                        <?php if ( ! empty( $record['notes'] ) ) : ?>
                            <div class="healthedia-result-row" style="grid-column: span 2;">
                                <span class="healthedia-result-label">Additional Notes</span>
                                <span class="healthedia-result-value" id="res-notes" style="font-weight: 500; color: #555555;"><?php echo esc_html( $record['notes'] ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="healthedia-result-not-found" id="verification-not-found-view">
                        <div class="healthedia-not-found-icon">⚠️</div>
                        <h3 class="healthedia-not-found-title">Record Not Found</h3>
                        <p class="healthedia-not-found-text">No matching record was found for Serial Number: <strong><?php echo esc_html( $serial_query ); ?></strong>. Please double-check the spelling or contact the administrator.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
