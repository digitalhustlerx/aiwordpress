# 🚀 AIWP Copilot v2.0 - Complete Installation Guide

## 🎯 What's New in v2.0

### ✨ AI Specialist Personas (NEW!)
Choose from specialized AI experts optimized for specific tasks:

**Core Specialists (Tier 1):**
- 🎨 **Frontend Specialist** - Modern UI/UX, Tailwind CSS, glassmorphism
- 📝 **SEO Expert** - Meta tags, schema markup, keyword optimization
- ✍️ **Copywriter (SaaS)** - Tech/startup voice, high-converting copy
- 🛍️ **Copywriter (E-commerce)** - Product descriptions, conversion copy

**Pro Specialists (Tier 2 - Coming Soon):**
- 🎯 Marketing Strategist
- 🏗️ WordPress Architect
- 💰 E-commerce Optimizer
- 🎬 Content Creator
- 🔧 DevOps Specialist
- ♿ Accessibility Expert

### 🔧 Enhanced Features
- ✅ jQuery deprecation fixes
- ✅ Error handling with rate limiting (100 req/hour)
- ✅ Deep Elementor integration
- ✅ User-friendly error messages with emojis
- ✅ Debug mode for troubleshooting
- ✅ Specialist badge in widget UI

---

## 📦 Installation

### Step 1: Upload Plugin

1. Download `aiwp-copilot-v2-complete.zip`
2. Extract to: `C:\Users\Digi\Documents\Antigravity\aiwordpress\wp-content\plugins\`
3. Folder structure should be:
   ```
   wp-content/
   └── plugins/
       └── aiwp-copilot/
           ├── aiwp-copilot.php
           ├── assets/
           └── includes/
   ```

### Step 2: Activate Plugin

1. Go to `http://localhost:8888/wp-admin/plugins.php`
2. Find "AIWP Copilot"
3. Click "Activate"

### Step 3: Configure Settings

1. Go to **Settings → AIWP Copilot**
2. Enter your API credentials

#### Recommended: Groq (Free + Fast)

```
API Provider: OpenAI (Groq uses OpenAI-compatible API)
API Key: Get from https://console.groq.com/keys
API Endpoint: https://api.groq.com/openai/v1
Model: llama-3.3-70b-versatile
```

#### Alternative: OpenAI

```
API Provider: OpenAI
API Key: Get from https://platform.openai.com/api-keys
API Endpoint: https://api.openai.com/v1
Model: gpt-4o or gpt-4o-mini
```

### Step 4: Choose Your Specialist

1. Scroll to **AI Specialist Mode** section
2. Select from dropdown (e.g., "🎨 Frontend Specialist")
3. Read the description to understand what each specialist does
4. Click "Save Settings"

---

## 🎨 Using Specialists

### Frontend Specialist 🎨

**Best for:**
- Creating modern UI components
- Designing landing pages
- Building Elementor sections
- Responsive layouts

**Example Prompt:**
```
Create a hero section with:
- Dark gradient background
- Glassmorphism card
- Call-to-action buttons
- Mobile responsive
```

### SEO Expert 📝

**Best for:**
- Meta title/description optimization
- Schema markup generation
- Keyword-optimized content
- Heading structure

**Example Prompt:**
```
Optimize this page for the keyword "WordPress AI plugin":
- Write meta title (50-60 chars)
- Write meta description (150-160 chars)
- Add schema markup
- Suggest H2/H3 headings
```

### Copywriter (SaaS) ✍️

**Best for:**
- SaaS landing pages
- Feature descriptions
- Pricing pages
- Email sequences

**Example Prompt:**
```
Write a SaaS landing page for an AI writing tool:
- Headline with value proposition
- 3 key benefits
- Social proof section
- CTA button copy
```

### Copywriter (E-commerce) 🛍️

**Best for:**
- Product descriptions
- Category pages
- Sales copy
- Urgency/scarcity messaging

**Example Prompt:**
```
Write a product description for wireless earbuds:
- Emotional opening hook
- 5 key benefits (not features)
- Address objections
- Add urgency
```

---

## 🛠️ File Structure

```
aiwp-copilot/
├── aiwp-copilot.php (Main plugin file)
├── assets/
│   ├── css/
│   │   └── copilot.css
│   └── js/
│       ├── copilot.js (Main widget)
│       └── elementor-scanner.js (Elementor integration)
├── includes/
│   ├── core/
│   │   ├── class-copilot-indexer.php
│   │   └── class-copilot-filesystem.php
│   ├── providers/
│   │   ├── interface-provider.php
│   │   ├── class-provider-registry.php
│   │   └── class-openai-provider.php
│   ├── specialists/ ← NEW!
│   │   ├── class-specialist-registry.php
│   │   └── class-specialist-engine.php
│   ├── class-error-handler.php
│   ├── rest-endpoints.php
│   └── settings-page.php
└── README.md
```

