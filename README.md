# 💰 The Vault

<div align="center">

### Your Personal Finance & Ledger Companion

**Track your income, manage expenses, monitor accounts, and work toward your savings goals — all in one place.**

Built with **Laravel · PHP · PostgreSQL · Blade · JavaScript**

</div>

---

## 📖 About The Project

**The Vault** is a personal finance and ledger management application designed to give users a clear and organized view of their financial activity.

Managing personal finances can become difficult when income, expenses, bank accounts, cash, and savings goals are tracked separately. **The Vault brings these financial activities together into one centralized application.**

With The Vault, users can record daily transactions, categorize spending, track different payment methods, monitor account balances, manage income sources, and create savings goals.

The goal of the project is simple:

> **Help users better understand where their money comes from, where it goes, and how their financial position changes over time.**

---

## ✨ Key Features

* 💸 **Transaction Management** — Record and organize daily income and expenses.
* 🏦 **Account Management** — Manage bank accounts and track account balances.
* 💵 **Cash Tracking** — Keep track of cash transactions separately.
* 📊 **Income Monitoring** — Record income and monitor different income sources.
* 🗂️ **Transaction Categories** — Organize transactions using types, methods, and categories.
* 🎯 **Savings Goals** — Create and track personal financial goals.
* ⏳ **Savings Duration** — Define how long you plan to save toward a goal.
* 📈 **Financial Overview** — Get a clearer picture of your overall financial activity.
* 👤 **User Management** — Support users and different access levels.

---

## 🛠️ Technology Stack

| Technology       | Purpose                  |
| ---------------- | ------------------------ |
| **Laravel**      | Backend Framework        |
| **PHP**          | Server-side Development  |
| **PostgreSQL**   | Database                 |
| **Blade**        | Frontend Templating      |
| **JavaScript**   | Client-side Interactions |
| **CSS**          | Styling                  |
| **Eloquent ORM** | Database Management      |

---

# 🗄️ Database Design

The Vault uses PostgreSQL to organize users, accounts, transactions, income, and savings goals.

## 👤 Users & Access

### `users`

| Column              | Type           | Description                  |
| ------------------- | -------------- | ---------------------------- |
| `id`                | `int`          | 🔑 Primary Key               |
| `name`              | `varchar(255)` | Required                     |
| `email`             | `varchar(255)` | Unique, lowercase, validated |
| `email_verified_at` | `timestamp`    | Nullable                     |
| `password`          | `varchar`      | Hashed                       |
| `user_type_id`      | `integer`      | User type reference          |
| `created_by`        | `integer`      | Creator                      |
| `updated_by`        | `integer`      | Last updater                 |
| `created_at`        | `timestamp`    | Record creation time         |
| `updated_at`        | `timestamp`    | Last update time             |

### `user_types`

| Column       | Type        | Description          |
| ------------ | ----------- | -------------------- |
| `id`         | `int`       | 🔑 Primary Key       |
| `user_type`  | `varchar`   | User role/type       |
| `access_to`  | `text`      | Access permissions   |
| `created_at` | `timestamp` | Record creation time |
| `updated_at` | `timestamp` | Last update time     |

---

## 🏦 Account Management

### `account_type`

| Column         | Type      | Description     |
| -------------- | --------- | --------------- |
| `id`           | `int`     | 🔑 Primary Key  |
| `account_type` | `varchar` | Type of account |

### `accounts`

| Column            | Type           | Description            |
| ----------------- | -------------- | ---------------------- |
| `id`              | `int`          | 🔑 Primary Key         |
| `account_number`  | `varchar`      | Account number         |
| `account_type_id` | `int`          | Account type reference |
| `bank_name`       | `varchar(100)` | Bank name              |
| `branch`          | `varchar(100)` | Branch name            |
| `ifsc_code`       | `varchar(100)` | IFSC code              |
| `balance`         | `numeric`      | Current balance        |
| `created_by`      | `int`          | Creator                |
| `updated_by`      | `int`          | Last updater           |
| `created_at`      | `timestamp`    | Record creation time   |
| `updated_at`      | `timestamp`    | Last update time       |

---

## 💸 Transaction Management

### `transaction_type`

| Column             | Type      |
| ------------------ | --------- |
| `id`               | `int`     |
| `transaction_type` | `varchar` |

### `transaction_method`

