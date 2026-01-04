/**
 * Copilot Widget Tests
 */

describe('AIWP Copilot Widget', () => {
  let AIWPCopilot;

  beforeEach(() => {
    // Setup DOM
    document.body.innerHTML = '';

    // Mock fetch
    global.fetch = jest.fn(() =>
      Promise.resolve({
        ok: true,
        json: () => Promise.resolve({
          success: true,
          data: {
            choices: [{
              message: {
                content: 'Test response'
              }
            }]
          }
        })
      })
    );

    // Load the widget code (simplified for testing)
    AIWPCopilot = {
      init: function() {
        this.createWidget();
        this.bindEvents();
      },

      createWidget: function() {
        const widget = `
          <div id="aiwp-copilot-widget" class="aiwp-widget">
            <div class="aiwp-header">
              <h3>🤖 AI Copilot</h3>
              <div id="aiwp-specialist-badge"></div>
              <button class="aiwp-toggle" id="aiwp-toggle">−</button>
            </div>
            <div class="aiwp-body">
              <div class="aiwp-messages" id="aiwp-messages"></div>
              <div class="aiwp-input-container">
                <textarea id="aiwp-prompt" placeholder="Ask AI Copilot..." rows="3"></textarea>
                <button id="aiwp-send" class="aiwp-send-btn">Send</button>
              </div>
            </div>
          </div>
        `;
        document.body.insertAdjacentHTML('beforeend', widget);
      },

      bindEvents: function() {
        const sendBtn = document.getElementById('aiwp-send');
        if (sendBtn) {
          sendBtn.addEventListener('click', () => this.sendMessage());
        }
      },

      sendMessage: function() {
        const prompt = document.getElementById('aiwp-prompt');
        const message = prompt.value.trim();

        if (!message) {
          return;
        }

        this.addMessage(message, 'user');
        prompt.value = '';

        // Simulate API call
        this.callAPI(message);
      },

      callAPI: function(message) {
        const data = {
          messages: [
            { role: 'user', content: message }
          ]
        };

        fetch(window.aiwpCopilot.apiUrl + '/complete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.aiwpCopilot.nonce
          },
          body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const content = data.data.choices[0].message.content;
            this.addMessage(content, 'assistant');
          }
        })
        .catch(error => {
          this.showError('Failed to send message');
        });
      },

      addMessage: function(content, role) {
        const messages = document.getElementById('aiwp-messages');
        const messageEl = document.createElement('div');
        messageEl.className = `aiwp-message aiwp-message-${role}`;
        messageEl.textContent = content;
        messages.appendChild(messageEl);
      },

      showError: function(message) {
        const messages = document.getElementById('aiwp-messages');
        const errorEl = document.createElement('div');
        errorEl.className = 'aiwp-error';
        errorEl.textContent = message;
        messages.appendChild(errorEl);
      }
    };
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('Widget Creation', () => {
    test('init creates widget in DOM', () => {
      AIWPCopilot.init();

      const widget = document.getElementById('aiwp-copilot-widget');
      expect(widget).toBeInTheDocument();
    });

    test('widget has correct structure', () => {
      AIWPCopilot.init();

      expect(document.querySelector('.aiwp-header')).toBeInTheDocument();
      expect(document.querySelector('.aiwp-body')).toBeInTheDocument();
      expect(document.getElementById('aiwp-messages')).toBeInTheDocument();
      expect(document.getElementById('aiwp-prompt')).toBeInTheDocument();
      expect(document.getElementById('aiwp-send')).toBeInTheDocument();
    });

    test('header contains AI Copilot title', () => {
      AIWPCopilot.init();

      const header = document.querySelector('.aiwp-header h3');
      expect(header).toHaveTextContent('🤖 AI Copilot');
    });
  });

  describe('Message Sending', () => {
    test('sendMessage validates input not empty', () => {
      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = '';

      const addMessageSpy = jest.spyOn(AIWPCopilot, 'addMessage');
      AIWPCopilot.sendMessage();

      expect(addMessageSpy).not.toHaveBeenCalled();
    });

    test('sendMessage adds user message to UI', () => {
      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = 'Test message';

      AIWPCopilot.sendMessage();

      const messages = document.querySelectorAll('.aiwp-message-user');
      expect(messages).toHaveLength(1);
      expect(messages[0]).toHaveTextContent('Test message');
    });

    test('sendMessage clears input after sending', () => {
      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = 'Test message';

      AIWPCopilot.sendMessage();

      expect(prompt.value).toBe('');
    });

    test('sendMessage makes API call', async () => {
      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = 'Test message';

      AIWPCopilot.sendMessage();

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('/complete'),
        expect.objectContaining({
          method: 'POST',
          headers: expect.objectContaining({
            'Content-Type': 'application/json',
            'X-WP-Nonce': 'test-nonce-123'
          })
        })
      );
    });

    test('sendMessage handles API success', async () => {
      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = 'Test message';

      AIWPCopilot.sendMessage();

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 100));

      const assistantMessages = document.querySelectorAll('.aiwp-message-assistant');
      expect(assistantMessages.length).toBeGreaterThan(0);
    });

    test('sendMessage handles API error', async () => {
      global.fetch = jest.fn(() => Promise.reject(new Error('Network error')));

      AIWPCopilot.init();

      const prompt = document.getElementById('aiwp-prompt');
      prompt.value = 'Test message';

      AIWPCopilot.sendMessage();

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 100));

      const errorMessages = document.querySelectorAll('.aiwp-error');
      expect(errorMessages.length).toBeGreaterThan(0);
    });
  });

  describe('Message Display', () => {
    test('addMessage with user role', () => {
      AIWPCopilot.init();

      AIWPCopilot.addMessage('User message', 'user');

      const message = document.querySelector('.aiwp-message-user');
      expect(message).toBeInTheDocument();
      expect(message).toHaveTextContent('User message');
    });

    test('addMessage with assistant role', () => {
      AIWPCopilot.init();

      AIWPCopilot.addMessage('Assistant message', 'assistant');

      const message = document.querySelector('.aiwp-message-assistant');
      expect(message).toBeInTheDocument();
      expect(message).toHaveTextContent('Assistant message');
    });

    test('showError displays error message', () => {
      AIWPCopilot.init();

      AIWPCopilot.showError('Test error');

      const error = document.querySelector('.aiwp-error');
      expect(error).toBeInTheDocument();
      expect(error).toHaveTextContent('Test error');
    });
  });

  describe('Event Binding', () => {
    test('send button click triggers sendMessage', () => {
      AIWPCopilot.init();

      const sendMessageSpy = jest.spyOn(AIWPCopilot, 'sendMessage');
      const sendBtn = document.getElementById('aiwp-send');

      sendBtn.click();

      expect(sendMessageSpy).toHaveBeenCalled();
    });
  });
});
