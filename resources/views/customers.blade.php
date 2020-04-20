@extends('layout.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h2>Costumers</h2>
                @foreach($customers as $costumer)
                <div class="card border-light mb-3" style="max-width: 100%;">
                    <div class="card-header"> <h3>{{ $costumer->name }} {{ $costumer->lastname }} <small class="ml-5">{{ $costumer->gender }} age:  {{ $costumer->age }}</small></h3>  <small>Identification number: {{ $costumer->identification_number }}</small> </div>
                    <div class="card-body">
                        <h5 class="card-title">Accounts</h5>
                        @foreach($costumer->accounts as $account)
                            <div class="row">
                                <div class="col-4">
                                    <a class="card-link" href="/customers/{{$account->id}}" >Acc:  <span class="h3">{{$account->account_number}}</span> </a>
                                </div>
                                <div class="col-8"> Bal: <span class="h3">${{$account->balance + $account->interest}}</span> including interest ${{ $account->interest }} </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                {{ $customers->links() }}
            </div>
            </div>
    </div>
@endsection