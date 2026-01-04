/**
 * AIWP Elementor Scanner
 * Deep Elementor API integration
 */

(function($) {
    'use strict';
    
    const AIWPElementorScanner = {
        
        /**
         * Check if Elementor is loaded
         */
        isElementorLoaded: function() {
            return typeof elementor !== 'undefined' && elementor.documents;
        },
        
        /**
         * Get page structure
         */
        getPageStructure: function() {
            if (!this.isElementorLoaded()) {
                return null;
            }
            
            const elements = elementor.getPreviewView().$el.find('.elementor-element');
            const structure = [];
            
            elements.each(function() {
                const $el = $(this);
                const model = elementor.getPreviewView().getElementModel($el.data('id'));
                
                if (model) {
                    structure.push({
                        id: model.get('id'),
                        type: model.get('elType'),
                        widgetType: model.get('widgetType'),
                        settings: model.get('settings').toJSON()
                    });
                }
            });
            
            return structure;
        },
        
        /**
         * Add widget to container
         */
        addWidget: function(containerID, widgetType, settings) {
            if (!this.isElementorLoaded()) {
                return false;
            }
            
            try {
                const container = elementor.getPreviewView().getContainer(containerID);
                
                if (!container) {
                    console.error('Container not found:', containerID);
                    return false;
                }
                
                elementor.getPreviewView().addElement({
                    elType: 'widget',
                    widgetType: widgetType,
                    settings: settings || {}
                }, container);
                
                return true;
            } catch (error) {
                console.error('Error adding widget:', error);
                return false;
            }
        },
        
        /**
         * Create section
         */
        createSection: function(columns, settings) {
            if (!this.isElementorLoaded()) {
                return null;
            }
            
            try {
                const model = elementor.getPreviewView().addSection({
                    elements: Array(columns).fill(null).map(() => ({
                        id: elementor.helpers.getUniqueId(),
                        elType: 'column',
                        settings: {},
                        elements: []
                    })),
                    settings: settings || {}
                });
                
                return model ? model.get('id') : null;
            } catch (error) {
                console.error('Error creating section:', error);
                return null;
            }
        },
        
        /**
         * Update widget settings
         */
        updateWidget: function(widgetID, settings) {
            if (!this.isElementorLoaded()) {
                return false;
            }
            
            try {
                const element = elementor.getPreviewView().getElementModel(widgetID);
                
                if (!element) {
                    console.error('Widget not found:', widgetID);
                    return false;
                }
                
                element.get('settings').set(settings);
                
                return true;
            } catch (error) {
                console.error('Error updating widget:', error);
                return false;
            }
        },
        
        /**
         * Get widget by ID
         */
        getWidget: function(widgetID) {
            if (!this.isElementorLoaded()) {
                return null;
            }
            
            const element = elementor.getPreviewView().getElementModel(widgetID);
            
            if (!element) {
                return null;
            }
            
            return {
                id: element.get('id'),
                type: element.get('elType'),
                widgetType: element.get('widgetType'),
                settings: element.get('settings').toJSON()
            };
        }
    };
    
    // Expose globally for AIWP Copilot
    window.AIWPElementorScanner = AIWPElementorScanner;
    
    // Auto-scan on load if Elementor is present
    $(document).ready(function() {
        if (AIWPElementorScanner.isElementorLoaded()) {
            console.log('AIWP Elementor Scanner loaded');
            
            if (window.aiwpCopilot && window.aiwpCopilot.debugMode) {
                console.log('Page Structure:', AIWPElementorScanner.getPageStructure());
            }
        }
    });
    
})(jQuery);
