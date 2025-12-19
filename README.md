# Expense Manager

Laravel + React を使用した経費管理アプリケーション + Claude AI PR Review Bot

## アプリケーションURL
https://dev.expense-manager.com/

## 主な機能

### 経費管理
- 経費の登録・編集・削除
- カテゴリ管理
- REST API

### 🤖 Claude AI PR Review Bot
- **自動コードレビュー**: PRが作成/更新されると自動的にレビュー
- **セキュリティ重視**: 機密情報を自動マスキング
- **包括的な分析**: コード品質、セキュリティ、パフォーマンスなどを評価
- **詳細**: [PR_BOT_SETUP.md](PR_BOT_SETUP.md) を参照

### 🚀 PR作成コマンド
- **コマンドラインからPR作成**: `pr:create` コマンドでGitHub PRを作成
- **自動説明文生成**: コミット履歴から自動的にPR説明を生成
- **ドラフトPR対応**: 作業中の変更をドラフトとして共有
- **詳細**: [PR_COMMAND_USAGE.md](PR_COMMAND_USAGE.md) を参照

## 技術スタック

### Backend
- Laravel 7.x
- PHP 7.4
- MySQL 8.0
- REST API
- Claude AI API (Anthropic)
- GitHub API

### Frontend
- React
- Vite
- Axios

## プロジェクト構造

```
expense-manager/
├── backend/           # Laravel API
├── frontend/          # React フロントエンド
├── docker-compose.yml # Docker設定
└── README.md
```

## 環境構築

### 前提条件
- Docker
- Docker Compose

### セットアップ手順

1. **リポジトリのクローン**
```bash
cd expense-manager
```

2. **環境変数の設定**

Backend (.env は既に設定済み):
```
APP_NAME="Expense Manager"
APP_URL=https://dev.expense-manager.com
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=expense_manager
DB_USERNAME=expense_user
DB_PASSWORD=expense_password
```

Frontend (.env は既に設定済み):
```
VITE_API_URL=https://dev.expense-manager.com/api
```

3. **Dockerコンテナの起動**
```bash
docker-compose up -d
```

4. **データベースマイグレーションの実行**
```bash
docker-compose exec backend php artisan migrate
```

5. **アプリケーションへのアクセス**
- Application URL: https://dev.expense-manager.com/
- Frontend (direct): http://localhost:5173
- Backend API (direct): http://localhost:8000/api

## API エンドポイント

### Expenses
- `GET /api/expenses` - 全ての経費を取得
- `POST /api/expenses` - 新しい経費を作成
- `GET /api/expenses/{id}` - 特定の経費を取得
- `PUT /api/expenses/{id}` - 経費を更新
- `DELETE /api/expenses/{id}` - 経費を削除

### Categories
- `GET /api/categories` - 全てのカテゴリを取得
- `POST /api/categories` - 新しいカテゴリを作成
- `GET /api/categories/{id}` - 特定のカテゴリを取得
- `PUT /api/categories/{id}` - カテゴリを更新
- `DELETE /api/categories/{id}` - カテゴリを削除

### Webhooks
- `POST /api/webhooks/github/pr` - GitHub PR Webhook (PR Review Bot用)

## コマンド

### PR作成コマンド

#### Claude Code スラッシュコマンド (最も簡単)
Claude Code内で直接使用:

```
/create-pr
/create-pr --title="Add new feature"
/create-pr --base=develop
/create-pr --draft
```

**特徴:**
- ✅ Claude Code内でスラッシュコマンドとして使用可能
- ✅ 最短コマンド (docker-compose不要)
- ✅ タイトル自動生成（ブランチ名から）
- ✅ 説明文自動生成（コミット履歴から）

#### ターミナルコマンド (シンプル版)
現在のブランチから即座にPRを作成:

```bash
# 最もシンプルな使い方（タイトルはブランチ名から自動生成）
docker-compose exec backend php artisan pr

# タイトルを指定
docker-compose exec backend php artisan pr --title="Add new feature"

# ベースブランチを指定
docker-compose exec backend php artisan pr --base=develop

# ドラフトPR
docker-compose exec backend php artisan pr --draft
```

**特徴:**
- ✅ タイトル自動生成（ブランチ名から）
- ✅ 説明文自動生成（コミット履歴から）
- ✅ 現在のブランチを自動検出
- ✅ オプション最小限で使いやすい

詳細は [PR_QUICK_COMMAND.md](PR_QUICK_COMMAND.md) を参照

## 開発

### Backend (Laravel)
```bash
# コンテナに入る
docker-compose exec backend bash

# Artisanコマンド
php artisan migrate
php artisan make:model ModelName
php artisan make:controller ControllerName
```

### Frontend (React)
```bash
# コンテナに入る
docker-compose exec frontend sh

# パッケージのインストール
npm install

# 開発サーバーの起動
npm run dev
```

## データベーススキーマ

### expenses テーブル
- id
- user_id (外部キー)
- category_id (外部キー, nullable)
- title
- description (nullable)
- amount (decimal)
- expense_date (date)
- created_at
- updated_at

### categories テーブル
- id
- name
- color (デフォルト: #000000)
- created_at
- updated_at

## トラブルシューティング

### コンテナが起動しない場合
```bash
docker-compose down
docker-compose up -d --build
```

### データベース接続エラー
```bash
# データベースコンテナのログを確認
docker-compose logs db
```

### マイグレーションエラー
```bash
# マイグレーションをリセット
docker-compose exec backend php artisan migrate:fresh
```

## ライセンス
MIT
