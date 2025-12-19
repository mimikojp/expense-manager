# Expense Manager

Laravel + React を使用した経費管理アプリケーション

## アプリケーションURL
https://dev.expense-manager.com/

## 技術スタック

### Backend
- Laravel 7.x
- PHP 7.4
- MySQL 8.0
- REST API

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
