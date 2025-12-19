# セットアップガイド - dev.expense-manager.com

## 1. hostsファイルの設定

ローカル環境で https://dev.expense-manager.com を使用するには、hostsファイルを編集します。

```bash
sudo nano /etc/hosts
```

以下の行を追加:
```
127.0.0.1 dev.expense-manager.com
```

保存して終了（Ctrl+X → Y → Enter）

## 2. SSL証明書の設定（開発用）

開発環境でHTTPSを使用する場合は、自己署名証明書を作成します。

### mkcertを使用する方法（推奨）

```bash
# mkcertのインストール（Homebrew）
brew install mkcert
brew install nss # Firefoxを使用する場合

# ローカルCAをインストール
mkcert -install

# 証明書を生成
cd /Users/aidma-766/Desktop/Prj-New/expense-manager
mkdir -p ssl
cd ssl
mkcert dev.expense-manager.com localhost 127.0.0.1 ::1
```

これで以下のファイルが生成されます:
- `dev.expense-manager.com+3.pem` (証明書)
- `dev.expense-manager.com+3-key.pem` (秘密鍵)

## 3. Nginxリバースプロキシの設定（オプション）

### nginx.confの作成

プロジェクトルートに `nginx.conf` を作成:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name dev.expense-manager.com;

    ssl_certificate /etc/nginx/ssl/dev.expense-manager.com+3.pem;
    ssl_certificate_key /etc/nginx/ssl/dev.expense-manager.com+3-key.pem;

    # フロントエンド
    location / {
        proxy_pass http://frontend:5173;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # バックエンドAPI
    location /api {
        proxy_pass http://backend:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### docker-compose.ymlにNginxを追加

```yaml
  nginx:
    image: nginx:alpine
    container_name: expense-manager-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - backend
      - frontend
    networks:
      - expense-network
```

## 4. 簡易セットアップ（HTTPのみ）

HTTPSが不要な場合は、hostsファイルだけ設定してHTTPでアクセス:

```bash
# hostsファイルに追加
127.0.0.1 dev.expense-manager.com
```

環境変数をHTTPに変更:

**backend/.env**
```
APP_URL=http://dev.expense-manager.com:8000
```

**frontend/.env**
```
VITE_API_URL=http://dev.expense-manager.com:8000/api
```

**docker-compose.yml**
```yaml
environment:
  - VITE_API_URL=http://dev.expense-manager.com:8000/api
```

## 5. 起動

```bash
# Dockerコンテナの起動
docker-compose up -d

# データベースマイグレーション
docker-compose exec backend php artisan migrate

# アクセス
# HTTPSの場合: https://dev.expense-manager.com
# HTTPの場合: http://dev.expense-manager.com:5173 (フロントエンド)
#            http://dev.expense-manager.com:8000/api (バックエンド)
```

## 6. トラブルシューティング

### ブラウザで「安全でない」と表示される

自己署名証明書を使用しているため、ブラウザが警告を表示します。
- Chrome/Edge: 「詳細設定」→「dev.expense-manager.comにアクセスする（安全ではありません）」
- Firefox: 「リスクを理解した上で続行」

### DNSが解決できない

```bash
# hostsファイルが正しく設定されているか確認
cat /etc/hosts | grep dev.expense-manager.com

# pingで確認
ping dev.expense-manager.com
```

### ポートが使用中

```bash
# 使用中のポートを確認
lsof -i :80
lsof -i :443
lsof -i :5173
lsof -i :8000

# プロセスを停止
kill -9 <PID>
```
