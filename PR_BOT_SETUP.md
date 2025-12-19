# Claude AI GitHub PR Review Bot セットアップガイド

## 概要

このプロジェクトには、Claude AIを使用した自動PR（Pull Request）レビューBotが統合されています。
PRが作成または更新されると、自動的にコードレビューを実行し、GitHubのPRにコメントとして投稿します。

### 主な機能

- ✅ **自動コードレビュー**: PRが作成/更新されると自動的にClaudeがレビュー
- ✅ **セキュリティ重視**: APIキー、パスワード、秘密鍵などの機密情報を自動マスキング
- ✅ **差分のみ共有**: PRの差分のみをClaudeに送信（リポジトリ全体は共有しない）
- ✅ **包括的なレビュー**: コード品質、セキュリティ、パフォーマンス、テストなどを分析
- ✅ **GitHub統合**: レビュー結果を自動的にPRコメントとして投稿

## セキュリティ機能

### 機密情報の自動フィルタリング

以下の機密情報は自動的にマスキングされます：

- **APIキーとトークン**: `api_key`, `token`, `secret`
- **パスワード**: データベースパスワード、アプリケーションパスワード
- **AWSキー**: AWS Access Key, Secret Access Key
- **秘密鍵**: RSA, OpenSSH秘密鍵
- **JWT トークン**
- **内部IPアドレス**

### 除外ファイル

以下のファイルは自動的にレビューから除外されます：

- `.env`, `.env.local`, `.env.production`
- `credentials.json`, `secrets.yaml`
- 秘密鍵ファイル (`*.pem`, `*.key`, `id_rsa`, 等)

## セットアップ手順

### 1. 必要なAPIキーの取得

#### GitHub Personal Access Token

1. GitHubにログイン
2. Settings → Developer settings → Personal access tokens → Generate new token
3. 必要なスコープ:
   - `repo` (リポジトリへのフルアクセス)
   - `write:discussion` (PRへのコメント投稿)
4. トークンをコピーして保存

#### Claude AI API Key (Anthropic)

1. https://console.anthropic.com/ にアクセス
2. アカウント作成/ログイン
3. API Keys → Create Key
4. APIキーをコピーして保存

#### GitHub Webhook Secret

1. ランダムな文字列を生成（推奨: 最低32文字）
```bash
openssl rand -hex 32
```

### 2. 環境変数の設定

バックエンドの `.env` ファイルに以下を追加：

```bash
# GitHub Integration for PR Review Bot
GITHUB_TOKEN=ghp_your_github_token_here
GITHUB_WEBHOOK_SECRET=your_generated_webhook_secret_here

# Claude AI Integration
ANTHROPIC_API_KEY=sk-ant-your_anthropic_api_key_here
```

### 3. GitHub Webhookの設定

1. GitHubリポジトリ → Settings → Webhooks → Add webhook

2. Webhook設定:
   - **Payload URL**: `https://expense-manager.com/api/webhooks/github/pr`
   - **Content type**: `application/json`
   - **Secret**: `.env`で設定した`GITHUB_WEBHOOK_SECRET`を入力
   - **Which events would you like to trigger this webhook?**
     - Let me select individual events
     - ✅ Pull requests (選択)
   - **Active**: ✅ チェック

3. Add webhook をクリック

### 4. コンテナの再起動

環境変数を反映させるため、コンテナを再起動：

```bash
docker-compose restart backend
```

## 使用方法

### 自動レビュー

1. リポジトリにPRを作成
2. Botが自動的にレビューを実行（30秒〜2分程度）
3. レビュー結果がPRのコメントに投稿される

### レビュー内容

Claudeは以下の観点でコードをレビューします：

1. **コード品質**
   - コードの構造と整理
   - 命名規則と可読性
   - コードの重複
   - 複雑さと保守性

