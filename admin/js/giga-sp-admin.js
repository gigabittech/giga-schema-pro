/**
 * Chart.js Color Adaptation
 * Gets current theme colors dynamically for proper dark/light mode support
 */
function getChartColors() {
    const computedStyle = getComputedStyle(document.documentElement);
    return {
        textColor: computedStyle.getPropertyValue('--color-text-primary').trim(),
        gridColor: computedStyle.getPropertyValue('--color-border-tertiary').trim(),
        backgroundColor: computedStyle.getPropertyValue('--color-background-primary').trim(),
        primaryColor: computedStyle.getPropertyValue('--color-brand-primary').trim(),
        successColor: computedStyle.getPropertyValue('--color-success').trim(),
        warningColor: computedStyle.getPropertyValue('--color-warning').trim(),
        dangerColor: computedStyle.getPropertyValue('--color-danger').trim()
    };
}

/**
 * Initialize Chart.js with theme-aware colors
 * Usage: const chart = new Chart(ctx, getChartConfig());
 */
function getChartConfig(type, data, options = {}) {
    const colors = getChartColors();
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: colors.textColor
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: colors.gridColor
                },
                ticks: {
                    color: colors.textColor
                }
            },
            y: {
                grid: {
                    color: colors.gridColor
                },
                ticks: {
                    color: colors.textColor
                }
            }
        }
    };
    
    return {
        type: type,
        data: data,
        options: { ...defaultOptions, ...options }
    };
}