| Column               | Type      |
| -------------------- | --------- |
| `id`                 | `int`     |
| `transaction_method` | `varchar` |

### `transaction_category`

| Column                 | Type      |
| ---------------------- | --------- |
| `id`                   | `int`     |
| `transaction_category` | `varchar` |

### `transactions`

| Column                    | Type           | Description                    |
| ------------------------- | -------------- | ------------------------------ |
| `id`                      | `int`          | 🔑 Primary Key                 |
| `transaction_name`        | `varchar(50)`  | Required                       |
| `transaction_description` | `varchar(300)` | Required                       |
| `transaction_method_id`   | `int`          | FK → `transaction_method.id`   |
| `transaction_type_id`     | `int`          | FK → `transaction_type.id`     |
| `transaction_category_id` | `int`          | FK → `transaction_category.id` |
| `account_number_id`       | `int`          | Account reference              |
| `amount_paid`             | `numeric`      | Required, minimum `1`          |
| `account_balance`         | `numeric`      | Nullable                       |
| `cash_balance`            | `numeric`      | Cash balance                   |
| `created_by`              | `int`          | Creator                        |
| `updated_by`              | `int`          | Last updater                   |
| `created_at`              | `timestamp`    | Record creation time           |
| `updated_at`              | `timestamp`    | Last update time               |

---

## 🎯 Savings Goals

### `duration`

| Column          | Type      |
| --------------- | --------- |
| `id`            | `bigint`  |
| `duration_name` | `varchar` |

### `savings_goals`

| Column                  | Type           | Description                  |
| ----------------------- | -------------- | ---------------------------- |
| `id`                    | `bigint`       | 🔑 Primary Key               |
| `saving_name`           | `varchar(100)` | Required                     |
| `savings_category`      | `varchar(255)` | Required                     |
| `description`           | `varchar(255)` | Required                     |
| `savings_for`           | `varchar(100)` | Required                     |
| `accounts_id`           | `bigint`       | FK → `accounts.id`, nullable |
| `savings_cash`          | `numeric`      | Used for cash savings        |
| `savings_amount`        | `integer`      | Required, minimum `1`        |
| `duration`              | `bigint`       | FK → `duration.id`           |
| `savings_method`        | `bigint`       | FK → `transaction_type.id`   |
| `total_account_savings` | `varchar`      | Nullable                     |
| `totol_cash_saving`     | `varchar`      | Nullable — typo preserved    |
| `created_by`            | `bigint`       | FK → `users.id`              |
| `updated_by`            | `bigint`       | FK → `users.id`              |
| `created_at`            | `timestamp`    | Record creation time         |
| `updated_at`            | `timestamp`    | Last update time             |

> ⚠️ **Note:** `total_account_savings` and `totol_cash_saving` are intentionally stored as `varchar` because the existing controller performs regex validation before converting their values to numbers. The `totol_cash_saving` spelling is intentionally preserved for compatibility with the current code.

---

## 📈 Income Tracking

### `income`

| Column          | Type        | Description          |
| --------------- | ----------- | -------------------- |
| `id`            | `bigint`    | 🔑 Primary Key       |
| `income_name`   | `varchar`   | Name of income       |
| `income_amount` | `numeric`   | Income amount        |
| `income_from`   | `varchar`   | Income source        |
| `created_by`    | `int`       | Creator              |
| `created_at`    | `timestamp` | Record creation time |
| `updated_at`    | `timestamp` | Last update time     |

---

## 🔗 Database Relationships

```text
users
├── belongs to → user_types
└── tracks → created_by / updated_by

accounts
└── belongs to → account_type

transactions
├── belongs to → transaction_method
├── belongs to → transaction_type
├── belongs to → transaction_category
└── references → accounts

savings_goals
├── belongs to → accounts
├── belongs to → duration
├── references → transaction_type
└── references → users
```

---

## 🎯 Project Vision

The Vault is more than just an expense tracker. The project aims to provide a **centralized personal financial management system** where users can understand their financial habits and make more informed decisions.

Future improvements may include:

* 📊 Advanced financial analytics
* 📅 Monthly and yearly reports
* 🔔 Budget and spending alerts
* 📈 Interactive charts and dashboards
* 🔐 Enhanced user roles and permissions
* 📤 Exportable financial reports
* 📱 Improved responsive experience

---

<div align="center">

### 💰 Take control of your money with **The Vault**

**Track • Analyze • Save • Grow**

</div>
