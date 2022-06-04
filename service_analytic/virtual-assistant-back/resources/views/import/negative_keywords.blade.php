@extends('layouts.app')

@section('title', __('import.negative_keywords'))

@section('content')

    <h2>{{ __('import.negative_keywords') }}</h2>
    <hr>

    @include('layouts.import.errors')

    {!! Form::open(['url' => route('loadNegativeKeywords'), 'method' => 'post', 'files' => true]) !!}

        <div class="form-group">
            {{ Form::label('file', __('import.csv_file'), ['class' => 'control-label']) }}
            {{ Form::file('file', ['class' => 'form-control-file']) }}
        </div>

        <div class="form-group">
            {{ Form::checkbox('containsTitles', 1, true, ['class' => '']) }}
            {{ Form::label('containsTitles', __('import.contains_titles'), ['class' => 'form-check-label']) }}
        </div>

        {{ Form::submit(__('import.load'), ['class' => 'btn btn-primary']) }}

    {!! Form::close() !!}

@endsection
