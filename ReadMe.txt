1. Optimized AngularJS Services - Auth, CRUD, Misc, Report
2. Optimized PHP Controllers - Base, CRUD, Misc (Misc is not enough)
3. Merged AngularJS controllers - Customer & Supplier, Balance sheet & Profit and Loss
4. Implemented View/Update the New Invoice and New Payment.
5. Implemented View/Update the New Expense.
6. Integrated overall functionalities except some validation, error handling and new pages.
7. Merged AngularJS controllers - New Invoice, New Payment and New expense. Fixed the custom dropdown and replaced the person dropdowns.

Previous versions from 201703211956 to 201703262035 had an error: always 200 ok issue because of newline mark in the public/index.php file. And this version has fixed it.

8. Divided the NewTransactionController into NewInvoice, NewPayment and NewExpense controllers again.
9. Fixed the New Invoice, New Payment and New Expense cotrollers. Update functions are still not working yet.
10. Added New Sales Receipt. Update functions are working now.
11. Implemented delete dialog of Invoice, Payment, Sales Receipt and Expenses.
12. Cleaned Misc controller. Used resource end points for invoice, payment, sales receipt and expense.