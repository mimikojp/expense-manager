import { useState } from 'react'
import ExpenseList from './components/ExpenseList'
import ExpenseForm from './components/ExpenseForm'
import './App.css'

function App() {
  const [refreshKey, setRefreshKey] = useState(0)

  const handleExpenseAdded = () => {
    setRefreshKey(prev => prev + 1)
  }

  return (
    <div className="App">
      <header>
        <h1>Expense Manager</h1>
      </header>
      <main>
        <ExpenseForm onSuccess={handleExpenseAdded} />
        <ExpenseList key={refreshKey} />
      </main>
    </div>
  )
}

export default App