2. **ベストプラクティス**
   - デザインパターンの使用
   - SOLID原則
   - DRY (Don't Repeat Yourself)
   - エラーハンドリング

3. **セキュリティ**
   - 入力検証
   - SQLインジェクションリスク
   - XSS脆弱性
   - 認証/認可の問題

4. **パフォーマンス**
   - ボトルネックの可能性
   - データベースクエリの最適化
   - リソース使用

5. **テスト**
   - テストカバレッジ
   - エッジケース
   - エラーシナリオ

6. **ドキュメント**
   - コードコメント
   - APIドキュメント
   - READMEの更新

## Webhook URL

```
POST https://expense-manager.com/api/webhooks/github/pr
```

## ログとモニタリング

レビューの実行状況はLaravelのログで確認できます：

```bash
# レビュー処理のログを確認
docker-compose logs backend | grep "PR review"

# エラーログを確認
docker-compose logs backend | grep ERROR
```

### ログの種類

- **Starting PR review**: レビュー開始
- **Filtered PR data**: 機密情報フィルタリング完了
- **PR review completed successfully**: レビュー成功
- **Failed to fetch PR data**: GitHub APIからのデータ取得失敗
- **Failed to get review from Claude AI**: Claude APIエラー
- **Failed to post review to GitHub**: GitHubへの投稿失敗

## トラブルシューティング

### Webhookが動作しない

1. **署名検証エラー**
   ```bash
   # ログを確認
   docker-compose logs backend | grep webhook
   ```
   - `.env`の`GITHUB_WEBHOOK_SECRET`が正しいか確認
   - GitHubのWebhook設定でSecretが一致しているか確認

2. **接続エラー**
   - Webhook URLが正しいか確認
   - サーバーが外部からアクセス可能か確認
   - ファイアウォール設定を確認

3. **APIキーエラー**
   ```bash
   # エラーログを確認
   docker-compose logs backend | grep "API key"
   ```
   - `GITHUB_TOKEN`が有効か確認
   - `ANTHROPIC_API_KEY`が有効か確認
   - トークンのスコープが正しいか確認

### レビューが投稿されない

1. **GitHub権限不足**
   - Personal Access Tokenに`repo`スコープがあるか確認
   - リポジトリへのアクセス権限があるか確認

2. **Claude API制限**
   - Anthropic APIの利用制限を確認
   - レート制限に達していないか確認

### レビュー品質を改善

1. **PRの説明を充実させる**
   - 変更の目的と背景を記載
   - テスト方法を記載
   - 関連Issueをリンク

2. **コミットメッセージを明確に**
   - 何を変更したか明確に記載
   - なぜ変更したか理由を記載

## セキュリティに関する注意事項

### ✅ 安全な点

- 機密情報は自動的にマスキングされます
- PRの差分のみが共有されます（リポジトリ全体は共有されません）
- 秘密鍵ファイルは自動的に除外されます

### ⚠️ 注意が必要な点

1. **Claude AIへのデータ送信**
   - コードの差分はAnthropicのサーバーに送信されます
   - 機密性の高いプライベートコードの場合は、使用を慎重に検討してください

2. **APIキーの管理**
   - `.env`ファイルは**絶対にGitにコミットしないでください**
   - APIキーは定期的にローテーションすることを推奨します

3. **Webhook Secret**
   - 強力なランダム文字列を使用してください
   - 定期的に更新することを推奨します

## コスト

### Anthropic Claude AI

- 使用料金は送信したトークン数に基づきます
- Claude 3.5 Sonnet の料金:
  - Input: $3.00 / million tokens
  - Output: $15.00 / million tokens
- 平均的なPR（500行程度）: 約$0.05〜$0.15

### 推奨事項

- 大規模なPR（1000行以上）の場合は、レビュー前に分割することを検討
- テスト目的の小規模なPRで動作を確認してから本格利用

## カスタマイズ

### レビュー観点のカスタマイズ

`backend/app/Services/ClaudeService.php` の `buildReviewPrompt()` メソッドを編集：

```php
// プロンプトをカスタマイズして、特定の観点を強調できます
```

### フィルタリングルールの追加

`backend/app/Services/SensitiveDataFilter.php` の `$sensitivePatterns` 配列に追加：

```php
private $sensitivePatterns = [
    // カスタムパターンを追加
    '/your_custom_pattern/i',
];
```

## サポート

問題が発生した場合：

1. ログを確認: `docker-compose logs backend`
2. データベースを確認: `pr_reviews` テーブルの `error_message` カラム
3. GitHub Webhookの配信履歴を確認（Settings → Webhooks → Recent Deliveries）

## ライセンス

MIT
