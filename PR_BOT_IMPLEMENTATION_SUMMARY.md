# Claude AI + GitHub PR Bot 実装サマリー

## 📋 実装完了内容

Claude AIを使用したGitHub PR Review Botの実装が完了しました。

### ✅ 実装された機能

1. **自動PR レビュー**
   - PRが作成または更新されると自動的にClaudeがコードレビュー
   - レビュー結果を自動的にGitHub PRにコメント投稿

2. **セキュリティ機能**
   - APIキー、パスワード、秘密鍵などの機密情報を自動マスキング
   - `.env`ファイルや秘密鍵ファイルを自動除外
   - PRの差分のみをClaudeに送信（リポジトリ全体は共有しない）

3. **包括的なレビュー**
   - コード品質
   - セキュリティ
   - パフォーマンス
   - テストカバレッジ
   - ドキュメント

## 📁 作成されたファイル

### Services (ビジネスロジック)

1. **`backend/app/Services/SensitiveDataFilter.php`**
   - 機密情報のフィルタリング
   - APIキー、パスワード、トークンなどを自動マスキング
   - 機密ファイルの除外

2. **`backend/app/Services/GitHubService.php`**
   - GitHub API との通信
   - PR情報の取得
   - Webhook署名の検証
   - レビューコメントの投稿

3. **`backend/app/Services/ClaudeService.php`**
   - Claude AI API との通信
   - レビュープロンプトの生成
   - レビュー結果のフォーマット

### Controller

4. **`backend/app/Http/Controllers/Api/WebhookController.php`**
   - GitHub Webhookの受信
   - PRレビューの処理フロー制御
   - ログベースの監視

### Configuration

5. **`backend/.env.example`** (更新)
   - 環境変数のテンプレート追加
   - `GITHUB_TOKEN`
   - `GITHUB_WEBHOOK_SECRET`
   - `ANTHROPIC_API_KEY`

6. **`backend/routes/api.php`** (更新)
   - Webhookエンドポイントの追加
   - `POST /api/webhooks/github/pr`

7. **`backend/app/Http/Middleware/VerifyCsrfToken.php`** (更新)
   - Webhook用のCSRF保護除外

### Documentation

8. **`PR_BOT_SETUP.md`**
    - 詳細なセットアップガイド
    - セキュリティ機能の説明
    - トラブルシューティング

9. **`README.md`** (更新)
    - PR Bot機能の追加
    - Webhookエンドポイントの追加

## 🔧 技術仕様

### アーキテクチャ

```
GitHub PR Event
    ↓
GitHub Webhook
    ↓
Laravel Webhook Endpoint (/api/webhooks/github/pr)
    ↓
WebhookController
    ↓
┌─────────────────────────────────────┐
│ 1. GitHubService: PR情報取得        │
│ 2. SensitiveDataFilter: 機密情報除外│
│ 3. ClaudeService: AIレビュー        │
│ 4. GitHubService: コメント投稿      │
│ 5. Log: 処理結果をログに記録        │
└─────────────────────────────────────┘
    ↓
GitHub PR Comment
```

### データフロー

1. **GitHub Webhook受信**
   - PRのopen/update/reopenイベントを受信
   - 署名検証 (HMAC SHA-256)

2. **PR情報取得**
   - GitHub APIからPR詳細を取得
   - 差分 (diff) を取得
   - 変更ファイル一覧を取得

3. **機密情報フィルタリング**
   - APIキー、パスワードなどをマスキング
   - `.env`, 秘密鍵ファイルを除外
   - フィルタリング統計を記録

4. **Claude AIレビュー**
   - フィルター済み差分をClaudeに送信
   - 包括的なレビューを取得
   - コード品質、セキュリティ、パフォーマンスを分析

5. **レビュー投稿**
   - GitHub APIでPRにコメント投稿
   - 処理結果をログに記録

## 🔐 セキュリティ機能

### 自動マスキング対象

- APIキーとトークン (`api_key`, `token`, `secret`)
- パスワード (`password`, `DB_PASSWORD`, `MYSQL_PASSWORD`)
- AWSキー (`AKIA...`, `aws_secret_access_key`)
- 秘密鍵 (RSA, OpenSSH private keys)
- JWT トークン (`eyJ...`)
- 内部IPアドレス (10.x.x.x, 192.168.x.x, 172.16-31.x.x)

### 除外ファイル

- `.env`, `.env.local`, `.env.production`
- `credentials.json`, `secrets.yaml`, `secrets.yml`
- 秘密鍵: `id_rsa`, `id_dsa`, `*.pem`, `*.key`, `*.p12`, `*.pfx`

## 📊 ログとモニタリング

レビューの実行状況はLaravelのログで追跡できます：

### ログレベル

- **INFO**: 正常な処理フロー
  - `Starting PR review`: レビュー開始
  - `Filtered PR data`: フィルタリング完了
  - `PR review completed successfully`: レビュー完了

