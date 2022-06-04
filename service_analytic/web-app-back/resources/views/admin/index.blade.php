@extends('admin.layouts.app')

@section('content')
    <layout
            :user="{{Auth::user()}}"
            :token="{{json_encode($token[0])}}"
            :seller="{{json_encode($seller)}}"
            :url-api="{{json_encode($_ENV['LOCAL_APP_API_URL'])}}"
            :url-admin-api="{{json_encode($_ENV['ADMIN_APP_API_URL'])}}"
            :url-va="{{json_encode($_ENV['VIRTUAL_ASSISTANT_V2_URL_FRONT'])}}"
    ></layout>
@endsection