(function($) {
    'use strict';
    
    // Initialize modern UI components
    $(document).ready(function() {
        
        // Initialize tab navigation
        initTabs();
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize interactive cards
        initInteractiveCards();
        
        // Initialize form validation
        initFormValidation();
        
        // Initialize animations
        initAnimations();
        
        // Initialize modal functionality
        initModals();
        
        // Initialize toggle switches
        initToggleSwitches();
        
        // Initialize progress bars
        initProgressBars();
        
        // Initialize notification system
        initNotifications();
        
        // Initialize validation functionality
        initValidation();
        
        // Initialize rule management
        initRuleManagement();
        
        // Initialize schema type selection
        initSchemaSelection();
        
        // Initialize WooCommerce-specific functionality
        initWooCommerceTabs();
        initWooCommerceValidation();
        
        // Initialize general settings tabs
        initGeneralSettingsTabs();
    });
    
    // Tab Navigation System
    function initTabs() {
        // Support both naming conventions: .giga-tabs (schema types page) and .giga-sp-tabs (dashboard)
        $(document).on('click', '.giga-sp-tab', function(e) {
            e.preventDefault();

            const $tab = $(this);
            // Find the closest tab container — works with both class names
            const $tabContainer = $tab.closest('.giga-sp-tabs, .giga-tabs');
            const targetId = $tab.attr('href');
            const $targetPanel = targetId ? $(targetId) : null;

            // Only switch panels if the target panel actually exists on this page
            if ( !$targetPanel || $targetPanel.length === 0 ) {
                return; // navigational tab — let the onclick handle it
            }

            // Remove active class from all tabs in this container
            $tabContainer.find('.giga-sp-tab').removeClass('active');

            // Add active class to clicked tab
            $tab.addClass('active');

            // Hide ALL panels (support both class names)
            $('.giga-sp-panel, .giga-panel').hide();

            // Show target panel with animation
            $targetPanel.stop(true, true).fadeIn(300).addClass('giga-animate-in');
        });
    }
    
    // Tooltip System
    function initTooltips() {
        $('.giga-tooltip').each(function() {
            const $tooltip = $(this);
            const $trigger = $tooltip.find('.giga-tooltip-trigger');
            const $content = $tooltip.find('.giga-tooltip-content');
            
            $trigger.on('mouseenter', function() {
                $content.fadeIn(200);
            });
            
            $trigger.on('mouseleave', function() {
                $content.fadeOut(200);
            });
        });
    }
    
    // Interactive Cards
    function initInteractiveCards() {
        $('.giga-card, .giga-schema-card, .giga-stat-card').each(function() {
            const $card = $(this);
            
            $card.on('click', function() {
                $card.addClass('giga-card-active');
                
                // Remove active class from siblings
                $card.siblings().removeClass('giga-card-active');
            });
            
            $card.on('mouseenter', function() {
                $(this).addClass('giga-card-hover');
            });
            
            $card.on('mouseleave', function() {
                $(this).removeClass('giga-card-hover');
            });
        });
    }
    
    // Form Validation
    function initFormValidation() {
        $('.giga-form').each(function() {
            const $form = $(this);
            const $submitBtn = $form.find('.giga-btn-primary');
            
            $form.on('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                const $requiredFields = $form.find('[required]');
                
                $requiredFields.each(function() {
                    const $field = $(this);
                    const $value = $field.val().trim();
                    
                    if (!$value) {
                        isValid = false;
                        $field.addClass('giga-error');
                        showFieldError($field, 'This field is required');
                    } else {
                        $field.removeClass('giga-error');
                        hideFieldError($field);
                    }
                });
                
                if (isValid) {
                    $submitBtn.prop('disabled', true).text('Saving...');
                    
                    // Simulate AJAX call
                    setTimeout(function() {
                        $submitBtn.prop('disabled', false).text('Save Changes');
                        showNotification('Settings saved successfully!', 'success');
                    }, 1000);
                }
            });
        });
    }
    
    // Field Error Handling
    function showFieldError($field, message) {
        const $errorContainer = $field.closest('.giga-form-group').find('.giga-error-message');
        
        if ($errorContainer.length) {
            $errorContainer.text(message).show();
        } else {
            $field.after(`<div class="giga-error-message">${message}</div>`);
        }
    }
    
    function hideFieldError($field) {
        const $errorContainer = $field.closest('.giga-form-group').find('.giga-error-message');
        $errorContainer.hide();
    }
    
    // Animations
    function initAnimations() {
        // Animate elements on scroll
        $(window).on('scroll', function() {
            const $animatedElements = $('.giga-animate-on-scroll');
            
            $animatedElements.each(function() {
                const $element = $(this);
                const elementTop = $element.offset().top;
                const elementBottom = elementTop + $element.outerHeight();
                const viewportTop = $(window).scrollTop();
                const viewportBottom = viewportTop + $(window).height();
                
                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    $element.addClass('giga-animate-in');
                }
            });
        });
        
        // Initial animation for visible elements
        $('.giga-animate-on-scroll:visible').addClass('giga-animate-in');
    }
    
    // Modal System
    function initModals() {
        $('.giga-modal-trigger').on('click', function(e) {
            e.preventDefault();
            const $modal = $($(this).attr('href'));
            openModal($modal);
        });
        
        $('.giga-modal-close').on('click', function() {
            const $modal = $(this).closest('.giga-modal');
            closeModal($modal);
        });
        
        $(document).on('click', '.giga-modal-overlay', function() {
            const $modal = $(this).closest('.giga-modal');
            closeModal($modal);
        });
    }
    
    function openModal($modal) {
        $modal.fadeIn(300);
        $('body').addClass('giga-modal-open');
    }
    
    function closeModal($modal) {
        $modal.fadeOut(300);
        $('body').removeClass('giga-modal-open');
    }
    
    // Toggle Switches
    function initToggleSwitches() {
        $('.giga-toggle-switch').each(function() {
            const $toggle = $(this);
            const $input = $toggle.find('input[type="checkbox"]');
            const $label = $toggle.find('.giga-toggle-label');
            
            $toggle.on('click', function() {
                $input.prop('checked', !$input.prop('checked'));
                updateToggleStyle($toggle, $input.prop('checked'));
            });
            
            // Initialize style
            updateToggleStyle($toggle, $input.prop('checked'));
        });
    }
    
    function updateToggleStyle($toggle, isChecked) {
        if (isChecked) {
            $toggle.addClass('giga-toggle-active');
            $toggle.find('.giga-toggle-slider').addClass('giga-toggle-slider-active');
        } else {
            $toggle.removeClass('giga-toggle-active');
            $toggle.find('.giga-toggle-slider').removeClass('giga-toggle-slider-active');
        }
    }
    
    // Progress Bars
    function initProgressBars() {
        $('.giga-progress-bar').each(function() {
            const $progress = $(this);
            const $progressFill = $progress.find('.giga-progress-fill');
            const $progressText = $progress.find('.giga-progress-text');
            const progress = parseInt($progress.data('progress'));
            
            // Animate progress bar
            setTimeout(function() {
                $progressFill.css('width', progress + '%');
                $progressText.text(progress + '%');
            }, 500);
        });
    }
    
    // Notification System
    function initNotifications() {
        // Auto-hide notifications after 5 seconds
        $(document).on('click', '.giga-notification-close', function() {
            const $notification = $(this).closest('.giga-notification');
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Auto-hide all notifications
        setTimeout(function() {
            $('.giga-notification').each(function() {
                const $notification = $(this);
                if (!$notification.hasClass('giga-notification-persistent')) {
                    $notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
        }, 5000);
    }
    
    function showNotification(message, type = 'info', duration = 5000) {
        const $notification = $(`
            <div class="giga-notification giga-notification-${type}">
                <div class="giga-notification-content">
                    <span class="giga-notification-icon">${getNotificationIcon(type)}</span>
                    <span class="giga-notification-message">${message}</span>
                </div>
                <button class="giga-notification-close">&times;</button>
            </div>
        `);
        
        $('body').append($notification);
        $notification.fadeIn(300);
        
        if (duration > 0) {
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, duration);
        }
    }
    
    function getNotificationIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }
    
    // Validation System
    function initValidation() {
        $('#giga-sp-validate-content').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            // Show loading state
            $btn.prop('disabled', true).html('<span class="spinner"></span> Validating...');
            
            // Get validation type
            const validationType = $btn.data('validation-type') || 'schema';
            
            // Simulate AJAX call
            simulateValidation(validationType, $btn);
        });
        
        // Bulk validation
        $('#giga-sp-validate-all').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner"></span> Validating All Pages...');
            
            simulateValidation('bulk', $btn);
        });
    }
    
    function simulateValidation(type, $btn) {
        setTimeout(function() {
            const results = generateMockValidationResults(type);
            displayValidationResults(results);
            
            $btn.prop('disabled', false).text('Validate Schema');
        }, 1500);
    }
    
    function generateMockValidationResults(type) {
        const results = {
            total: Math.floor(Math.random() * 50) + 10,
            passed: Math.floor(Math.random() * 40) + 5,
            failed: Math.floor(Math.random() * 5) + 1,
            warnings: Math.floor(Math.random() * 10) + 2,
            errors: []
        };
        
        results.failed += Math.floor(Math.random() * 3);
        results.warnings += Math.floor(Math.random() * 5);
        
        // Generate some sample errors
        if (results.failed > 0) {
            results.errors = [
                { type: 'missing_field', field: 'image', pages: ['Homepage', 'About Page'] },
                { type: 'invalid_format', field: 'datePublished', pages: ['Blog Post 1'] }
            ];
        }
        
        return results;
    }
    
    function displayValidationResults(results) {
        const $resultsContainer = $('#giga-validation-results');
        
        if ($resultsContainer.length) {
            $resultsContainer.html(generateValidationHTML(results));
            $resultsContainer.fadeIn(300);
        } else {
            const $newResults = $(`<div id="giga-validation-results" class="giga-validation-results">${generateValidationHTML(results)}</div>`);
            $('.giga-validation-section').after($newResults);
            $newResults.fadeIn(300);
        }
        
        // Show notification
        showNotification(`Validation complete! ${results.passed}/${results.total} pages passed.`, 'success');
    }
    
    function generateValidationHTML(results) {
        return `
            <div class="giga-stats-grid">
                <div class="giga-stat-card">
                    <div class="giga-stat-header">
                        <div class="giga-stat-icon">✓</div>
                        <div class="giga-stat-content">
                            <h3>${results.passed}</h3>
                            <p>Passed</p>
                        </div>
                    </div>
                </div>
                <div class="giga-stat-card">
                    <div class="giga-stat-header">
                        <div class="giga-stat-icon" style="background: var(--color-danger); color: var(--color-background-primary);">✕</div>
                        <div class="giga-stat-content">
                            <h3>${results.failed}</h3>
                            <p>Failed</p>
                        </div>
                    </div>
                </div>
                <div class="giga-stat-card">
                    <div class="giga-stat-header">
                        <div class="giga-stat-icon" style="background: var(--color-warning); color: var(--color-background-primary);">⚠</div>
                        <div class="giga-stat-content">
                            <h3>${results.warnings}</h3>
                            <p>Warnings</p>
                        </div>
                    </div>
                </div>
            </div>
            ${results.errors.length > 0 ? `
                <div class="giga-card">
                    <div class="giga-card-header">
                        <h3 class="giga-card-title">Errors Found</h3>
                    </div>
                    <div class="giga-card-body">
                        ${results.errors.map(error => `
                            <div class="giga-error-item">
                                <strong>${error.type.replace(/_/g, ' ').toUpperCase()}:</strong> ${error.field}
                                <br><small>Affects: ${error.pages.join(', ')}</small>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        `;
    }
    
    // Rule Management
    function initRuleManagement() {
        $('.giga-rule-toggle').on('click', function() {
            const $rule = $(this).closest('.giga-rule-card');
            const $ruleContent = $rule.find('.giga-rule-content');
            
            $ruleContent.slideToggle(300);
            $(this).toggleClass('active');
        });
        
        $('.giga-rule-edit').on('click', function(e) {
            e.preventDefault();
            const $rule = $(this).closest('.giga-rule-card');
            openRuleEditor($rule);
        });
        
        $('.giga-rule-delete').on('click', function(e) {
            e.preventDefault();
            const $rule = $(this).closest('.giga-rule-card');
            
            if (confirm('Are you sure you want to delete this rule?')) {
                $rule.fadeOut(300, function() {
                    $(this).remove();
                    showNotification('Rule deleted successfully', 'success');
                });
            }
        });
    }
    
    function openRuleEditor($rule) {
        const ruleData = {
            id: $rule.data('rule-id'),
            name: $rule.find('.giga-rule-title').text(),
            conditions: $rule.find('.giga-condition-tag').map(function() {
                return $(this).text();
            }).get()
        };
        
        // Open modal with rule editor
        const $modal = $(`
            <div class="giga-modal">
                <div class="giga-modal-overlay"></div>
                <div class="giga-modal-content">
                    <div class="giga-modal-header">
                        <h3>Edit Rule</h3>
                        <button class="giga-modal-close">&times;</button>
                    </div>
                    <div class="giga-modal-body">
                        <form class="giga-rule-form">
                            <div class="giga-form-group">
                                <label class="giga-form-label">Rule Name</label>
                                <input type="text" class="giga-form-input" value="${ruleData.name}">
                            </div>
                            <div class="giga-form-group">
                                <label class="giga-form-label">Conditions</label>
                                <div class="giga-condition-builder">
                                    <div class="giga-condition-item">
                                        <select class="giga-form-select">
                                            <option value="post_type">Post Type</option>
                                            <option value="category">Category</option>
                                            <option value="tag">Tag</option>
                                            <option value="custom_field">Custom Field</option>
                                        </select>
                                        <select class="giga-form-select">
                                            <option value="equals">Equals</option>
                                            <option value="contains">Contains</option>
                                            <option value="not_equals">Not Equals</option>
                                        </select>
                                        <input type="text" class="giga-form-input" placeholder="Value">
                                        <button type="button" class="giga-btn-icon remove-condition">−</button>
                                    </div>
                                </div>
                                <button type="button" class="giga-btn-secondary add-condition">+ Add Condition</button>
                            </div>
                            <div class="giga-form-group">
                                <label class="giga-form-label">Schema Type</label>
                                <select class="giga-form-select">
                                    <option value="article">Article</option>
                                    <option value="faq">FAQ</option>
                                    <option value="howto">HowTo</option>
                                    <option value="product">Product</option>
                                </select>
                            </div>
                            <div class="giga-modal-footer">
                                <button type="button" class="giga-btn-secondary giga-modal-close">Cancel</button>
                                <button type="submit" class="giga-btn-primary">Save Rule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append($modal);
        openModal($modal);
    }
    
    // Schema Type Selection
    function initSchemaSelection() {
        $('.giga-schema-card').on('click', function() {
            const $card = $(this);
            const schemaType = $card.data('schema-type');
            
            // Toggle selection
            $card.toggleClass('selected');
            
            // Update selection counter
            updateSchemaSelectionCounter();
        });
        
        $('.giga-select-all').on('click', function() {
            $('.giga-schema-card').addClass('selected');
            updateSchemaSelectionCounter();
        });
        
        $('.giga-select-none').on('click', function() {
            $('.giga-schema-card').removeClass('selected');
            updateSchemaSelectionCounter();
        });
    }
    
    function updateSchemaSelectionCounter() {
        const selectedCount = $('.giga-schema-card.selected').length;
        const $counter = $('.giga-selection-counter');
        
        if ($counter.length) {
            $counter.text(selectedCount);
        }
    }
    
    // WooCommerce Settings Specific Functions
    function initWooCommerceSettings() {
        // Tab switching for WooCommerce settings
        $('.giga-tabs .giga-sp-tab').on('click', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetId = $tab.attr('href');
            const $targetPanel = $(targetId);
            
            // Remove active class from all tabs
            $('.giga-tabs .giga-sp-tab').removeClass('active');
            
            // Add active class to clicked tab
            $tab.addClass('active');
            
            // Hide all panels
            $('.giga-panel').hide();
            
            // Show target panel with animation
            $targetPanel.fadeIn(300);
        });
        
        // Form validation for WooCommerce settings
        $('.giga-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('input[type="submit"]');
            
            // Validate required fields
            let isValid = true;
            const $requiredFields = $form.find('[required]');
            
            $requiredFields.each(function() {
                const $field = $(this);
                const $value = $field.val().trim();
                
                if (!$value) {
                    isValid = false;
                    $field.addClass('giga-error');
                    showFieldError($field, 'This field is required');
                } else {
                    $field.removeClass('giga-error');
                    hideFieldError($field);
                }
            });
            
            // Validate shipping rate
            const $shippingRate = $('#shipping_rate');
            if ($shippingRate.length && $shippingRate.val()) {
                const rate = parseFloat($shippingRate.val());
                if (isNaN(rate) || rate < 0) {
                    isValid = false;
                    $shippingRate.addClass('giga-error');
                    showFieldError($shippingRate, 'Please enter a valid shipping rate');
                }
            }
            
            // Validate return days
            const $returnDays = $('#return_days');
            if ($returnDays.length && $returnDays.val()) {
                const days = parseInt($returnDays.val());
                if (isNaN(days) || days < 0) {
                    isValid = false;
                    $returnDays.addClass('giga-error');
                    showFieldError($returnDays, 'Please enter a valid number of days');
                }
            }
            
            if (isValid) {
                // Show loading state
                $submitBtn.prop('disabled', true).val('Saving...');
                
                // Submit the form
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $form.serialize() + '&action=giga_sp_save_woo_settings',
                    success: function(response) {
                        if (response.success) {
                            showNotification('WooCommerce settings saved successfully!', 'success');
                            
                            // Update status indicators
                            updateWooCommerceStatus();
                            
                            // Reset button
                            $submitBtn.prop('disabled', false).val('Save Settings');
                        } else {
                            showNotification('Error saving settings: ' + response.data.message, 'error');
                            $submitBtn.prop('disabled', false).val('Save Settings');
                        }
                    },
                    error: function() {
                        showNotification('Error saving settings. Please try again.', 'error');
                        $submitBtn.prop('disabled', false).val('Save Settings');
                    }
                });
            }
        });
        
        // Currency input formatting
        $('#shipping_currency').on('input', function() {
            const $input = $(this);
            const value = $input.val().toUpperCase();
            
            // Auto-uppercase currency codes
            if (value.length <= 3) {
                $input.val(value);
            }
        });
        
        // Real-time schema preview updates
        $('#shipping_rate, #shipping_currency, #return_days').on('input', function() {
            updateSchemaPreview();
        });
        
        // Initialize schema preview
        updateSchemaPreview();
    }
    
    function updateWooCommerceStatus() {
        // Update WooCommerce status indicators
        const $defaultBrand = $('#default_brand');
        const $shippingRate = $('#shipping_rate');
        
        // Update brand status
        if ($defaultBrand.val().trim()) {
            $('.giga-status-item:nth-child(2) .giga-status-icon').addClass('active').removeClass('inactive');
        } else {
            $('.giga-status-item:nth-child(2) .giga-status-icon').removeClass('active').addClass('inactive');
        }
        
        // Update shipping status
        if ($shippingRate.val().trim()) {
            $('.giga-status-item:nth-child(3) .giga-status-icon').addClass('active').removeClass('inactive');
        } else {
            $('.giga-status-item:nth-child(3) .giga-status-icon').removeClass('active').addClass('inactive');
        }
    }
    
    function updateSchemaPreview() {
        const $shippingRate = $('#shipping_rate');
        const $shippingCurrency = $('#shipping_currency');
        const $returnDays = $('#return_days');
        
        const rate = $shippingRate.val() || '0.00';
        const currency = $shippingCurrency.val() || 'USD';
        const days = $returnDays.val() || '30';
        
        const preview = {
            "@type": "Offer",
            "priceCurrency": currency,
            "price": rate,
            "shippingDetails": {
                "@type": "OfferShippingDetails",
                "shippingRate": {
                    "@type": "MonetaryAmount",
                    "value": rate,
                    "currency": currency
                }
            },
            "hasMerchantReturnPolicy": {
                "@type": "MerchantReturnPolicy",
                "merchantReturnDays": parseInt(days),
                "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow"
            }
        };
        
        // Update preview in real-time
        $('.giga-schema-preview pre code').text(JSON.stringify(preview, null, 2));
    }
    
    // Maintenance actions
    window.gigaRegenerateAllSchema = function() {
        if (confirm('Are you sure you want to regenerate all schema for WooCommerce products? This may take a while for stores with many products.')) {
            showNotification('Regenerating schema for all products...', 'info');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'giga_regenerate_woo_schema',
                    nonce: gigaSpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Schema regenerated successfully for ' + response.data.count + ' products!', 'success');
                    } else {
                        showNotification('Error regenerating schema: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Error regenerating schema. Please try again.', 'error');
                }
            });
        }
    };
    
    window.gigaClearSchemaCache = function() {
        if (confirm('Are you sure you want to clear the schema cache?')) {
            showNotification('Clearing schema cache...', 'info');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'giga_clear_schema_cache',
                    nonce: gigaSpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Schema cache cleared successfully!', 'success');
                    } else {
                        showNotification('Error clearing cache: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Error clearing cache. Please try again.', 'error');
                }
            });
        }
    };
    
    // Initialize WooCommerce settings when the page loads
    $(document).ready(function() {
        if ($('.giga-woo-status').length) {
            initWooCommerceSettings();
        }
    });
    
    // WooCommerce Tab Navigation
    function initWooCommerceTabs() {
        $('.giga-woocommerce-tabs .giga-sp-tab').on('click', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetId = $tab.attr('href');
            const $targetPanel = $(targetId);
            
            // Remove active class from all tabs
            $('.giga-woocommerce-tabs .giga-sp-tab').removeClass('active');
            
            // Add active class to clicked tab
            $tab.addClass('active');
            
            // Hide all panels
            $('.giga-woocommerce-tab-content').hide();
            
            // Show target panel with animation
            $targetPanel.fadeIn(300);
        });
    }
    
    // WooCommerce Validation System
    function initWooCommerceValidation() {
        $('.giga-validate-btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const originalText = $btn.html();
            
            // Show loading state
            $btn.html('<span class="spinner"></span> Validating...').prop('disabled', true);
            
            // Show validation container if hidden
            $('.giga-validation-container').show();
            
            // Simulate validation process
            simulateWooCommerceValidation($btn);
        });
        
        // Validation filters
        $('.giga-filter-btn').on('click', function() {
            const $filter = $(this);
            const filterType = $filter.data('filter');
            
            // Toggle active state
            $filter.toggleClass('active');
            
            // Filter results
            filterValidationResults(filterType);
        });
        
        // Export validation report
        $('.giga-export-report').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.html('<span class="spinner"></span> Exporting...').prop('disabled', true);
            
            // Simulate export
            setTimeout(function() {
                exportValidationReport($btn, originalText);
            }, 1000);
        });
        
        // Regenerate schema
        $('.giga-regenerate-schema').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to regenerate all schema markup? This may take some time.')) {
                return;
            }
            
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.html('<span class="spinner"></span> Regenerating...').prop('disabled', true);
            
            simulateSchemaRegeneration($btn, originalText);
        });
        
        // Clear cache
        $('.giga-clear-cache').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear the schema cache?')) {
                return;
            }
            
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.html('<span class="spinner"></span> Clearing...').prop('disabled', true);
            
            clearSchemaCache($btn, originalText);
        });
    }
    
    // Simulate WooCommerce validation
    function simulateWooCommerceValidation($btn) {
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 20;
            if (progress > 100) progress = 100;
            
            $('.giga-progress-bar').width(progress + '%');
            $('.giga-progress-text').text('Validating... ' + Math.round(progress) + '%');
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                
                // Show validation results
                showWooCommerceValidationResults();
                
                // Restore button
                $btn.html('<span class="dashicons dashicons-check"></span> Validate Schema').prop('disabled', false);
            }
        }, 200);
    }
    
    // Show WooCommerce validation results
    function showWooCommerceValidationResults() {
        const resultsContainer = $('.giga-validation-results');
        
        // Mock validation results
        const mockResults = [
            {
                type: 'success',
                title: 'Product Schema Markup',
                message: 'Product schema markup is properly implemented for all products.',
                code: '{"@context":"https://schema.org","@type":"Product","name":"Sample Product","description":"A sample product description","offers":{"@type":"Offer","priceCurrency":"USD","price":29.99}}'
            },
            {
                type: 'warning',
                title: 'Missing Product Reviews',
                message: '3 products are missing review schema markup. Consider adding customer reviews.',
                code: '// Warning: Review schema not found for products 123, 456, 789'
            },
            {
                type: 'error',
                title: 'Invalid Product Images',
                message: '2 products have invalid image URLs. Please fix image references.',
                code: '// Error: Invalid image URL for product 456: https://invalid-url.com/image.jpg'
            }
        ];
        
        // Clear existing results
        resultsContainer.empty();
        
        // Add results
        mockResults.forEach(function(result) {
            const resultItem = $('<div class="giga-result-item ' + result.type + '"></div>');
            const resultHeader = $('<div class="giga-result-header"></div>');
            const resultTitle = $('<h4 class="giga-result-title"></h4>').text(result.title);
            const resultStatus = $('<div class="giga-result-status ' + result.type + '"></div>');
            
            if (result.type === 'success') {
                resultStatus.html('<span class="dashicons dashicons-yes"></span> Valid');
            } else if (result.type === 'warning') {
                resultStatus.html('<span class="dashicons dashicons-warning"></span> Warning');
            } else if (result.type === 'error') {
                resultStatus.html('<span class="dashicons dashicons-no"></span> Error');
            }
            
            resultHeader.append(resultTitle);
            resultHeader.append(resultStatus);
            
            const resultDetails = $('<div class="giga-result-details"></div>').text(result.message);
            
            if (result.code) {
                const resultCode = $('<div class="giga-result-code"></div>').text(result.code);
                resultItem.append(resultHeader);
                resultItem.append(resultDetails);
                resultItem.append(resultCode);
            } else {
                resultItem.append(resultHeader);
                resultItem.append(resultDetails);
            }
            
            resultsContainer.append(resultItem);
        });
        
        // Update statistics
        updateValidationStats(mockResults);
    }
    
    // Update validation statistics
    function updateValidationStats(results) {
        const stats = {
            success: 0,
            warning: 0,
            error: 0,
            total: results.length
        };
        
        results.forEach(function(result) {
            stats[result.type]++;
        });
        
        $('.giga-stat-value.success').text(stats.success);
        $('.giga-stat-value.warning').text(stats.warning);
        $('.giga-stat-value.error').text(stats.error);
        $('.giga-stat-value.info').text(stats.total);
    }
    
    // Filter validation results
    function filterValidationResults(filterType) {
        const resultsContainer = $('.giga-validation-results');
        const resultItems = resultsContainer.find('.giga-result-item');
        
        resultItems.each(function() {
            const $item = $(this);
            const itemType = $item.hasClass('success') ? 'success' :
                           $item.hasClass('warning') ? 'warning' : 'error';
            
            if (filterType === 'all' || filterType === itemType) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }
    
    // Export validation report
    function exportValidationReport($btn, originalText) {
        // Create and download file
        const reportData = {
            generated_at: new Date().toISOString(),
            site_url: window.location.href,
            validation_results: [
                {
                    type: 'success',
                    title: 'Product Schema Markup',
                    message: 'Product schema markup is properly implemented for all products.',
                    count: 15
                },
                {
                    type: 'warning',
                    title: 'Missing Product Reviews',
                    message: '3 products are missing review schema markup.',
                    count: 3
                },
                {
                    type: 'error',
                    title: 'Invalid Product Images',
                    message: '2 products have invalid image URLs.',
                    count: 2
                }
            ],
            summary: {
                total_products: 20,
                valid_schemas: 15,
                warnings: 3,
                errors: 2
            }
        };
        
        const blob = new Blob([JSON.stringify(reportData, null, 2)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'giga-schema-validation-report.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        $btn.html(originalText).prop('disabled', false);
        
        // Show success message
        showNotification('Validation report exported successfully!', 'success');
    }
    
    // Simulate schema regeneration
    function simulateSchemaRegeneration($btn, originalText) {
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 10;
            if (progress > 100) progress = 100;
            
            $('.giga-progress-bar').width(progress + '%');
            $('.giga-progress-text').text('Regenerating schema... ' + Math.round(progress) + '%');
            
            if (progress >= 100) {
                clearInterval(progressInterval);
                
                $btn.html(originalText).prop('disabled', false);
                showNotification('Schema regenerated successfully!', 'success');
            }
        }, 100);
    }
    
    // Clear schema cache
    function clearSchemaCache($btn, originalText) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'giga_sp_clear_schema_cache'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Schema cache cleared successfully!', 'success');
                } else {
                    showNotification('Error clearing cache: ' + response.data.message, 'error');
                }
                $btn.html(originalText).prop('disabled', false);
            },
            error: function() {
                showNotification('Error clearing cache. Please try again.', 'error');
                $btn.html(originalText).prop('disabled', false);
            }
        });
    }
    
    // General Settings Tab Navigation
    function initGeneralSettingsTabs() {
        $('.giga-tabs .giga-sp-tab').on('click', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetId = $tab.data('tab');
            const $targetPanel = $('#' + targetId);
            
            // Remove active class from all tabs
            $('.giga-tabs .giga-sp-tab').removeClass('active');
            
            // Add active class to clicked tab
            $tab.addClass('active');
            
            // Hide all panels
            $('.giga-settings-section').removeClass('active').hide();
            
            // Show target panel with animation
            $targetPanel.addClass('active').fadeIn(300);
        });
    }
    
    // General Settings Form Validation
    function initGeneralSettingsForm() {
        $('.giga-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('.giga-btn-primary');
            const originalText = $submitBtn.val();
            
            // Validate required fields
            let isValid = true;
            const $requiredFields = $form.find('[required]');
            
            $requiredFields.each(function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if (!value) {
                    isValid = false;
                    $field.addClass('giga-error');
                    showFieldError($field, 'This field is required');
                } else {
                    $field.removeClass('giga-error');
                    hideFieldError($field);
                }
            });
            
            // Validate URLs
            const $urlFields = $form.find('input[type="url"]');
            $urlFields.each(function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if (value && !isValidURL(value)) {
                    isValid = false;
                    $field.addClass('giga-error');
                    showFieldError($field, 'Please enter a valid URL');
                } else {
                    $field.removeClass('giga-error');
                    hideFieldError($field);
                }
            });
            
            if (isValid) {
                // Show loading state
                $submitBtn.prop('disabled', true).val('Saving...');
                
                // Submit the form
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $form.serialize() + '&action=giga_sp_save_settings',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Settings saved successfully!', 'success');
                            
                            // Update logo preview if changed
                            updateLogoPreview();
                            
                            // Reset button
                            $submitBtn.prop('disabled', false).val(originalText);
                        } else {
                            showNotification('Error saving settings: ' + response.data.message, 'error');
                            $submitBtn.prop('disabled', false).val(originalText);
                        }
                    },
                    error: function() {
                        showNotification('Error saving settings. Please try again.', 'error');
                        $submitBtn.prop('disabled', false).val(originalText);
                    }
                });
            }
        });
    }
    
    // Update logo preview
    function updateLogoPreview() {
        const $logoInput = $('#organization_logo');
        const $logoPreview = $('.giga-logo-preview');
        
        if ($logoInput.length && $logoPreview.length) {
            const logoUrl = $logoInput.val().trim();
            
            if (logoUrl) {
                $logoPreview.html(`<img src="${logoUrl}" alt="Organization Logo" class="giga-logo-img">`);
            } else {
                $logoPreview.empty();
            }
        }
    }
    
    // URL validation helper
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Reset settings
    $('.giga-reset-settings').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to reset all settings to default? This action cannot be undone.')) {
            return;
        }
        
        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.html('<span class="spinner"></span> Resetting...').prop('disabled', true);
        
        // Simulate reset
        setTimeout(function() {
            // Reset form fields
            $('.giga-settings-form')[0].reset();
            
            // Clear logo preview
            $('.giga-logo-preview').empty();
            
            $btn.html(originalText).prop('disabled', false);
            
            showNotification('Settings reset successfully!', 'success');
        }, 1000);
    });
    
    // Initialize general settings when the page loads
    $(document).ready(function() {
        if ($('.giga-settings-form').length) {
            initGeneralSettingsTabs();
            initGeneralSettingsForm();
            
            // Initialize logo preview on load
            updateLogoPreview();
        }
    });
    
})(jQuery);