- **ERROR**: エラー発生時
  - `Failed to fetch PR data from GitHub`: GitHub API エラー
  - `Failed to get review from Claude AI`: Claude API エラー
  - `Failed to post review to GitHub`: GitHub投稿エラー

### ログ確認方法

```bash
# すべてのPRレビューログ
docker-compose logs backend | grep "PR review"

# エラーのみ
docker-compose logs backend | grep ERROR

# リアルタイム監視
docker-compose logs -f backend | grep "PR review\|webhook"
```

## 🚀 セットアップ手順

### 1. 環境変数の設定

`backend/.env` に追加:

```env
# GitHub Integration
GITHUB_TOKEN=ghp_your_token_here
GITHUB_WEBHOOK_SECRET=your_secret_here

# Claude AI Integration
ANTHROPIC_API_KEY=sk-ant-your_key_here
```

### 2. GitHub Webhook設定

- **Payload URL**: `https://expense-manager.com/api/webhooks/github/pr`
- **Content type**: `application/json`
- **Secret**: 環境変数の`GITHUB_WEBHOOK_SECRET`と同じ値
- **Events**: Pull requests のみ

### 3. コンテナ再起動

```bash
docker-compose restart backend
```

## 🧪 テスト方法

1. テスト用PRを作成
2. 30秒〜2分待つ
3. PRにClaudeのレビューコメントが投稿される

### ログ確認

```bash
# Webhookログ
docker-compose logs backend | grep webhook

# レビュー処理ログ
docker-compose logs backend | grep "PR review"

# エラーログ
docker-compose logs backend | grep ERROR

# リアルタイム監視
docker-compose logs -f backend
```

## 📈 レビュー内容

Claudeは以下の観点でレビューします:

1. **Summary** - 変更の概要と総合評価
2. **Strengths** - 良い点
3. **Issues Found** - 発見された問題
4. **Suggestions** - 改善提案
5. **Security Notes** - セキュリティに関する注意事項
6. **Final Recommendation** - 最終推奨 (Approve/Request Changes/Comment)

## 💰 コスト見積もり

### Claude AI (Anthropic)

- **モデル**: Claude 3.5 Sonnet
- **料金**:
  - Input: $3.00 / million tokens
  - Output: $15.00 / million tokens

### 使用例

- **小規模PR** (100-300行): 約 $0.02 - $0.05
- **中規模PR** (300-800行): 約 $0.05 - $0.12
- **大規模PR** (800-2000行): 約 $0.12 - $0.30

**月間コスト例**:
- 小規模PR 50件/月: 約 $1.50 - $2.50
- 中規模PR 30件/月: 約 $1.50 - $3.60
- 大規模PR 10件/月: 約 $1.20 - $3.00
- **合計**: 約 $4.20 - $9.10/月

## ⚠️ 注意事項

### セキュリティ

1. **コードはAnthropicに送信されます**
   - 機密性の高いコードの場合は使用を慎重に検討
   - 機密情報は自動マスキングされますが、100%の保証はありません

2. **APIキーの管理**
   - `.env`ファイルは絶対にGitにコミットしない
   - 定期的にAPIキーをローテーション

3. **Webhook Secret**
   - 強力なランダム文字列を使用
   - 定期的に更新

### パフォーマンス

1. **大規模PR**
   - 1000行以上のPRは処理に時間がかかる可能性
   - 可能であれば小さなPRに分割することを推奨

2. **同時処理**
   - 現在は同期処理
   - 本番環境ではキュー (Queue) の使用を推奨

## 🔧 カスタマイズ

### レビュー観点の変更

`backend/app/Services/ClaudeService.php` の `buildReviewPrompt()` メソッドを編集

### フィルタリングルールの追加

`backend/app/Services/SensitiveDataFilter.php` の `$sensitivePatterns` 配列に追加

### レビューモデルの変更

`backend/app/Services/ClaudeService.php` の `$model` プロパティを変更:
- `claude-3-5-sonnet-20241022` (デフォルト)
- `claude-3-opus-20240229` (より高精度、高コスト)
- `claude-3-haiku-20240307` (より高速、低コスト)

## 📚 参考リンク

- [PR_BOT_SETUP.md](PR_BOT_SETUP.md) - 詳細なセットアップガイド
- [Claude API Documentation](https://docs.anthropic.com/)
- [GitHub Webhooks Documentation](https://docs.github.com/webhooks)

## ✅ チェックリスト

セットアップ完了前に確認:

- [ ] GitHub Personal Access Token を取得
- [ ] Anthropic API Key を取得
- [ ] Webhook Secret を生成
- [ ] `.env` に環境変数を設定
- [ ] GitHub Webhook を設定
- [ ] コンテナを再起動
- [ ] テストPRで動作確認
- [ ] ログでレビュー実行を確認

## 🎉 完了

Claude AI + GitHub PR Review Bot の実装が完了しました！

PRを作成すると、自動的にClaudeがコードレビューを実行し、
セキュリティに配慮しながら包括的なフィードバックを提供します。
