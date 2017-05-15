'use strict';

angular
    .module( 'bookkeeping' )
    .run( [
            '$rootScope', 
            '$state',
            '$stateParams',
            function ( $rootScope, $state, $stateParams ) {
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;
                $rootScope.$on( '$stateChangeSuccess', function () {
                    window.scrollTo( 0, 0 );
                } );
            },
        ]
    )
    .config(
        [
            '$stateProvider', 
            '$urlRouterProvider',
            function ( $stateProvider, $urlRouterProvider, $state ) {

                // For unmatched routes
                $urlRouterProvider.otherwise( '/signin' );

                // Application routes
                $stateProvider

                .state( 'app', {
                    abstract: true,
                    templateUrl: 'views/layouts/app.html',
                    resolve: {
                      deps: [
                        '$ocLazyLoad',
                        function( $ocLazyLoad ) {
                          return $ocLazyLoad.load( [
                            'css/customDropdown.css',
                            'robust-assets/css/plugins/ui/jquery-ui.min.css',
                            'robust-assets/css/plugins/extensions/toastr.css',
                            'robust-assets/css/plugins/forms/icheck/icheck.css',
                            'robust-assets/css/plugins/forms/selects/selectize.css',
                            'robust-assets/css/plugins/forms/selects/select2.min.css',
                            'robust-assets/css/plugins/forms/toggle/switchery.min.css',
                            'robust-assets/css/plugins/file-uploaders/dropzone.min.css',
                            'robust-assets/css/plugins/tables/datatable/buttons.dataTables.min.css',
                            'robust-assets/css/plugins/tables/datatable/dataTables.bootstrap4.min.css',
                            'robust-assets/css/plugins/tables/extensions/responsive.dataTables.min.css',

                            'app/services/Auth.service.js',
                            'app/services/CRUD.service.js',
                            'app/services/Misc.service.js',
                            'app/services/TRXN.service.js',
                            'app/services/Rprt.service.js',
                            'app/controllers/Auth.controller.js',
                            'app/directives/CountryList.directive.js',
                            'app/directives/DecimalPoint.directive.js',
                            'app/directives/AddNewDropdown.directive.js',
                            'app/directives/AccountDropdown.directive.js',
                            'app/directives/SejAccountDialog/SejAccountDialog.directive.js',
                            'app/directives/SejDeleteConfirmDialog/SejDeleteConfirmDialog.directive.js',
                            'app/directives/SejDeactivateConfirmDialog/SejDeactivateConfirmDialog.directive.js',
                            'app/directives/SejNewExpensesDropdown/SejNewExpensesDropdown.directive.js',
                            'app/directives/SejNewSalesDropdown/SejNewSalesDropdown.directive.js',
                            'app/directives/SejPersonDialog/SejPersonDialog.directive.js',
                            'app/directives/SejPersonTypeDialog/SejPersonTypeDialog.directive.js',
                            'app/directives/SejProductServiceDialog/SejProductServiceDialog.directive.js',
                            'app/directives/SejProductCategoryDialog/SejProductCategoryDialog.directive.js',
                            'app/directives/SejScreenPreLoader/SejScreenPreLoader.directive.js',
                            'app/directives/SejTrxnZeroWarningDialog/SejTrxnZeroWarningDialog.directive.js',

                            'js/transaction.js',
                            'robust-assets/js/plugins/ui/jquery.sticky.js',
                            'robust-assets/js/plugins/extensions/toastr.min.js',
                            'robust-assets/js/plugins/forms/icheck/icheck.min.js',
                            'robust-assets/js/plugins/extensions/dropzone.min.js',
                            'robust-assets/js/plugins/forms/toggle/switchery.min.js',
                            'robust-assets/js/plugins/tables/jquery.dataTables.min.js',
                            'robust-assets/js/plugins/forms/select/select2.full.min.js',
                            'robust-assets/js/core/libraries/jquery_ui/jquery-ui.min.js',
                            'robust-assets/js/plugins/forms/toggle/bootstrap-switch.min.js',
                            'robust-assets/js/plugins/forms/toggle/bootstrap-checkbox.min.js',
                            'robust-assets/js/plugins/forms/validation/jqBootstrapValidation.js',
                            'robust-assets/js/plugins/tables/datatable/dataTables.responsive.min.js',
                          ] ).then( function() {
                            Dropzone.autoDiscover = false;
                            return $ocLazyLoad.load( [
                              'robust-assets/js/components/extensions/toastr.js',
                              'robust-assets/js/components/tables/datatables/buttons.print.min.js',
                              'robust-assets/js/components/tables/datatables/buttons.html5.min.js',
                              'robust-assets/js/plugins/tables/datatable/dataTables.bootstrap4.min.js',
                              'robust-assets/js/components/tables/datatables/dataTables.buttons.min.js',
                            ] );
                          } );
                        }
                      ]
                    },
                    data: { contentClasses: 'horizontal-layout horizontal-menu 2-columns' }
                } )

                .state( 'app.main', {
                  templateUrl: 'views/layouts/main.html',
                  resolve: {
                    deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( [ 'css/style.css', 'app/controllers/Header.controller.js'] ); } ]
                  }
                } )

                .state( 'app.main.dashboard', {
                  url: '/dashboard',
                  templateUrl: 'views/pages/dashboard/dashboard.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/Dashboard.controller.js' ); } ] }
                } )

                .state( 'app.main.customers', {
                  url: '/customers',
                  templateUrl: 'views/pages/customers/customers.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/CustomerSupplier.controller.js' ); } ] }
                } )

                .state( 'app.main.suppliers', {
                  url: '/suppliers',
                  templateUrl: 'views/pages/suppliers/suppliers.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/CustomerSupplier.controller.js' ); } ] }
                } )



                .state( 'app.main.sales', {
                  templateUrl: 'views/pages/sales/main.html',
                } )

                .state( 'app.main.sales.main', {
                  url: '/sales',
                  templateUrl: 'views/pages/sales/sales.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/Sales.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.invoice', {
                  url: '/invoice?trxnId',
                  templateUrl:  'views/pages/sales/new-invoice.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewInvoice.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.payment', {
                  url: '/payment?trxnId',
                  templateUrl: 'views/pages/sales/new-payment.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewPayment.controller.js'); } ] }
                } )

                .state( 'app.main.sales.sales-receipt', {
                  url: '/sales-receipt?trxnId',
                  templateUrl: 'views/pages/sales/new-sales-receipt.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewSalesReceipt.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.credit-note', {
                  url: '/credit-note?trxnId',
                  templateUrl: 'views/pages/sales/new-credit-note.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewCreditNote.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.new-invoice', {
                  url: '/new-invoice',
                  templateUrl: 'views/pages/sales/new-invoice.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewInvoice.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.new-payment', {
                  url: '/new-payment?invoiceId',
                  templateUrl: 'views/pages/sales/new-payment.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewPayment.controller.js'); } ] }
                } )

                .state( 'app.main.sales.new-sales-receipt', {
                  url: '/new-sales-receipt',
                  templateUrl: 'views/pages/sales/new-sales-receipt.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewSalesReceipt.controller.js' ); } ] }
                } )

                .state( 'app.main.sales.new-credit-note', {
                  url: '/new-credit-note',
                  templateUrl: 'views/pages/sales/new-credit-note.html',
                  resolve: { deps: [ '$ocLazyLoad', function ( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewCreditNote.controller.js' ); } ] }
                } )



                .state( 'app.main.expense', {
                  templateUrl: 'views/pages/expense/main.html'
                } )

                .state( 'app.main.expense.main', {
                  url: '/expense',
                  templateUrl: 'views/pages/expense/expense.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/Expense.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.expense', {
                  url: '/expense?trxnId',
                  templateUrl: 'views/pages/expense/new-expense.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewExpense.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.bill', {
                  url: '/bill?trxnId',
                  templateUrl: 'views/pages/expense/new-bill.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewBill.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.bill-payment', {
                  url: '/bill-payment?trxnId',
                  templateUrl: 'views/pages/expense/new-bill-payment.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewBillPayment.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.supplier-credit', {
                  url: '/supplier-credit?trxnId',
                  templateUrl: 'views/pages/expense/new-supplier-credit.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewSupplierCredit.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.new-expense', {
                  url: '/new-expense',
                  templateUrl: 'views/pages/expense/new-expense.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewExpense.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.new-bill', {
                  url: '/new-bill',
                  templateUrl: 'views/pages/expense/new-bill.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewBill.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.new-bill-payment', {
                  url: '/new-bill-payment?expenseId',
                  templateUrl: 'views/pages/expense/new-bill-payment.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewBillPayment.controller.js' ); } ] }
                } )

                .state( 'app.main.expense.new-supplier-credit', {
                  url: '/new-supplier-credit',
                  templateUrl: 'views/pages/expense/new-supplier-credit.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewSupplierCredit.controller.js' ); } ] }
                } )



                .state( 'app.main.other', {
                  templateUrl: 'views/pages/other/main.html'
                } )

                .state( 'app.main.other.journal-entry', {
                  url: '/journal-entry',
                  templateUrl: 'views/pages/other/journal-entry.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/JournalEntry.controller.js' ); } ] }
                } )

                .state( 'app.main.other.new-journal-entry', {
                  url: '/new-journal-entry?trxnId',
                  templateUrl: 'views/pages/other/new-journal-entry.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/NewJournalEntry.controller.js' ); } ] }
                } )

                .state( 'app.main.other.account-history', {
                  url: '/account-history?accountId',
                  templateUrl: 'views/pages/other/account-history.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/AccountHistory.controller.js' ); } ] }
                } )

                .state( 'app.main.other.audit-log', {
                  url: '/audit-log?tableId,recordId',
                  templateUrl: 'views/pages/other/audit-log.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/AuditLog.controller.js' );  } ] }
                } )

                .state( 'app.main.other.audit-history', {
                  url: '/audit-history?trxnType,trxnId',
                  templateUrl: 'views/pages/other/audit-history.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/AuditHistory.controller.js' ); } ] }
                } )



                .state( 'app.main.reports', {
                  templateUrl: 'views/pages/reports/main.html'
                } )

                .state( 'app.main.reports.main', {
                  url: '/reports',
                  templateUrl: 'views/pages/reports/reports.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/Reports.controller.js' ); } ] }
                } )

                .state( 'app.main.reports.balance-sheet', {
                  url: '/balance-sheet',
                  templateUrl: 'views/pages/reports/balance-sheet.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/BalanceSheet.controller.js' ); } ] }
                } )

                .state( 'app.main.reports.company-snapshot', {
                  url: '/company-snapshot',
                  templateUrl: 'views/pages/reports/company-snapshot.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/CompanySnapshot.controller.js' ); } ] }
                } )

                .state( 'app.main.reports.expense-by-supplier-summary', {
                  url: '/expense-by-supplier-summary',
                  templateUrl: 'views/pages/reports/expense-by-supplier-summary.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/ExpenseBySupplierSummary.controller.js' ); } ] }
                } )

                .state( 'app.main.reports.profit-loss', {
                  url: '/profit-loss',
                  templateUrl: 'views/pages/reports/profit-loss.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/ProfitAndLoss.controller.js' ); } ] }
                } )



                .state( 'app.main.settings', {
                  templateUrl: 'views/pages/settings/main.html'
                } )

                .state( 'app.main.settings.chart-of-account', {
                  url: '/chart-of-account',
                  templateUrl: 'views/pages/settings/chart-of-account.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/ChartOfAccount.controller.js' ); } ] }
                } )

                .state( 'app.main.settings.company-profile', {
                  url: '/company-profile',
                  templateUrl: 'views/pages/settings/company-profile.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/CompanyProfile.controller.js' ); } ] }
                } )

                .state( 'app.main.settings.product-category', {
                  url: '/product-category',
                  templateUrl: 'views/pages/settings/product-category.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/ProductCategory.controller.js' ); } ] }
                } )

                .state( 'app.main.settings.product-service', {
                  url: '/product-service',
                  templateUrl: 'views/pages/settings/product-service.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/ProductService.controller.js' ); } ] }
                } )

                .state( 'app.main.settings.user-profile', {
                  url: '/user-profile',
                  templateUrl: 'views/pages/settings/user-profile.html',
                  resolve: { deps: [ '$ocLazyLoad', function( $ocLazyLoad ) { return $ocLazyLoad.load( 'app/controllers/UserProfile.controller.js' ); } ] }
                } )



                .state( 'app.user', {
                    templateUrl: 'views/layouts/user.html',
                } )

                .state( 'app.user.signin', {
                    url: '/signin',
                    templateUrl: 'views/pages/user/signin.html',
                    data: { contentClasses: 'horizontal-layout horizontal-menu 1-column bg-lighten-2 blank-page blank-page' }
                } )

                .state( 'app.user.agreement', {
                  url: '/agreement',
                  templateUrl: 'views/pages/user/agreement.html',
                  data: { contentClasses: 'horizontal-layout horizontal-menu 1-column bg-lighten-2 blank-page blank-page' }
                } )

                .state( 'app.user.recover-password', {
                  url: '/recover-password',
                  templateUrl: 'views/pages/user/recover-password.html',
                  data: { contentClasses: 'horizontal-layout horizontal-menu 1-column bg-lighten-2 blank-page blank-page' }
                } )

                .state( 'app.user.signup', {
                  url: '/signup',
                  templateUrl: 'views/pages/user/signup.html',
                  data: { contentClasses: 'horizontal-layout horizontal-menu 1-column bg-lighten-2 blank-page blank-page' }
                } );
            }
        ]
    )

    .config(
        [
            '$ocLazyLoadProvider', 
            function ( $ocLazyLoadProvider ) {
                $ocLazyLoadProvider.config(
                    {
                        debug: false,
                        events: false
                    }
                );
            }
        ]
    );