<?php

namespace App\Http\Controllers\TRXN;

use Illuminate\Http\Request;
use App\Http\Controllers\TRXN\JournalEntryController;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;

use App\Helper\RestResponseMessages;

class JournalEntryController extends TRXNController
{
    protected $debitNormalAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Cost of Sales/Services', 'Non-Operating Expenses and Losses' ];
    protected $creditNormalAccounts = [ 'Current Liability', 'Non-Current Liability', 'Owner\'s Equity', 'Operating Revenue', 'Non-Operating Revenues and Gains' ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request )
    {
        $journalEntry = $GLOBALS[ 'input' ][ 'transaction' ];
        $journalEntryItems = array_filter( $GLOBALS[ 'input' ][ 'journalEntryItems' ] );

        $journalEntry = JournalEntry::create( $journalEntry );
        foreach ( $journalEntryItems as $journalEntryItem ) {
            if ( isset( $journalEntryItem[ 'account' ] ) ) {
                $journalEntryItem[ 'account_id' ] = $this->account[ $journalEntryItem[ 'account' ] ]->id;
                $journalEntryItem[ 'journal_entry_id' ] = $journalEntry->id;

                if ( isset( $journalEntryItem[ 'person' ] ) ) {
                    $journalEntryItem[ 'person_id' ] = $this->payee[ $journalEntryItem[ 'person' ] ]->id;
                    $journalEntryItem[ 'person_type'] = $this->payee[ $journalEntryItem[ 'person' ] ]->type;
                }
                JournalEntryItem::create( $journalEntryItem );

                $accountCategoryName = Account::join( 'account_category_type', 'account.account_category_type_id', '=', 'account_category_type.id' )->where( 'account.id', $journalEntryItem[ 'account_id' ] )->select( 'account_category_type.name' )->first()->name;

                $account = Account::find( $journalEntryItem[ 'account_id' ] );
                if ( in_array( $accountCategoryName, $this->debitNormalAccounts ) ) {
                    $newBalance = $account->balance + $journalEntryItem[ 'debits' ] - $journalEntryItem[ 'credits' ];
                } else if ( in_array( $accountCategoryName, $this->creditNormalAccounts ) ) {
                    $newBalance = $account->balance - $journalEntryItem[ 'debits' ] + $journalEntryItem[ 'credits' ];
                }
                $account->update( [ 'balance' => $newBalance ] );
            }
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Journal Entry' ], 
                'record_id' => $journalEntry[ 'id' ],
                'trxn_id' => $journalEntry[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Added ',
                'target_name' => 'Journal Entry',
                'date' => $journalEntry[ 'date' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Create Journal Entry', $journalEntry, 200 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $journalEntry = JournalEntry::find( $id );
        $journalEntryItems = JournalEntryItem::where( 'journal_entry_id', $id )->get();

        foreach ( $journalEntryItems as $journalEntryItem ) {
            if ( $journalEntryItem->person_type == $this->personTypes[ 'Customer' ] )
                $journalEntryItem[ 'person' ] = Customer::find( $journalEntryItem->person_id )->name;
            else if ( $journalEntryItem->person_type == $this->personTypes[ 'Supplier' ] )
                $journalEntryItem[ 'person' ] = Supplier::find( $journalEntryItem->person_id )->name;
        }

        return RestResponseMessages::TRXNSuccessMessage( 'Retrieve Journal Entry', [ 'transaction' => $journalEntry,  'journalEntryItems' => $journalEntryItems ], 200 );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $journalEntry = $GLOBALS[ 'input' ][ 'transaction' ];
        $journalEntryItems = array_filter( $GLOBALS[ 'input' ][ 'journalEntryItems' ] );

        $this->debitNormalAccounts = [ 'Current Asset', 'Non-Current Asset', 'Operating Expense', 'Cost of Sales/Services', 'Non-Operating Expenses and Losses' ];
        $creditNormalAccounts = [ 'Current Liability', 'Non-Current Liability', 'Owner\'s Equity', 'Operating Revenue', 'Non-Operating Revenues and Gains' ];

        JournalEntry::find( $journalEntry[ 'id' ] )->update( $journalEntry );
        
        foreach ( $journalEntryItems as $journalEntryItem ) {
            if ( isset( $journalEntryItem[ 'account' ] ) ) {
                $journalEntryItem[ 'account_id' ] = $this->account[ $journalEntryItem[ 'account' ] ]->id;
                $journalEntryItem[ 'journal_entry_id' ] = $journalEntry[ 'id' ];

                $accountCategoryName = Account::join( 'account_category_type', 'account.account_category_type_id', '=', 'account_category_type.id' )->where( 'account.id', $journalEntryItem[ 'account_id' ] )->select( 'account_category_type.name' )->first()->name;

                $account = Account::find( $journalEntryItem[ 'account_id' ] );
                $orgJournalEntryItem = JournalEntryItem::find( $journalEntryItem[ 'id' ] );
                if ( in_array( $accountCategoryName, $this->debitNormalAccounts ) ) {
                    $newBalance = $account->balance + $journalEntryItem[ 'debits' ] - $journalEntryItem[ 'credits' ] - $orgJournalEntryItem[ 'debits' ] + $orgJournalEntryItem[ 'credits' ];
                } else if ( in_array( $accountCategoryName, $this->creditNormalAccounts ) ) {
                    $newBalance = $account->balance - $journalEntryItem[ 'debits' ] + $journalEntryItem[ 'credits' ] + $orgJournalEntryItem[ 'debits' ] - $orgJournalEntryItem[ 'credits' ];
                }
                $account->update( [ 'balance' => $newBalance ] );

                $orgJournalEntryItem->update( $journalEntryItem );
            }
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Journal Entry' ], 
                'record_id' => $journalEntry[ 'id' ],
                'trxn_id' => $journalEntry[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Edited ',
                'target_name' => 'Journal Entry',
                'date' => $journalEntry[ 'date' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Update Journal Entry', $journalEntry, 200 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id )
    {
        $journalEntry = JournalEntry::find( $id );
        $journalEntryItems = JournalEntryItem::where( 'journal_entry_id', $journalEntry->id )->get();

        $journalEntry->update( [ 'is_trash' => 1 ] );

        foreach( $journalEntryItems as $journalEntryItem ) {
            $accountCategoryName = Account::join( 'account_category_type', 'account.account_category_type_id', '=', 'account_category_type.id' )->where( 'account.id', $journalEntryItem[ 'account_id' ] )->select( 'account_category_type.name' )->first()->name;
            $account = Account::find( $journalEntryItem[ 'account_id' ] );

            $orgJournalEntryItem = JournalEntryItem::find( $journalEntryItem[ 'id' ] );
            if ( in_array( $accountCategoryName, $this->debitNormalAccounts ) ) {
                $newBalance = $account->balance - $journalEntryItem[ 'debits' ] + $journalEntryItem[ 'credits' ];
            } else if ( in_array( $accountCategoryName, $this->creditNormalAccounts ) ) {
                $newBalance = $account->balance + $journalEntryItem[ 'debits' ] - $journalEntryItem[ 'credits' ];
            }
            $account->update( [ 'balance' => $newBalance ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Journal Entry' ], 
                'record_id' => $journalEntry[ 'id' ],
                'trxn_id' => $journalEntry[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Deleted ',
                'target_name' => 'Journal Entry',
                'date' => $journalEntry[ 'date' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Delete Journal Entry', $journalEntry, 200 );
    }

    public function recoverDelete( Request $request, $id ) {
        $journalEntry = JournalEntry::find( $id );
        $journalEntryItems = JournalEntryItem::where( 'journal_entry_id', $journalEntry->id )->get();

        $journalEntry->update( [ 'is_trash' => 0 ] );

        foreach( $journalEntryItems as $journalEntryItem ) {
            $accountCategoryName = Account::join( 'account_category_type', 'account.account_category_type_id', '=', 'account_category_type.id' )->where( 'account.id', $journalEntryItem[ 'account_id' ] )->select( 'account_category_type.name' )->first()->name;
            $account = Account::find( $journalEntryItem[ 'account_id' ] );

            if ( in_array( $accountCategoryName, $this->debitNormalAccounts ) ) {
                $newBalance = $account->balance + $journalEntryItem[ 'debits' ] - $journalEntryItem[ 'credits' ];
            } else if ( in_array( $accountCategoryName, $this->creditNormalAccounts ) ) {
                $newBalance = $account->balance - $journalEntryItem[ 'debits' ] + $journalEntryItem[ 'credits' ];
            }
            $account->update( [ 'balance' => $newBalance ] );
        }

        $this->createAuditLog(
            [
                'table_id' => $this->tableIdsForAudit[ 'Journal Entry' ], 
                'record_id' => $journalEntry[ 'id' ],
                'trxn_id' => $journalEntry[ 'id' ],
                'date_changed' => date( 'Y-m-d H:i:s' ), 
                'user_email' => \Auth::user()->email, 
                'event_text' => 'Recovered ',
                'target_name' => 'Journal Entry',
                'date' => $journalEntry[ 'date' ]
            ] 
        );

        return RestResponseMessages::TRXNSuccessMessage( 'Recover Journal Entry', $journalEntry, 200 );
    }
}