---

## 🧪 Testing

### Test the Plugin

1. **Open any post/page** in WordPress editor
2. **Look for the AI Copilot widget** in bottom-right corner
3. **Type a test prompt**, e.g., "Write a paragraph about AI"
4. **Check specialist badge** shows your selected specialist

### Test Each Specialist

Create a new page and test each specialist:

1. **Go to Settings → AIWP Copilot**
2. **Change specialist** to "Frontend Specialist"
3. **Save settings**
4. **Open a new page**
5. **Ask:** "Create a hero section with a gradient background"
6. **Repeat** for each specialist

---

## 🐛 Troubleshooting

### Plugin Won't Activate

Check error logs:
```bash
# In PowerShell at: C:\Users\Digi\Documents\Antigravity\aiwordpress\
npx wp-env run cli cat wp-content/debug.log
```

### API Errors

1. **🔑 API Key Error**
   - Verify key in Settings → AIWP Copilot
   - Test with: https://console.groq.com/playground

2. **⏱️ Rate Limit**
   - You hit 100 requests/hour
   - Wait 1 hour or clear transient:
   ```php
   delete_transient('aiwp_rate_limit_' . get_current_user_id());
   ```

3. **❌ Connection Error**
   - Check API endpoint URL
   - Verify internet connection

### Enable Debug Mode

1. **Go to Settings → AIWP Copilot**
2. **Check "Enable debug logging"**
3. **Save settings**
4. **Check logs:** `wp-content/debug.log`

---

## 📊 Feature Comparison

| Feature | v1.1 | v2.0 |
|---------|------|------|
| Basic AI completion | ✅ | ✅ |
| Error handling | ✅ | ✅ |
| Elementor integration | ✅ | ✅ |
| jQuery fixes | ✅ | ✅ |
| **AI Specialist Personas** | ❌ | ✅ |
| **Specialist Badge UI** | ❌ | ✅ |
| **Custom System Prompts** | ❌ | ✅ |
| **Specialist Dropdown** | ❌ | ✅ |

---

## 🔮 Roadmap

### v2.1 (Next)
- [ ] Specialist tier system (Free/Pro/Agency)
- [ ] Custom specialist creation
- [ ] Brand voice training
- [ ] Template library per specialist

### v2.2 (Future)
- [ ] Multi-turn conversations
- [ ] Context memory
- [ ] Image generation (DALL-E integration)
- [ ] Voice input

---

## 💡 Tips & Best Practices

### Getting Better Results

1. **Be specific** - "Create a dark hero section with glassmorphism" vs. "make a hero"
2. **Use specialist strengths** - Frontend for design, SEO for optimization
3. **Iterate** - Ask follow-up questions to refine
4. **Combine specialists** - Use SEO Expert first, then Copywriter

### Optimal Prompts

**Frontend Specialist:**
```
Create a [component] with:
- [Specific style/design system]
- [Key features]
- [Responsive requirements]
```

**SEO Expert:**
```
Optimize for keyword "[keyword]":
- Meta title (target length)
- Meta description (target length)
- H2/H3 structure
- Schema type
```

**Copywriter:**
```
Write [content type] for [audience]:
- [Tone/voice]
- [Key message]
- [Call to action]
```

---

## 📞 Support

### Need Help?

1. **Check this README first**
2. **Enable debug mode** and check logs
3. **Test API key** at https://console.groq.com/playground
4. **Share error message** with developer

### Common Issues

**"Plugin could not be activated"**
- Missing files - reupload complete plugin
- PHP version - requires PHP 7.4+

**"No specialist selected"**
- Go to Settings → AIWP Copilot
- Select a specialist from dropdown
- Save settings

**Widget doesn't appear**
- Clear browser cache
- Check if you're in post editor
- Inspect console for errors (F12)

---

## 🎯 Quick Reference

### API Endpoints

```
GET  /wp-json/aiwp/v1/specialists       - List available specialists
POST /wp-json/aiwp/v1/complete          - Send completion request
GET  /wp-json/aiwp/v1/context/{id}      - Get page context
POST /wp-json/aiwp/v1/validate          - Validate API credentials
```

### Specialist IDs

```php
'default'              // General AI
'frontend'             // 🎨 Frontend Specialist
'seo'                  // 📝 SEO Expert
'copywriter_saas'      // ✍️ Copywriter (SaaS)
'copywriter_ecommerce' // 🛍️ Copywriter (E-commerce)
```

---

## 📄 License

GPL v2 or later

---

## 🚀 Ready to Go!

Your AIWP Copilot v2.0 is ready! Start by:

1. ✅ Activating the plugin
2. ✅ Configuring API credentials (recommend Groq)
3. ✅ Selecting your first specialist
4. ✅ Testing in a new post

**Enjoy your AI-powered WordPress assistant!** 🎉
