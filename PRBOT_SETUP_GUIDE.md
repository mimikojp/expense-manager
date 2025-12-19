# PR Bot Setup Guide

Complete guide to set up the automated PR review bot using Claude AI.

## Current Status

✅ Backend services are implemented
✅ Webhook endpoint is configured
✅ Environment variables are set
❌ **Anthropic API has insufficient credits**
⚠️ **GitHub webhook needs to be configured**

---

## Prerequisites

### 1. Anthropic API Key with Credits

Your current API key has insufficient balance. Follow these steps:

#### Get/Update API Key:

1. Go to **https://console.anthropic.com/**
2. Sign in with your account
3. Navigate to **Settings → API Keys**
4. Create a new key or use existing one
5. Copy the key (starts with `sk-ant-api03-...`)

#### Add Credits:

1. In Anthropic Console, go to **Settings → Plans & Billing**
2. Choose an option:
   - **Pay-as-you-go**: Add $10-20 in credits
   - **Subscribe**: Monthly plan with included credits

**Estimated Cost per PR Review**: $0.05 - $0.50
**Recommended Starting Balance**: $10-20

#### Update .env File:

```env
ANTHROPIC_API_KEY=sk-ant-api03-your-new-key-here
```

Then restart the backend:
```bash
docker-compose restart backend
```

---

## GitHub Webhook Setup

### Step 1: Access Repository Settings

1. Go to your GitHub repository: **https://github.com/mimikojp/expense-manager**
2. Click **Settings** tab
3. Click **Webhooks** in the left sidebar
4. Click **Add webhook**

### Step 2: Configure Webhook

Fill in the webhook configuration:

**Payload URL:**
```
https://dev.expense-manager.com/api/webhooks/github/pr
```

**Content type:**
```
application/json
```

**Secret:**
```
your_webhook_secret_from_env_file
```
*(Use the value from `GITHUB_WEBHOOK_SECRET` in your .env file)*

**Which events would you like to trigger this webhook?**
- Select **"Let me select individual events"**
- Check only: ✅ **Pull requests**
- Uncheck all others

**Active:**
- ✅ Check this box

### Step 3: Save Webhook

Click **Add webhook** button at the bottom.

### Step 4: Test Webhook

GitHub will immediately send a test ping. You should see:
- ✅ Green checkmark if successful
- ❌ Red X if failed (check logs)

---

## Testing the PR Bot

### Method 1: Test Command

Run the diagnostic test:

```bash
docker-compose exec backend php artisan prbot:test
```

Expected output:
```
🤖 PR Bot Diagnostic Test

1️⃣ Checking environment configuration...
   ✅ GITHUB_TOKEN is configured
   ✅ GITHUB_WEBHOOK_SECRET is configured
   ✅ ANTHROPIC_API_KEY is configured
✅ Environment configured correctly

2️⃣ Testing GitHub API connection...
✅ GitHub API connection successful
   User: mimikojp

3️⃣ Testing Claude AI connection...
✅ Claude AI connection successful

4️⃣ Testing webhook signature verification...
✅ Webhook signature verification working

🎉 All tests passed! PR Bot is configured correctly.
```

### Method 2: Test with Real PR

Run test on a specific PR:

```bash
docker-compose exec backend php artisan prbot:test \
  --owner=mimikojp \
  --repo=expense-manager \
  --pr=1
```

### Method 3: Create a Test PR

1. Create a new branch:
   ```bash
   git checkout -b test-pr-bot
   ```

2. Make a small change:
   ```bash
   echo "# Test PR Bot" >> TEST.md
   git add TEST.md
   git commit -m "Test PR bot"
   git push -u origin test-pr-bot
   ```

3. Create PR on GitHub

4. Wait for Claude AI review comment to appear (30-60 seconds)

---

## How It Works

```
┌─────────────┐
│   GitHub    │
│  (PR Event) │
└──────┬──────┘
       │ webhook
       ↓
┌─────────────────────┐
│   Nginx (SSL)       │
│  Port 443           │
└──────┬──────────────┘
       │ proxy
       ↓
┌─────────────────────┐
│  Laravel Backend    │
│  /api/webhooks/...  │
└──────┬──────────────┘
       │
       ├─→ Verify webhook signature
       │
       ├─→ Fetch PR diff & files
       │
       ├─→ Filter sensitive data
       │
       ├─→ Send to Claude AI
       │
       ├─→ Format response
       │
       └─→ Post comment to GitHub
```

---

## Troubleshooting

### Check Webhook Deliveries

1. Go to GitHub Repository → Settings → Webhooks
2. Click on your webhook
3. Click **Recent Deliveries** tab
4. Check the response status:
   - **200**: Success
   - **401**: Invalid signature
   - **500**: Server error

### Check Laravel Logs

```bash
docker-compose exec backend tail -f storage/logs/laravel.log
```

### Common Issues

