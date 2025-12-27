(function ($) {
  $(document).ready(function () {
    if (typeof AIWP_API === 'undefined') return;

    // --- 1. WPScanner (The Eyes) ---
    const WPScanner = {
      getSiteContext: function () {
        const context = {
          title: document.title,
          url: window.location.href,
          page_structure: this.scanDOM(),
        };
        return JSON.stringify(context);
      },
      scanDOM: function () {
        const sections = {};
        $('header, footer, main, section, .hero, .navigation, h1, h2').each(function () {
          const tag = this.tagName.toLowerCase();
          const cls = this.className ? '.' + this.className.split(' ').join('.') : '';
          const id = this.id ? '#' + this.id : '';
          const key = tag + id + cls;
          sections[key] = $(this).text().trim().substring(0, 300);
        });
        return sections;
      }
    };

    // --- 2. Icons ---
    const icons = {
      gear: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>`,
      send: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>`,
      history: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>`,
      check: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
      x: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`,
      pointer: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path><path d="M13 13l6 6"></path></svg>`,
      clear: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`
    };

    // --- 3. UI Construction ---
    const commandCenter = $(`
      <div id="aiwp-command-center">
        <div id="aiwp-suggestions"></div>
        <div id="aiwp-confirm-bar">
            <span class="aiwp-confirm-text">Apply this change?</span>
            <div class="aiwp-confirm-btns">
                <button class="aiwp-btn-confirm" title="Keep Changes">${icons.check}</button>
                <button class="aiwp-btn-cancel" title="Undo">${icons.x}</button>
            </div>
        </div>
        <div id="aiwp-history-panel"></div>
        <div id="aiwp-main-bar">
           <div class="aiwp-logo-glow"></div>
           <textarea id="aiwp-prompt" placeholder="Message God Mode..."></textarea>
           <div class="aiwp-actions">
              <button class="aiwp-icon-btn aiwp-select-btn" title="Select Element">${icons.pointer}</button>
              <button class="aiwp-icon-btn aiwp-clear-select" title="Clear Selection">${icons.clear}</button>
              <button class="aiwp-icon-btn aiwp-history-toggle" title="Toggle History">${icons.history}</button>
              <button class="aiwp-icon-btn aiwp-settings-btn">${icons.gear}</button>
              <button id="aiwp-send-cmd" class="aiwp-icon-btn">${icons.send}</button>
           </div>
        </div>
      </div>
    `);

    $('body').append(commandCenter);

    const $prompt = $('#aiwp-prompt');
    const $suggestions = $('#aiwp-suggestions');
    const $history = $('#aiwp-history-panel');
    const $confirmBar = $('#aiwp-confirm-bar');
    const $selectBtn = $('.aiwp-select-btn');

    let currentAction = null;
    let rollbackData = null;
    let selectionMode = false;
    let selectedElement = null;
    let pendingAction = null;

    // --- 4. Logic & Handlers ---

    // Toggle History
    $('.aiwp-history-toggle').on('click', () => $history.toggleClass('expanded'));

    // Selection Logic
    $selectBtn.on('click', function () {
      selectionMode = !selectionMode;
      $(this).toggleClass('active');
      $('body').toggleClass('aiwp-selecting');

      if (!selectionMode) {
        $('.aiwp-hover-outline').removeClass('aiwp-hover-outline');
      }
    });

    $('body').on('mouseover', function (e) {
      if (!selectionMode) return;
      if ($(e.target).closest('#aiwp-command-center').length) return;

      $('.aiwp-hover-outline').removeClass('aiwp-hover-outline');
      $(e.target).addClass('aiwp-hover-outline');
    });

    $('body').on('click', function (e) {
      if (!selectionMode) return;
      if ($(e.target).closest('#aiwp-command-center').length) return;

      e.preventDefault();
      e.stopPropagation();

      $('.aiwp-selected').removeClass('aiwp-selected');
      $(e.target).addClass('aiwp-selected');
      selectedElement = e.target;

      // Generate a simple selector
      let tag = e.target.tagName.toLowerCase();
      let cls = e.target.className ? '.' + e.target.className.split(' ').filter(c => !c.startsWith('aiwp-')).join('.') : '';
      let id = e.target.id ? '#' + e.target.id : '';
      let simpleSelector = tag + id + cls;

      $prompt.val(`[Target: ${simpleSelector}] `).focus();

      // Turn off selection mode
      selectionMode = false;
      $selectBtn.removeClass('active');
      $('body').removeClass('aiwp-selecting');
      $('.aiwp-hover-outline').removeClass('aiwp-hover-outline');
    });

    // Clear selection manually
    $('.aiwp-clear-select').on('click', function () {
      selectedElement = null;
      $('.aiwp-selected').removeClass('aiwp-selected');
      $prompt.focus();
    });

    // Escape cancels selection mode
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && selectionMode) {
        selectionMode = false;
        $selectBtn.removeClass('active');
        $('body').removeClass('aiwp-selecting');
        $('.aiwp-hover-outline').removeClass('aiwp-hover-outline');
      }
    });

    // Auto-resize
    $prompt.on('input', function () {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
      if (this.value.length > 0) $('#aiwp-send-cmd').addClass('aiwp-send-active');
      else $('#aiwp-send-cmd').removeClass('aiwp-send-active');
    });

    // Proactive Audit
    async function runAudit() {
      try {
        const res = await fetch(AIWP_API.rest_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': AIWP_API.nonce },
          body: JSON.stringify({
            message: "AUDIT_REQUEST: Analyze site and suggest 3 improvements.",
            context: WPScanner.getSiteContext()
          })
        });
        const data = await res.json();
        if (data.reply) {
          // Parse out suggestions if possible, or just show defaults if AI is being generic
          const suggestions = data.reply.match(/\[(.*?)\]/) ? data.reply.match(/\[(.*?)\]/)[1].split(',') : ["Improve Design", "Rewrite Copy", "Add Premium Hover"];
          addSuggestions(suggestions.map(s => s.trim()));
        }
      } catch (e) { console.error("Audit failed."); }
    }

    function addSuggestions(chips) {
      $suggestions.empty();
      chips.forEach(text => {
        const $chip = $(`<div class="aiwp-chip">${text}</div>`);
        $chip.on('click', () => { $prompt.val(text).trigger('input').focus(); });
        $suggestions.append($chip);
      });
      $suggestions.addClass('visible');
    }

    runAudit();

    // Command Execution
    async function executeCommand() {
      const msg = $prompt.val().trim();
      if (!msg) return;

      pendingAction = null;
      rollbackData = null;
      $confirmBar.hide();

      $('#aiwp-main-bar').addClass('aiwp-processing');
      $prompt.val('').css('height', 'auto');
      addLog('user', msg);

      try {
        const context = WPScanner.getSiteContext();
        let payload = { message: msg, context: context };

        // Attach specific element context if selected
        if (selectedElement && msg.includes('[Target:')) {
          payload.selector = msg.match(/\[Target: (.*?)\]/)[1];
          payload.html = selectedElement.outerHTML.substring(0, 1000); // Limit size
        }

        const response = await fetch(AIWP_API.rest_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': AIWP_API.nonce },
          body: JSON.stringify(payload)
        });
        const data = await response.json();
        $('#aiwp-main-bar').removeClass('aiwp-processing');

        addLog('ai', data.reply || "Done.");

        if (data.action && data.action !== 'none') {
          handleAIAction(data);
        }

      } catch (e) {
        $('#aiwp-main-bar').removeClass('aiwp-processing');
        addLog('ai', "Error: Connection lost.");
      }
    }

    function handleAIAction(data) {
      const action = data.action;
      const actionData = data.data || {};
      const selectorFromAI = actionData.selector || (selectedElement ? buildSelector(selectedElement) : null);
      if (selectorFromAI && !actionData.selector) {
        actionData.selector = selectorFromAI;
      }

      if (action === 'apply_css' && actionData.selector) {
        const cssMap = actionData.css || {};
        const $el = $(actionData.selector);
        if ($el.length && Object.keys(cssMap).length) {
          rollbackData = { type: 'css', selector: actionData.selector, oldStyle: $el.attr('style') || '' };
          $el.css(cssMap);
          pendingAction = { type: 'css', selector: actionData.selector, css: cssMap };
          showConfirmBar("Style applied. Save to keep?");
        } else if (!$el.length) {
          addLog('ai', "[System] Could not find element for selector: " + actionData.selector);
        } else {
          addLog('ai', "[System] AI returned apply_css without rules.");
        }
      }

      if (action === 'update_content' && actionData.selector && actionData.html) {
        const $el = $(actionData.selector);
        if ($el.length) {
          rollbackData = { type: 'html', selector: actionData.selector, oldHTML: $el.html() };
          $el.html(actionData.html);
          pendingAction = { type: 'html', selector: actionData.selector, html: actionData.html };
          showConfirmBar("Content updated. Save to keep?");
        } else {
          addLog('ai', "[System] Could not find element for selector: " + actionData.selector);
        }
      }

      if (action === 'write_file') {
        addLog('ai', "[System] AI updated file: " + actionData.path);
      }
    }

    function showConfirmBar(text) {
      $('.aiwp-confirm-text').text(text);
      $confirmBar.css('display', 'flex');
    }

    async function persistChanges() {
      if (!pendingAction || !AIWP_API.save_url || !AIWP_API.post_id) {
        addLog('ai', "[System] Nothing to save or missing post ID.");
        pendingAction = null;
        return;
      }

      const payload = { post_id: AIWP_API.post_id, selector: pendingAction.selector };
      if (pendingAction.type === 'css') payload.css = pendingAction.css || {};
      if (pendingAction.type === 'html') payload.html = pendingAction.html || '';

      try {
        const res = await fetch(AIWP_API.save_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': AIWP_API.nonce },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        addLog('ai', data.message || "[System] Changes saved.");
      } catch (err) {
        addLog('ai', "[System] Failed to save changes.");
      } finally {
        pendingAction = null;
        $confirmBar.hide();
      }
    }

    $('.aiwp-btn-confirm').on('click', persistChanges);

    $('.aiwp-btn-cancel').on('click', () => {
      $confirmBar.hide();
      if (rollbackData) {
        if (rollbackData.type === 'css') $(rollbackData.selector).attr('style', rollbackData.oldStyle);
        if (rollbackData.type === 'html') $(rollbackData.selector).html(rollbackData.oldHTML);
      }
      pendingAction = null;
      addLog('ai', "[System] Changes reverted.");
    });

    $('#aiwp-send-cmd').on('click', executeCommand);
    $prompt.on('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); executeCommand(); } });

    function addLog(type, text) {
      const log = $(`<div class="aiwp-log aiwp-log-${type}"></div>`).text(text);
      $history.append(log).scrollTop($history[0].scrollHeight);
      if (!$history.hasClass('expanded')) {
        $history.addClass('expanded');
        setTimeout(() => $history.removeClass('expanded'), 8000);
      }
      const MAX_LOGS = 60;
      if ($history.children().length > MAX_LOGS) {
        $history.children().slice(0, $history.children().length - MAX_LOGS).remove();
      }
    }

    function buildSelector(el) {
      if (!el) return null;
      const $el = $(el);
      const id = $el.attr('id');
      if (id) return '#' + id;
      const classes = ($el.attr('class') || '').split(' ').filter(c => c && !c.startsWith('aiwp-')).join('.');
      const tag = el.tagName ? el.tagName.toLowerCase() : 'div';
      return tag + (classes ? '.' + classes : '');
    }
  });
})(jQuery);
