# Quick PR Command Usage (`pr`)

## Overview

The `pr` command is a simplified version for creating GitHub Pull Requests from your current branch with minimal input required.

## Features

- ✅ **Auto-detects current branch** - No need to specify which branch you're on
- ✅ **Auto-generates title** - Creates a readable title from your branch name
- ✅ **Auto-generates description** - Builds PR body from your commit history
- ✅ **Zero required arguments** - Just type `php artisan pr` and go!

## Usage

### Basic Usage (Simplest)

```bash
# Create PR from current branch with auto-generated title and body
docker-compose exec backend php artisan pr
```

**Example:**
```bash
# If you're on branch: feature/add-user-authentication
# Generated title: "Add User Authentication"
# Body: Auto-generated from commits
```

### With Custom Title

```bash
docker-compose exec backend php artisan pr --title="Add user authentication feature"
```

### Change Base Branch

```bash
# Default base is 'main', change to 'develop'
docker-compose exec backend php artisan pr --base=develop
```

### Create Draft PR

```bash
docker-compose exec backend php artisan pr --draft
```

### Combined Options

```bash
docker-compose exec backend php artisan pr --title="WIP: New feature" --base=develop --draft
```

## How It Works

### 1. Title Generation

The command automatically generates a title from your branch name:

| Branch Name | Generated Title |
|-------------|----------------|
| `feature/add-login` | "Add Login" |
| `fix/payment-bug` | "Payment Bug" |
| `bugfix-user-profile` | "User Profile" |
| `chore/update-deps` | "Update Deps" |
| `my-awesome-feature` | "My Awesome Feature" |

**Rules:**
- Removes common prefixes: `feature/`, `fix/`, `bugfix/`, `hotfix/`, `chore/`, `docs/`, `refactor/`
- Replaces hyphens (`-`) and underscores (`_`) with spaces
- Capitalizes each word

### 2. Body Generation

Auto-generates PR description from commit history:

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

### 3. Branch Detection

Automatically detects your current branch using:
```bash
git rev-parse --abbrev-ref HEAD
```

## Prerequisites

1. **GitHub Token**: Set in `backend/.env`
   ```env
   GITHUB_TOKEN=ghp_your_token_here
   ```

2. **Not on base branch**: You must be on a feature branch
   ```bash
   # ✅ Good
   git checkout -b feature/new-feature

   # ❌ Bad - will error
   git checkout main
   ```

3. **Git remote configured**: Your repo must have a GitHub remote
   ```bash
   git remote -v
   # origin  git@github.com:owner/repo.git (fetch)
   # origin  git@github.com:owner/repo.git (push)
   ```

## Examples

### Example 1: Quick PR from feature branch

```bash
# 1. Create and checkout feature branch
git checkout -b feature/add-dark-mode

# 2. Make your changes and commit
git add .
git commit -m "Add dark mode toggle"
git commit -m "Update theme colors"

# 3. Push to remote
git push -u origin feature/add-dark-mode

# 4. Create PR with one command
docker-compose exec backend php artisan pr
```

**Output:**
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

```bash
# On branch: feature/experimental-api
docker-compose exec backend php artisan pr --title="WIP: Experimental API design" --draft
```

### Example 3: PR to develop branch

```bash
# On branch: hotfix/critical-bug
docker-compose exec backend php artisan pr --title="Fix critical payment bug" --base=develop
```

## Error Handling

### Error: "Cannot create PR: current branch 'main' is same as base branch"

**Solution:** Switch to a feature branch first
```bash
git checkout -b feature/my-feature
```

### Error: "GITHUB_TOKEN is not configured in .env file"

**Solution:** Add your GitHub token to `backend/.env`
```env
GITHUB_TOKEN=ghp_your_token_here
```

### Error: "Could not parse GitHub repository from git remote"

**Solution:** Ensure your remote is a GitHub URL
```bash
git remote set-url origin git@github.com:owner/repo.git
# or
git remote set-url origin https://github.com/owner/repo.git
```

## Comparison with Full Command

| Feature | `pr` (Simple) | `pr:create` (Full) |
|---------|---------------|-------------------|
| Command length | Shorter | Longer |
| Title required | No (auto-generated) | Yes (required argument) |
| Branch detection | Automatic | Automatic (with --head option) |
| Body generation | Automatic | Automatic (with --body option) |
| Custom body | Not supported | Supported with --body flag |
| Use case | Quick daily PRs | Complex PRs needing custom descriptions |

## Tips

1. **Branch Naming Convention**: Use descriptive branch names with prefixes for better auto-generated titles
   ```bash
   # Good
   feature/user-authentication
   fix/payment-validation

   # Avoid
   test
   tmp
   my-branch
   ```

2. **Commit Messages**: Write clear commit messages as they'll appear in the PR body
   ```bash
   # Good
   git commit -m "Add login form validation"

   # Avoid
   git commit -m "wip"
   git commit -m "fix"
   ```

3. **Push Before PR**: Always push your branch to remote before creating PR
   ```bash
   git push -u origin your-branch-name
   ```

## Troubleshooting

### Check if command is available
```bash
docker-compose exec backend php artisan list | grep pr
```

### View detailed help
```bash
docker-compose exec backend php artisan pr --help
```

### Check current branch
```bash
git branch --show-current
```

### View what would be in PR body
```bash
git log main..HEAD --oneline --no-merges
```

## Advanced Usage

### Using inside container
```bash
# Enter backend container
docker-compose exec backend bash

# Run command directly (no docker-compose prefix needed)
php artisan pr
php artisan pr --title="My feature" --base=develop
```

### Alias for even faster usage

Add to your `~/.bashrc` or `~/.zshrc`:
```bash
alias dpr='docker-compose exec backend php artisan pr'
```

Then use:
```bash
dpr
dpr --title="Quick fix"
dpr --base=develop --draft
```

## Related Commands

- `php artisan pr:create` - Full-featured PR creation with more options
- See [PR_COMMAND_USAGE.md](PR_COMMAND_USAGE.md) for detailed documentation on `pr:create`

## License

MIT
