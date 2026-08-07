<?php
/**
 * Template Name: Archive Search Homepage
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        .healthedia-search-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 20px;
            max-width: 900px;
            margin: 0 auto;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
            min-height: calc(100vh - 280px);
        }

        .healthedia-search-title {
            font-size: 64px;
            font-weight: 800;
            color: #000000;
            margin: 0;
            letter-spacing: -1.5px;
            line-height: 1.05;
        }

        .healthedia-search-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #999999;
            letter-spacing: 2px;
            margin-top: 15px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .healthedia-search-divider {
            width: 50px;
            height: 3px;
            background-color: #000000;
            margin-bottom: 25px;
        }

        .healthedia-search-description {
            font-size: 16px;
            color: #888888;
            max-width: 620px;
            line-height: 1.6;
            margin-bottom: 40px;
            font-weight: 500;
        }

        .healthedia-search-bar-container {
            width: 100%;
            max-width: 780px;
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 40px;
            padding: 6px 6px 6px 24px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
            box-sizing: border-box;
            margin-bottom: 30px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .healthedia-search-bar-container:focus-within {
            border-color: #000000;
            box-shadow: 0 6px 35px rgba(0, 0, 0, 0.06);
        }

        .healthedia-search-icon-left {
            color: #999999;
            margin-right: 12px;
            display: flex;
            align-items: center;
        }

        .healthedia-search-input {
            border: none;
            outline: none;
            flex-grow: 1;
            font-size: 16px;
            font-family: inherit;
            color: #333333;
            background: transparent;
            width: 100%;
        }

        .healthedia-search-input::placeholder {
            color: #999999;
        }

        .healthedia-search-actions-right {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-right: 10px;
        }

        .healthedia-shortcut-badge {
            background-color: #f3f3f3;
            color: #777777;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .healthedia-icon-btn {
            background: none;
            border: none;
            color: #888888;
            cursor: pointer;
            padding: 6px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .healthedia-icon-btn:hover {
            background-color: #f3f3f3;
            color: #000000;
        }

        .healthedia-search-submit {
            background: #000000;
            color: #ffffff;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 30px;
            letter-spacing: 1px;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
            text-transform: uppercase;
        }

        .healthedia-search-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .healthedia-suggested-tags {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            max-width: 700px;
        }

        .healthedia-tag {
            text-decoration: none;
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            color: #666666;
            font-size: 11px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .healthedia-tag:hover {
            border-color: #000000;
            color: #000000;
            background-color: #fafafa;
        }

        /* Responsive scaling */
        @media (max-width: 768px) {
            .healthedia-search-title {
                font-size: 40px;
            }
            .healthedia-search-bar-container {
                padding: 4px 4px 4px 16px;
                border-radius: 30px;
            }
            .healthedia-search-input {
                font-size: 14px;
            }
            .healthedia-search-submit {
                padding: 10px 20px;
                font-size: 12px;
            }
            .healthedia-shortcut-badge {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="healthedia-search-hero">
        <h1 class="healthedia-search-title">HEALTHEDIA</h1>
        <div class="healthedia-search-subtitle">GLOBAL HEALTH & PERFORMANCE ARCHIVE</div>
        <div class="healthedia-search-divider"></div>
        <p class="healthedia-search-description">
            The leading open-access indexing database for medical, rehabilitation, sports physiology, biomechanics, and human performance studies.
        </p>

        <!-- Search Bar -->
        <div class="healthedia-search-bar-container">
            <span class="healthedia-search-icon-left">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" class="healthedia-search-input" id="healthedia-search-field" placeholder="Search by title, author, keyword, journal, specialty or DOI...">
            <div class="healthedia-search-actions-right">
                <span class="healthedia-shortcut-badge">/</span>
                <button class="healthedia-icon-btn" title="Voice Search" id="healthedia-voice-search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                </button>
                <button class="healthedia-icon-btn" title="AI Search Optimization" id="healthedia-ai-search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- AI Sparkle Icon Path -->
                        <path d="M12 3v4M12 17v4M3 12h4M17 12h4M6.34 6.34l2.83 2.83M14.83 14.83l2.83 2.83M6.34 17.66l2.83-2.83M14.83 9.17l2.83-2.83"/>
                    </svg>
                </button>
                <button class="healthedia-search-submit" id="healthedia-search-button">Search</button>
            </div>
        </div>

        <!-- Suggested tags -->
        <div class="healthedia-suggested-tags">
            <a href="#" class="healthedia-tag">HIIT VS CONTINUOUS AEROBIC</a>
            <a href="#" class="healthedia-tag">ACHILLES TENDINOPATHY</a>
            <a href="#" class="healthedia-tag">MYOKINES IN MUSCLE AGING</a>
            <a href="#" class="healthedia-tag">SLEEP OPTIMIZATION</a>
            <a href="#" class="healthedia-tag">SARCOPENIA</a>
            <a href="#" class="healthedia-tag">GAIT BIOMECHANICS</a>
            <a href="#" class="healthedia-tag">KETONE MONOESTER SUPPLEMENTATION</a>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchField = document.getElementById('healthedia-search-field');
            const searchButton = document.getElementById('healthedia-search-button');

            // Keyboard shortcut '/' to focus search input
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && document.activeElement !== searchField) {
                    e.preventDefault();
                    searchField.focus();
                }
            });

            // Perform Search Action
            function performSearch() {
                const query = searchField.value.trim();
                if (query) {
                    alert('Searching for: ' + query);
                } else {
                    alert('Please enter a search query.');
                }
            }

            searchButton.addEventListener('click', performSearch);
            searchField.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Handle Tags
            document.querySelectorAll('.healthedia-tag').forEach(tag => {
                tag.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchField.value = this.textContent.trim();
                    searchField.focus();
                });
            });

            // Mic action
            document.getElementById('healthedia-voice-search').addEventListener('click', function() {
                alert('Voice search activated.');
            });

            // AI action
            document.getElementById('healthedia-ai-search').addEventListener('click', function() {
                alert('AI Optimization activated.');
            });
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
