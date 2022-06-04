@if( $errors->any() )
    <div class="alert alert-danger">
        <strong>{{ $message ?? '' }}</strong>
        @foreach( $errors->all() as $error )
            {{ $error }}<br>
        @endforeach
    </div>
@else
    @isset( $message )
        <div class="alert alert-success">
            <strong>{{ $message }}</strong>
        </div>
    @endisset
@endif
