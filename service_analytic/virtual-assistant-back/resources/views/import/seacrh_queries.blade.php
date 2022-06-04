@extends('layouts.app')

@section('content')

    <h2>{{ __('import.search_queries') }}</h2>
    <hr>

    @include('layouts.import.errors')

    {!! Form::open(['url' => route('loadSearchQueries'), 'method' => 'post', 'files' => true]) !!}

        <div class="form-group">
            {{ Form::label('parserOutCsvUrl', __('import.csv_file_link'), ['class' => 'control-label']) }}
            {{ Form::text('parserOutCsvUrl', 'http://op.getrealprice.com/media/ozon_seller_analytic_results.csv', ['class' => 'form-control']) }}
        </div>

        <div class="form-group">
            {{ Form::checkbox('containsTitles', 1, true, ['class' => '']) }}
            {{ Form::label('containsTitles', __('import.contains_titles'), ['class' => 'form-check-label']) }}
        </div>

        {{ Form::submit(__('import.load'), ['class' => 'btn btn-primary']) }}

    {!! Form::close() !!}

@endsection