#### ❌ "Invalid signature"
- Webhook secret mismatch
- Check `GITHUB_WEBHOOK_SECRET` in `.env`
- Re-configure webhook in GitHub with correct secret

#### ❌ "Insufficient credits"
- Add credits to Anthropic account
- Update API key in `.env`
- Restart backend container

#### ❌ "GitHub API error"
- Check `GITHUB_TOKEN` is valid
- Token needs `repo` scope
- Token might be expired

#### ❌ "Connection timeout"
- Check if containers are running: `docker-compose ps`
- Check if Nginx is proxying correctly
- Check firewall/network settings

### Manual Test with curl

```bash
# Test webhook endpoint is accessible
curl https://dev.expense-manager.com/api/webhooks/github/pr

# Should return: {"error":"Invalid signature"}
```

---

## Environment Variables Summary

Required in `backend/.env`:

```env
# GitHub Integration
GITHUB_TOKEN=your_github_personal_access_token_here
GITHUB_WEBHOOK_SECRET=your_webhook_secret_here

# Claude AI Integration
ANTHROPIC_API_KEY=your_anthropic_api_key_here
```

---

## What the Bot Reviews

The PR Bot analyzes and provides feedback on:

### 1. Code Quality
- Code organization and structure
- Naming conventions and readability
- Code duplication
- Complexity and maintainability

### 2. Best Practices
- Design patterns usage
- SOLID principles
- DRY (Don't Repeat Yourself)
- Error handling

### 3. Security
- Input validation
- SQL injection risks
- XSS vulnerabilities
- Authentication/Authorization issues

### 4. Performance
- Potential bottlenecks
- Database query optimization
- Resource usage

### 5. Testing
- Test coverage
- Edge cases
- Error scenarios

### 6. Documentation
- Code comments
- API documentation
- README updates

---

## Review Comment Format

When a PR is opened or updated, the bot posts a comment like this:

```markdown
## 🤖 Claude AI Code Review

### Summary
[Brief overview of the changes and overall assessment]

### Strengths
[What's done well in this PR]

### Issues Found
[List critical issues, if any]

### Suggestions
[Improvements and recommendations]

### Security Notes
[Security-related observations]

### Final Recommendation
- [ ] Approve
- [ ] Request Changes
- [ ] Comment Only

**Note:** This review is automated using Claude AI. Please use human judgment for final decisions.

---
*This review was automatically generated by Claude AI*
*Review Statistics: 1,234 lines reviewed*
```

---

## Cost Estimation

Based on Claude 3.5 Sonnet pricing:

| PR Size | Tokens Used | Estimated Cost |
|---------|-------------|----------------|
| Small (< 100 lines) | 5,000 | $0.05 |
| Medium (100-500 lines) | 15,000 | $0.15 |
| Large (500-1000 lines) | 30,000 | $0.30 |
| Very Large (> 1000 lines) | 50,000+ | $0.50+ |

**Monthly Cost Estimate:**
- 10 PRs/month: ~$2-5
- 50 PRs/month: ~$10-25
- 100 PRs/month: ~$20-50

---

## Security Notes

### Sensitive Data Protection

The bot automatically filters sensitive information:

- API keys and tokens
- Passwords and secrets
- Email addresses
- Credit card numbers
- Private keys
- Database credentials

Filtered patterns include:
- `GITHUB_TOKEN=xxx` → `GITHUB_TOKEN=[REDACTED]`
- `password: "xxx"` → `password: [REDACTED]`
- `sk-ant-api03-xxx` → `[REDACTED_API_KEY]`

### Webhook Security

- Webhook signature verification (HMAC-SHA256)
- HTTPS/SSL encryption
- CSRF protection disabled for webhook endpoint
- Request validation

---

## Quick Reference Commands

```bash
# Test PR Bot configuration
docker-compose exec backend php artisan prbot:test

# Test specific PR review
docker-compose exec backend php artisan prbot:test --owner=mimikojp --repo=expense-manager --pr=1

# Check Laravel logs
docker-compose exec backend tail -f storage/logs/laravel.log

# Restart backend
docker-compose restart backend

# Check container status
docker-compose ps

# View webhook route
docker-compose exec backend php artisan route:list | grep webhook
```

---

## Next Steps

1. ✅ Fix Anthropic API credits issue
   - Add credits at https://console.anthropic.com/settings/billing
   - Update API key in `.env`
   - Restart backend

2. ✅ Configure GitHub webhook
   - Add webhook in repository settings
   - Use webhook URL: `https://dev.expense-manager.com/api/webhooks/github/pr`
   - Set secret from `.env` file

3. ✅ Test the bot
   - Run diagnostic test
   - Create a test PR
   - Verify review comment appears

---

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review Laravel logs for errors
3. Verify all environment variables are set
4. Test each component individually using the diagnostic command

## Links

- **Anthropic Console**: https://console.anthropic.com/
- **GitHub Repository**: https://github.com/mimikojp/expense-manager
- **Webhook Endpoint**: https://dev.expense-manager.com/api/webhooks/github/pr
