/**
 * AIWP Copilot - Main JavaScript
 * Version: 2.0.0
 */

(function ($) {
    'use strict';

    const AIWPCopilot = {

        selectedElement: null,
        elementSelectorActive: false,

        /**
         * Initialize
         */
        init: function () {
            this.createWidget();
            this.bindEvents();
            this.loadProviders();
            this.loadSpecialists();

            if (window.aiwpCopilot.debugMode) {
                console.log('AIWP Copilot initialized');
            }
        },

        /**
         * Create widget
         */
        createWidget: function () {
            const widget = `
                <div id="aiwp-copilot-widget" class="aiwp-widget">
                    <div class="aiwp-header">
                        <h3>🤖 AI Copilot</h3>
                        <div class="aiwp-header-controls">
                            <button class="aiwp-settings-toggle" id="aiwp-settings-toggle" title="Settings">
                                <span class="dashicons dashicons-admin-generic"></span>
                            </button>
                            <button class="aiwp-toggle" id="aiwp-toggle">−</button>
                        </div>
                    </div>
                    
                    <!-- Settings Panel -->
                    <div class="aiwp-settings-panel" id="aiwp-settings-panel" style="display:none;">
                        <div class="aiwp-setting-row">
                            <label>Provider & Model:</label>
                            <select id="aiwp-provider-switcher" class="aiwp-control-select">
                                <option>Loading...</option>
                            </select>
                        </div>
                        <div class="aiwp-setting-row">
                            <label>Specialist:</label>
                            <select id="aiwp-specialist-switcher" class="aiwp-control-select">
                                <option>Loading...</option>
                            </select>
                        </div>
                        <div class="aiwp-setting-row">
                            <button id="aiwp-element-selector-toggle" class="aiwp-tool-btn">
                                <span class="dashicons dashicons-location"></span>
                                Select Element
                            </button>
                            <span id="aiwp-selected-element-info"></span>
                        </div>
                    </div>
                    
                    <div class="aiwp-body">
                        <div class="aiwp-messages" id="aiwp-messages"></div>
                        <div class="aiwp-input-container">
                            <textarea id="aiwp-prompt" 
                                      placeholder="Ask AI Copilot..."
                                      rows="3"></textarea>
                            <button id="aiwp-send" class="aiwp-send-btn">
                                <span class="dashicons dashicons-arrow-up-alt2"></span>
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(widget);
        },

        /**
         * Bind events
         */
        bindEvents: function () {
            const self = this;

            // Toggle widget
            $('#aiwp-toggle').on('click', function () {
                $('.aiwp-body').slideToggle();
                $(this).text($(this).text() === '−' ? '+' : '−');
            });

            // Toggle settings panel
            $('#aiwp-settings-toggle').on('click', function () {
                $('#aiwp-settings-panel').slideToggle();
            });

            // Send message
            $('#aiwp-send').on('click', function () {
                self.sendMessage();
            });

            // Enter to send (Shift+Enter for new line)
            $('#aiwp-prompt').on('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Provider switcher
            $('#aiwp-provider-switcher').on('change', function () {
                const parts = $(this).val().split('|');
                const provider_id = parts[0];
                const model = parts[1];
                self.switchProvider(provider_id, model);
            });

            // Specialist switcher
            $('#aiwp-specialist-switcher').on('change', function () {
                const specialist_id = $(this).val();
                self.switchSpecialist(specialist_id);
            });

            // Element selector toggle
            $('#aiwp-element-selector-toggle').on('click', function () {
                if (self.elementSelectorActive) {
                    self.deactivateElementSelector();
                } else {
                    self.activateElementSelector();
                }
            });
        },

        /**
         * Load providers
         */
        loadProviders: function () {
            $.ajax({
                url: window.aiwpCopilot.apiUrl + '/providers',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': window.aiwpCopilot.nonce
                },
                success: function (response) {
                    if (response.success && response.data) {
                        AIWPCopilot.populateProviders(response.data);
                    }
                },
                error: function () {
                    $('#aiwp-provider-switcher').html('<option>Error loading providers</option>');
                }
            });
        },

        /**
         * Populate provider dropdown
         */
        populateProviders: function (providers) {
            const $select = $('#aiwp-provider-switcher');
            $select.empty();

            providers.forEach(function (provider) {
                if (!provider.is_configured) return; // Skip unconfigured providers

                provider.models.forEach(function (model) {
                    const isCurrentActive = provider.is_active && model.id === provider.current_model;
                    const freeBadge = model.free ? ' 🆓' : '';
                    const label = `${provider.name} - ${model.name}${freeBadge}`;
                    const value = `${provider.id}|${model.id}`;

                    const option = $('<option>')
                        .val(value)
                        .text(label)
                        .prop('selected', isCurrentActive);

                    $select.append(option);
                });
            });
        },

        /**
         * Load specialists
         */
        loadSpecialists: function () {
            $.ajax({
                url: window.aiwpCopilot.apiUrl + '/specialists',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': window.aiwpCopilot.nonce
                },
                success: function (response) {
                    if (response.success && response.data) {
                        AIWPCopilot.populateSpecialists(response.data);
                    }
                }
            });
        },

        /**
         * Populate specialist dropdown
         */
        populateSpecialists: function (specialists) {
            const $select = $('#aiwp-specialist-switcher');
            $select.empty();

            Object.values(specialists).forEach(function (specialist) {
                const label = `${specialist.icon} ${specialist.name}`;
                const option = $('<option>')
                    .val(specialist.id)
                    .text(label)
                    .prop('selected', specialist.id === window.aiwpCopilot.specialist);

                $select.append(option);
            });
        },

        /**
         * Switch provider
         */
        switchProvider: function (provider_id, model) {
            $.ajax({
                url: window.aiwpCopilot.apiUrl + '/providers/switch',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': window.aiwpCopilot.nonce,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    provider_id: provider_id,
                    model: model
                }),
                success: function (response) {
                    if (response.success) {
                        AIWPCopilot.addMessage('system', `✅ Switched to ${provider_id} - ${model}`);
                    }
                }
            });
        },

        /**
         * Switch specialist
         */
        switchSpecialist: function (specialist_id) {
            // Update via settings API (simplified for now)
            AIWPCopilot.addMessage('system', `🎯 Switched to specialist: ${specialist_id}`);
            window.aiwpCopilot.specialist = specialist_id;
        },

        /**
         * Activate element selector
         */
        activateElementSelector: function () {
            this.elementSelectorActive = true;
            $('body').addClass('aiwp-selector-active');
            $('#aiwp-element-selector-toggle').addClass('active').text('Cancel Selection');

            const self = this;

            // Highlight on hover
            $(document).on('mouseover.aiwp', '*', function (e) {
                if ($(this).closest('#aiwp-copilot-widget').length) return;
                e.stopPropagation();
                $('.aiwp-highlight').removeClass('aiwp-highlight');
                $(this).addClass('aiwp-highlight');
            });

            // Select on click
            $(document).on('click.aiwp', '*', function (e) {
                if ($(this).closest('#aiwp-copilot-widget').length) return;
                e.preventDefault();
                e.stopPropagation();
                self.selectElement(this);
                self.deactivateElementSelector();
            });
        },

        /**
         * Deactivate element selector
         */
        deactivateElementSelector: function () {
            this.elementSelectorActive = false;
            $('body').removeClass('aiwp-selector-active');
            $('.aiwp-highlight').removeClass('aiwp-highlight');
            $('#aiwp-element-selector-toggle').removeClass('active').html('<span class="dashicons dashicons-location"></span> Select Element');

            $(document).off('mouseover.aiwp');
            $(document).off('click.aiwp');
        },

        /**
         * Select element
         */
        selectElement: function (element) {
            const selector = this.getCSSPath(element);
            const text = $(element).text().substring(0, 200).trim();

            this.selectedElement = {
                selector: selector,
                text: text
            };

            $('#aiwp-selected-element-info').text('✓ ' + selector);
            this.addMessage('system', `📍 Selected element: ${selector}`);
        },

        /**
         * Get CSS path for element
         */
        getCSSPath: function (el) {
            if (!(el instanceof Element)) return;

            // If element has ID, use it
            if (el.id) {
                return '#' + el.id;
            }

            // Build path
            var path = [];
            while (el.nodeType === Node.ELEMENT_NODE) {
                var selector = el.nodeName.toLowerCase();

                if (el.className) {
                    var classes = el.className.trim().split(/\s+/);
                    if (classes.length) {
                        selector += '.' + classes[0];
                    }
                }

                path.unshift(selector);

                if (el.parentNode === null || el.nodeName === 'BODY') {
                    break;
                }

                el = el.parentNode;
            }

            return path.join(' > ');
        },

        /**
         * Send message
         */
        sendMessage: function () {
            const prompt = $('#aiwp-prompt').val().trim();

            if (!prompt) {
                return;
            }

            // Add user message
            this.addMessage('user', prompt);

            // Clear input
            $('#aiwp-prompt').val('');

            // Show loading
            this.addMessage('assistant', '...');
            const $loadingMsg = $('#aiwp-messages .aiwp-message:last');

            // Get current page context
            const postId = $('#post_ID').val() || null;

            // Prepare messages
            const messages = [
                {
                    role: 'user',
                    content: prompt
                }
            ];

            // Add element context if selected
            if (this.selectedElement) {
                messages.unshift({
                    role: 'system',
                    content: `User has selected element: ${this.selectedElement.selector}\nElement text: "${this.selectedElement.text}"`
                });
            }

            // Add page context if available
            if (postId) {
                messages.unshift({
                    role: 'system',
                    content: `You are helping edit WordPress post ID ${postId}.`
                });
            }

            // Send request
            $.ajax({
                url: window.aiwpCopilot.apiUrl + '/complete',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': window.aiwpCopilot.nonce,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    messages: messages,
                    options: {
                        temperature: 0.7,
                        max_tokens: 2000
                    }
                }),
                success: function (response) {
                    $loadingMsg.remove();

                    if (response.success && response.data.choices && response.data.choices[0]) {
                        const content = response.data.choices[0].message.content;
                        AIWPCopilot.addMessage('assistant', content);
                    } else {
                        AIWPCopilot.addMessage('error', 'No response received');
                    }
                },
                error: function (xhr) {
                    $loadingMsg.remove();

                    let errorMsg = 'An error occurred';

                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error.message;
                    }

                    AIWPCopilot.addMessage('error', errorMsg);
                }
            });
        },

        /**
         * Add message to chat
         */
        addMessage: function (role, content) {
            const $messages = $('#aiwp-messages');

            const className = role === 'user' ? 'aiwp-message-user' :
                role === 'error' ? 'aiwp-message-error' :
                    role === 'system' ? 'aiwp-message-system' :
                        'aiwp-message-assistant';

            const $message = $('<div>')
                .addClass('aiwp-message')
                .addClass(className)
                .html(this.formatContent(content));

            $messages.append($message);

            // Scroll to bottom
            $messages[0].scrollTop = $messages[0].scrollHeight;
        },

        /**
         * Format content (basic markdown support)
         */
        formatContent: function (content) {
            // Escape HTML
            content = $('<div>').text(content).html();

            // Basic markdown
            content = content.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
            content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
            content = content.replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>');
            content = content.replace(/\*([^\*]+)\*/g, '<em>$1</em>');
            content = content.replace(/\n/g, '<br>');

            return content;
        }
    };

    // Initialize when ready
    $(document).ready(function () {
        // Only initialize in post editor
        if ($('#post_ID').length || $('.block-editor').length) {
            AIWPCopilot.init();
        }
    });

})(jQuery);
