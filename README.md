# 💰 Expense Manager

A full-stack expense management application built with Laravel (backend) and React (frontend).

## Features

- ✅ Add, edit, and delete expenses
- 📊 View total expenses
- 📅 Track expenses by date
- 🏷️ Categorize expenses
- 📝 Add descriptions to expenses
- 💵 Monitor spending with a clean, modern UI

## Tech Stack

### Backend
- **Laravel 12** - PHP framework for the REST API
- **SQLite** - Database (can be configured to use other databases)
- **Laravel Sanctum** - API authentication ready

### Frontend
- **React 18** - UI library
- **Vite** - Build tool and dev server
- **Axios** - HTTP client for API calls
- **CSS3** - Modern, responsive styling

## Project Structure

```
expense-manager/
├── backend/          # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── ExpenseController.php
│   │   └── Models/
│   │       └── Expense.php
│   ├── database/
│   │   └── migrations/
│   ├── routes/
│   │   └── api.php
│   └── ...
├── frontend/         # React application
│   ├── src/
│   │   ├── App.jsx
│   │   ├── App.css
│   │   └── ...
│   └── ...
└── README.md
```

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- npm or yarn

## Installation

### Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run database migrations:
```bash
php artisan migrate
```

6. Start the Laravel development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install Node dependencies:
```bash
npm install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Start the development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:5173`

## API Endpoints

### Expenses

- `GET /api/expenses` - Get all expenses
- `POST /api/expenses` - Create a new expense
- `GET /api/expenses/{id}` - Get a specific expense
- `PUT /api/expenses/{id}` - Update an expense
- `DELETE /api/expenses/{id}` - Delete an expense

### Request/Response Examples

#### Create Expense
```json
POST /api/expenses
{
  "title": "Groceries",
  "description": "Weekly shopping",
  "amount": 125.50,
  "date": "2025-12-19",
  "category": "Food"
}
```

#### Response
```json
{
  "id": 1,
  "title": "Groceries",
  "description": "Weekly shopping",
  "amount": "125.50",
  "date": "2025-12-19",
  "category": "Food",
  "created_at": "2025-12-19T10:00:00.000000Z",
  "updated_at": "2025-12-19T10:00:00.000000Z"
}
```

## Usage

1. Start the Laravel backend server (runs on port 8000)
2. Start the React frontend server (runs on port 5173)
3. Open your browser and navigate to `http://localhost:5173`
4. Start adding your expenses!

### Adding an Expense

1. Fill in the expense form with:
   - Title (required)
   - Amount (required)
   - Date (required)
   - Category (optional)
   - Description (optional)
2. Click "Add Expense"
3. The expense will appear in the list below

### Editing an Expense

1. Click the "Edit" button on any expense card
2. The form will populate with the expense data
3. Make your changes
4. Click "Update Expense"

### Deleting an Expense

1. Click the "Delete" button on any expense card
2. Confirm the deletion
3. The expense will be removed

## Development

### Backend Development

The Laravel backend uses:
- **Model**: `app/Models/Expense.php` - Defines the Expense model and database fields
- **Controller**: `app/Http/Controllers/ExpenseController.php` - Handles API requests
- **Migration**: `database/migrations/*_create_expenses_table.php` - Defines the database schema
- **Routes**: `routes/api.php` - Defines API endpoints

### Frontend Development

The React frontend is structured as:
- **Main Component**: `src/App.jsx` - Contains all the expense management logic
- **Styles**: `src/App.css` - Component-specific styles
- **API Integration**: Uses Axios to communicate with the Laravel backend

## Configuration

### Environment Variables

#### Backend (.env)
The Laravel backend uses SQLite by default. To use a different database, update the `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_manager
DB_USERNAME=root
DB_PASSWORD=
```

#### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
```

## Troubleshooting

### CORS Issues
If you encounter CORS errors, ensure the Laravel backend has CORS middleware enabled in `bootstrap/app.php`.

### Port Already in Use
- Laravel: Change the port with `php artisan serve --port=8001`
- React: Update the port in `vite.config.js` or use `npm run dev -- --port=5174`

### Database Issues
Run migrations again:
```bash
cd backend
php artisan migrate:fresh
```

## Future Enhancements

- [ ] User authentication
- [ ] Expense categories management
- [ ] Expense filtering and search
- [ ] Export expenses to CSV/PDF
- [ ] Charts and analytics
- [ ] Budget tracking
- [ ] Recurring expenses
- [ ] Multi-currency support

## License

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.