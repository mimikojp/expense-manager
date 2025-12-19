import { useState, useEffect } from 'react'
import axios from 'axios'
import './App.css'

const API_URL = `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/expenses`

function App() {
  const [expenses, setExpenses] = useState([])
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    category: ''
  })
  const [editingId, setEditingId] = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchExpenses()
  }, [])

  const fetchExpenses = async () => {
    try {
      setLoading(true)
      const response = await axios.get(API_URL)
      setExpenses(response.data)
      setError(null)
    } catch (err) {
      setError('Failed to fetch expenses')
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      setLoading(true)
      if (editingId) {
        await axios.put(`${API_URL}/${editingId}`, formData)
      } else {
        await axios.post(API_URL, formData)
      }
      setFormData({
        title: '',
        description: '',
        amount: '',
        date: new Date().toISOString().split('T')[0],
        category: ''
      })
      setEditingId(null)
      fetchExpenses()
      setError(null)
    } catch (err) {
      setError('Failed to save expense')
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const handleEdit = (expense) => {
    setFormData({
      title: expense.title,
      description: expense.description || '',
      amount: expense.amount,
      date: expense.date,
      category: expense.category || ''
    })
    setEditingId(expense.id)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this expense?')) {
      try {
        setLoading(true)
        await axios.delete(`${API_URL}/${id}`)
        fetchExpenses()
        setError(null)
      } catch (err) {
        setError('Failed to delete expense')
        console.error(err)
      } finally {
        setLoading(false)
      }
    }
  }

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  const handleCancel = () => {
    setFormData({
      title: '',
      description: '',
      amount: '',
      date: new Date().toISOString().split('T')[0],
      category: ''
    })
    setEditingId(null)
  }

  const totalExpenses = expenses.reduce((sum, expense) => sum + parseFloat(expense.amount), 0)

  return (
    <div className="app">
      <div className="container">
        <h1>💰 Expense Manager</h1>
        
        {error && <div className="error">{error}</div>}
        
        <div className="summary">
          <h2>Total Expenses: ${totalExpenses.toFixed(2)}</h2>
        </div>

        <form onSubmit={handleSubmit} className="expense-form">
          <h2>{editingId ? 'Edit Expense' : 'Add New Expense'}</h2>
          
          <div className="form-group">
            <label htmlFor="title">Title *</label>
            <input
              type="text"
              id="title"
              name="title"
              value={formData.title}
              onChange={handleChange}
              required
              placeholder="e.g., Groceries"
            />
          </div>

          <div className="form-group">
            <label htmlFor="amount">Amount *</label>
            <input
              type="number"
              id="amount"
              name="amount"
              value={formData.amount}
              onChange={handleChange}
              required
              min="0"
              step="0.01"
              placeholder="0.00"
            />
          </div>

          <div className="form-group">
            <label htmlFor="date">Date *</label>
            <input
              type="date"
              id="date"
              name="date"
              value={formData.date}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="category">Category</label>
            <input
              type="text"
              id="category"
              name="category"
              value={formData.category}
              onChange={handleChange}
              placeholder="e.g., Food, Transport"
            />
          </div>

          <div className="form-group">
            <label htmlFor="description">Description</label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows="3"
              placeholder="Optional notes..."
            />
          </div>

          <div className="form-actions">
            <button type="submit" disabled={loading}>
              {loading ? 'Saving...' : editingId ? 'Update Expense' : 'Add Expense'}
            </button>
            {editingId && (
              <button type="button" onClick={handleCancel} className="cancel-btn">
                Cancel
              </button>
            )}
          </div>
        </form>

        <div className="expenses-list">
          <h2>Expenses</h2>
          {loading && !expenses.length ? (
            <p>Loading...</p>
          ) : expenses.length === 0 ? (
            <p className="no-data">No expenses yet. Add your first expense above!</p>
          ) : (
            <div className="expenses-grid">
              {expenses.map((expense) => (
                <div key={expense.id} className="expense-card">
                  <div className="expense-header">
                    <h3>{expense.title}</h3>
                    <span className="expense-amount">${parseFloat(expense.amount).toFixed(2)}</span>
                  </div>
                  <div className="expense-details">
                    <p className="expense-date">📅 {expense.date}</p>
                    {expense.category && (
                      <p className="expense-category">🏷️ {expense.category}</p>
                    )}
                    {expense.description && (
                      <p className="expense-description">{expense.description}</p>
                    )}
                  </div>
                  <div className="expense-actions">
                    <button onClick={() => handleEdit(expense)} className="edit-btn">
                      Edit
                    </button>
                    <button onClick={() => handleDelete(expense.id)} className="delete-btn">
                      Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default App
