# Claude Code Slash Command Guide

## `/create-pr` - Create Pull Request

A custom Claude Code slash command for quickly creating GitHub Pull Requests from your current branch.

## Quick Start

```
/create-pr
```

That's it! This will:
1. ✅ Detect your current branch automatically
2. ✅ Generate a PR title from the branch name
3. ✅ Generate PR description from commit history
4. ✅ Create the PR on GitHub

## Usage Options

### Basic (Auto-generated title)
```
/create-pr
```

### With custom title
```
/create-pr --title="Add user authentication feature"
```

### Target different base branch
```
/create-pr --base=develop
```

### Create as draft
```
/create-pr --draft
```

### Combined options
```
/create-pr --title="WIP: Experimental feature" --base=develop --draft
```

## How Title Generation Works

The command automatically converts your branch name into a readable PR title:

| Branch Name | Generated Title |
|-------------|----------------|
| `feature/add-login` | "Add Login" |
| `fix/payment-bug` | "Payment Bug" |
| `bugfix-user-profile` | "User Profile" |
| `chore/update-deps` | "Update Deps" |
| `hotfix/critical-fix` | "Critical Fix" |

**Rules:**
- Removes common prefixes: `feature/`, `fix/`, `bugfix/`, `hotfix/`, `chore/`, `docs/`, `refactor/`
- Replaces hyphens (`-`) and underscores (`_`) with spaces
- Capitalizes each word

## PR Description Auto-Generation

The command automatically generates a PR description from your commit history:

```markdown
## Summary

This PR includes the following changes:

- abc1234 Add user login functionality
- def5678 Fix validation errors
- ghi9012 Update tests

## Test Plan

- [ ] Tests added/updated
- [ ] Manual testing completed
- [ ] Documentation updated
```

## Prerequisites

Before using this command, ensure:

1. **GITHUB_TOKEN is configured** in `backend/.env`:
   ```env
   GITHUB_TOKEN=ghp_your_token_here
   ```

2. **You're on a feature branch** (not main/develop):
   ```bash
   git checkout -b feature/my-feature
   ```

3. **Your branch is pushed to remote**:
   ```bash
   git push -u origin feature/my-feature
   ```

## Alternative: Terminal Command

If you prefer to use the terminal directly:

```bash
# Simple version
docker-compose exec backend php artisan pr

# With options
docker-compose exec backend php artisan pr --title="Custom title" --base=develop --draft
```

## Examples

### Example 1: Quick PR from feature branch

```bash
# 1. Create feature branch
git checkout -b feature/add-dark-mode

# 2. Make changes and commit
git add .
git commit -m "Add dark mode toggle"
git commit -m "Update theme colors"

# 3. Push to remote
git push -u origin feature/add-dark-mode

# 4. Create PR using Claude Code
/create-pr
```

**Result:**
```
Creating PR from current branch: feature/add-dark-mode
Generated title: Add Dark Mode
Repository: yourusername/expense-manager
From: feature/add-dark-mode → To: main

✅ Pull Request created successfully!

PR #42: Add Dark Mode
URL: https://github.com/yourusername/expense-manager/pull/42
State: Open
```

### Example 2: Draft PR with custom title

```
/create-pr --title="WIP: Experimental API design" --draft
```

### Example 3: PR targeting develop branch

```
/create-pr --title="Fix critical payment bug" --base=develop
```

## Error Messages

### "GITHUB_TOKEN is not configured in .env file"

**Solution:** Add your GitHub token to `backend/.env`:
```env
GITHUB_TOKEN=ghp_your_token_here
```

Get a token from: https://github.com/settings/tokens

### "Cannot create PR: current branch 'main' is same as base branch"

**Solution:** Switch to a feature branch:
```bash
git checkout -b feature/my-feature
```

### "Could not parse GitHub repository from git remote"

**Solution:** Ensure your git remote is properly configured:
```bash
git remote set-url origin git@github.com:owner/repo.git
# or
git remote set-url origin https://github.com/owner/repo.git
```

## Tips for Best Results

### 1. Use descriptive branch names
```bash
# Good
git checkout -b feature/user-authentication
git checkout -b fix/payment-validation

# Avoid
git checkout -b test
git checkout -b tmp
```

### 2. Write clear commit messages
```bash
# Good
git commit -m "Add login form validation"
git commit -m "Fix password strength checker"

# Avoid
git commit -m "wip"
git commit -m "fix"
```

### 3. Push before creating PR
```bash
git push -u origin your-branch-name
```

## How It Works

1. **Slash Command Execution**: When you type `/create-pr` in Claude Code
2. **Argument Parsing**: The command parses any options you provided
3. **Laravel Command**: Executes `docker-compose exec -T backend php artisan pr [options]`
4. **Branch Detection**: Gets current branch using `git rev-parse --abbrev-ref HEAD`
5. **Title Generation**: Converts branch name to readable title
6. **Body Generation**: Extracts commits using `git log base..HEAD`
7. **GitHub API**: Creates PR using GitHub REST API
8. **Response**: Displays PR number, URL, and state

## File Locations

- **Slash Command Implementation**: `.claude/skills/create-pr.ts`
- **Slash Command Documentation**: `.claude/skills/create-pr.md`
- **Laravel Artisan Command**: `backend/app/Console/Commands/CreatePR.php`
- **Usage Guide**: This file (`SLASH_COMMAND_GUIDE.md`)

## Related Commands

- Terminal version: `docker-compose exec backend php artisan pr`
- See also: [PR_QUICK_COMMAND.md](PR_QUICK_COMMAND.md) for detailed terminal usage

## Troubleshooting

### Check if slash command is available
```bash
ls -la .claude/skills/create-pr.*
```

### Check if Laravel command is available
```bash
docker-compose exec backend php artisan list | grep pr
```

### View current branch
```bash
git branch --show-current
```

### Test GitHub token
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" https://api.github.com/user
```

## License

MIT
