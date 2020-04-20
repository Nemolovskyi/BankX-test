@extends('layout.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h2>Account details</h2>
                <div class="card border-light mb-3" style="max-width: 100%;">
                    <div class="card-header"> <h3># {{ $account->account_number }}
                            <small class="ml-5">Total balance ${{ $account->balance + $interest}} (where ${{$interest}} is interest for all time) margin: {{ $account->margin }}%</small>
                        </h3>  <small>Created: {{ $account->started }}</small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Accounts</h5>

                            <div class="row">
                                <table class="table">
                                    <caption>List of transactions</caption>
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">type</th>
                                        <th scope="col">sum</th>
                                        <th scope="col">billed</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <th scope="row">{{$transaction->id}}</th>
                                        <td>{{$transaction->type}}</td>
                                        <td>{{$transaction->sum}}</td>
                                        <td>{{$transaction->billed}}</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                    </div>
                </div>
                {{ $transactions->links() }}
            </div>
            </div>
    </div>
@endsection