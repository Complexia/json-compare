# 🔍 JSON Comparison Tool

A sleek web application built with Laravel and Vue.js that helps you compare two JSON structures with ease.

## ✨ Features

- Side-by-side JSON comparison
- Visual difference highlighting
- Easy-to-use interface
- Real-time validation

## 🚀 Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Bun.js
- Node.js

### Installation

1. Clone the repository


2. Install PHP dependencies
```bash
composer install
```

3. Install JavaScript dependencies
```bash
bun install
```

4. Copy the environment file
```bash
cp .env.example .env
```

5. Generate application key
```bash
php artisan key:generate
```

## 🛠️ Running the Application

1. Start the Laravel development server
```bash
php artisan serve
```

2. In a new terminal, start the Vue development server
```bash
bun dev
```

3. Visit [http://localhost:5173](http://localhost:5173) in your browser

## 📝 How to Use

1. Open your browser and navigate to `http://localhost:5173`
2. Paste your first JSON structure in the left text box
3. Paste your second JSON structure in the right text box
4. The differences will be highlighted automatically!

## 🎯 Example JSONs

Try these sample JSONs to test the comparison:

```json
{
    "name": "John",
    "age": 30,
    "city": "New York"
}
```

```json
{
    "name": "John",
    "age": 31,
    "city": "Boston"
}
```

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---
Made with ❤️ using Laravel + Vue.js