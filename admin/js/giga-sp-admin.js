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
                        <div class="giga-stat-icon" style="background: #ef4444; color: white;">✕</div>
                        <div class="giga-stat-content">
                            <h3>${results.failed}</h3>
                            <p>Failed</p>
                        </div>
                    </div>
                </div>
                <div class="giga-stat-card">
                    <div class="giga-stat-header">
                        <div class="giga-stat-icon" style="background: #f59e0b; color: white;">⚠</div>
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
    
})(jQuery);
