# 💰 The Vault

> A personal finance and ledger application built with **Laravel** and **PostgreSQL**.

**The Vault** provides a simple and organized way to track daily transactions, monitor income and expenses, manage accounts, and set savings goals—all in one place.

---

## 🗄️ Database Schema

The application uses the following PostgreSQL tables.

### 👤 Users & Access

| Table        | Description                                      |
| ------------ | ------------------------------------------------ |
| `users`      | Stores user accounts and authentication details. |
| `user_types` | Defines user roles and access permissions.       |

<details>
<summary><strong>users</strong></summary>

| Column              | Type           | Notes                        |
| ------------------- | -------------- | ---------------------------- |
| `id`                | `int`          | Primary Key                  |
| `name`              | `varchar(255)` | Required                     |
| `email`             | `varchar(255)` | Unique, lowercase, validated |
| `email_verified_at` | `timestamp`    | Nullable                     |
| `password`          | `varchar`      | Hashed                       |
| `user_type_id`      | `integer`      | User type reference          |
| `created_by`        | `integer`      | Creator                      |
| `updated_by`        | `integer`      | Last updater                 |
| `created_at`        | `timestamp`    |                              |
| `updated_at`        | `timestamp`    |                              |

</details>

<details>
<summary><strong>user_types</strong></summary>

| Column       | Type        |
| ------------ | ----------- |
| `id`         | `int`       |
| `user_type`  | `varchar`   |
| `access_to`  | `text`      |
| `created_at` | `timestamp` |
| `updated_at` | `timestamp` |

</details>

---

### 🏦 Accounts

| Table          | Description                          |
| -------------- | ------------------------------------ |
| `account_type` | Defines different account types.     |
| `accounts`     | Stores bank and account information. |

<details>
<summary><strong>account_type</strong></summary>

| Column         | Type      |
| -------------- | --------- |
| `id`           | `int`     |
| `account_type` | `varchar` |

</details>

<details>
<summary><strong>accounts</strong></summary>

| Column            | Type           | Notes                  |
| ----------------- | -------------- | ---------------------- |
| `id`              | `int`          | Primary Key            |
| `account_number`  | `varchar`      |                        |
| `account_type_id` | `int`          | Account type reference |
| `bank_name`       | `varchar(100)` |                        |
| `branch`          | `varchar(100)` |                        |
| `ifsc_code`       | `varchar(100)` |                        |
| `balance`         | `numeric`      |                        |
| `created_by`      | `int`          |                        |
| `updated_by`      | `int`          |                        |
| `created_at`      | `timestamp`    |                        |
| `updated_at`      | `timestamp`    |                        |

</details>

---

### 💸 Transactions

| Table                  | Description                        |
| ---------------------- | ---------------------------------- |
| `transaction_type`     | Defines transaction types.         |
| `transaction_method`   | Defines transaction methods.       |
| `transaction_category` | Defines transaction categories.    |
| `transactions`         | Stores all financial transactions. |

<details>
<summary><strong>transaction_type</strong></summary>

| Column             | Type      |
| ------------------ | --------- |
| `id`               | `int`     |
| `transaction_type` | `varchar` |

</details>

<details>
<summary><strong>transaction_method</strong></summary>

| Column               | Type      |
| -------------------- | --------- |
| `id`                 | `int`     |
| `transaction_method` | `varchar` |

</details>

<details>
<summary><strong>transaction_category</strong></summary>

| Column                 | Type      |
| ---------------------- | --------- |
| `id`                   | `int`     |
| `transaction_category` | `varchar` |

</details>

<details>
<summary><strong>transactions</strong></summary>

| Column                    | Type           | Notes                          |
| ------------------------- | -------------- | ------------------------------ |
| `id`                      | `int`          | Primary Key                    |
| `transaction_name`        | `varchar(50)`  | Required                       |
| `transaction_description` | `varchar(300)` | Required                       |
| `transaction_method_id`   | `int`          | FK → `transaction_method.id`   |
| `transaction_type_id`     | `int`          | FK → `transaction_type.id`     |
| `transaction_category_id` | `int`          | FK → `transaction_category.id` |
| `account_number_id`       | `int`          | Account reference              |
| `amount_paid`             | `numeric`      | Required, minimum `1`          |
| `account_balance`         | `numeric`      | Nullable                       |
| `cash_balance`            | `numeric`      |                                |
| `created_by`              | `int`          |                                |
| `updated_by`              | `int`          |                                |
| `created_at`              | `timestamp`    |                                |
| `updated_at`              | `timestamp`    |                                |

</details>

---

### 🎯 Savings Goals

| Table           | Description                             |
| --------------- | --------------------------------------- |
| `duration`      | Stores available savings durations.     |
| `savings_goals` | Stores user savings goals and progress. |

<details>
<summary><strong>duration</strong></summary>

| Column          | Type      |
| --------------- | --------- |
| `id`            | `bigint`  |
| `duration_name` | `varchar` |

</details>

<details>
<summary><strong>savings_goals</strong></summary>

| Column                  | Type           | Notes                                   |
| ----------------------- | -------------- | --------------------------------------- |
| `id`                    | `bigint`       | Primary Key                             |
| `saving_name`           | `varchar(100)` | Required                                |
| `savings_category`      | `varchar(255)` | Required                                |
| `description`           | `varchar(255)` | Required                                |
| `savings_for`           | `varchar(100)` | Required                                |
| `accounts_id`           | `bigint`       | FK → `accounts.id`, nullable            |
| `savings_cash`          | `numeric`      | Used for cash savings                   |
| `savings_amount`        | `integer`      | Required, minimum `1`                   |
| `duration`              | `bigint`       | FK → `duration.id`                      |
| `savings_method`        | `bigint`       | FK → `transaction_type.id`              |
| `total_account_savings` | `varchar`      | Nullable                                |
| `totol_cash_saving`     | `varchar`      | Nullable — typo intentionally preserved |
| `created_by`            | `bigint`       | FK → `users.id`, nullable               |
| `updated_by`            | `bigint`       | FK → `users.id`, nullable               |
| `created_at`            | `timestamp`    |                                         |
| `updated_at`            | `timestamp`    |                                         |

> ⚠️ **Important:** `total_account_savings` and `totol_cash_saving` are intentionally stored as `varchar`. The existing controller validates these values using a regular expression before casting them to numeric values.

</details>

---

### 📈 Income

The `income` table stores income records used by the dashboard to calculate income totals and sources.

| Column          | Type        | Notes                           |
| --------------- | ----------- | ------------------------------- |
| `id`            | `bigint`    | Primary Key                     |
| `income_name`   | `varchar`   |                                 |
| `income_amount` | `numeric`   |                                 |
| `income_from`   | `varchar`   | Used to identify income sources |
| `created_by`    | `int`       |                                 |
| `created_at`    | `timestamp` |                                 |
| `updated_at`    | `timestamp` |                                 |

> 💡 The dashboard checks for this table using Laravel's `Schema::hasTable()` and `Schema::hasColumn()`, so it is technically optional but recommended.

---

## 🔗 Key Relationships

```text
users
 └── user_types

accounts
 └── account_type

transactions
 ├── transaction_method
 ├── transaction_type
 ├── transaction_category
 └── accounts

savings_goals
 ├── accounts
 ├── duration
 ├── transaction_type
 └── users
```

---

* **Backend:** Laravel
* **Database:** PostgreSQL
* **Authentication:** Breeze
* **Purpose:** Personal Finance & Ledger Management

> 💰 **The Vault — Take control of your income, expenses, accounts, and savings in one organized place.**
