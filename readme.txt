1. users

Columns

id			int 	
name			varchar(255)	required
email	                varchar(255) unique	lowercase, validated
email_verified_at	timestamp without timezone
password		varchar	hashed
user_type_id		integer
created_by		integer	
updated_by		integer
created_at		timestamp without timezone
updated_at		timestamp without timezone

2. user_types

Columns

id		int 	
user_type	character varying
access_to	text	
created_at	timestamp without timezone
updated_at	timestamp without timezone

3. account_type

id	int 
account_type	character varying


4. accounts

id		int 
account_number	character varying
account_type_id	int 	
bank_name	varchar(100)	
branch		varchar(100)	
ifsc_code	varchar(100)	
balance		numeric
created_by	int	
updated_by	int 
created_at 	timestamp without timezone
updated_at	timestamp without timezone


5. transaction_type
Column	
id			int PK	
transaction_type	character varying

7. transaction_method
Column	
id			int 	
transaction_method	character varying	

8. transaction_category
Column
id			int 
transaction_category	character varying

9. transactions

id			int	
transaction_name	varchar(50)	required
transaction_description	varchar(300)	required
transaction_method_id	int FK → transaction_method.id	required
transaction_type_id	int FK → transaction_type.id	required
transaction_category_id	int FK → transaction_category.id	required
account_number_id	int
amount_paid		numeric	required, min 1
account_balance		numeric nullable	
cash_balance		numeric 
created_by		int 
updated_by		int 
created_at 		timestamp without timezone
updated_at		timestamp without timezone
	
10. duration
Column	Type
id	bigint PK
duration_name	varchar
11. savings_goals

⚠️ Important quirk: total_account_savings and totol_cash_saving (yes, typo'd column name — keep it as-is unless you also update the controller) are queried with a regex check (~ '^[0-9]+(\.[0-9]+)?$') before casting, which strongly implies these are text/varchar columns, not numeric — despite storing numbers. Recommend keeping them as varchar to match existing code exactly, unless you want to refactor the controller too.

Column	Type	Notes
id	bigint PK	
saving_name	varchar(100)	required
savings_category	varchar(255)	required
description	varchar(255)	required
savings_for	varchar(100)	required
accounts_id	bigint FK → accounts.id, nullable	null when method is cash
savings_cash	numeric nullable	set when method is cash
savings_amount	integer	required, min 1
duration	bigint FK → duration.id	required
savings_method	bigint FK → transaction_type.id	required (despite the name, it points at transaction_type)
total_account_savings	varchar nullable	see note above
totol_cash_saving	varchar nullable	see note above (typo preserved intentionally)
created_by	bigint FK → users.id, nullable	
updated_by	bigint FK → users.id, nullable	
created_at / updated_at	timestamp	
12. income

Referenced only from DashboardController, always guarded with Schema::hasTable() / Schema::hasColumn() checks — so it's optional, but clearly expected to exist for the dashboard income figures to populate.

id	bigint PK	
income_name	varchar	
income_amount	numeric	
income_from	varchar	used with distinct()->count() for "income sources this month"
created_by	int 
created_at and updated_at	timestamp without timezone	
