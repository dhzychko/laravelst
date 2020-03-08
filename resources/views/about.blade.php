@extends('layouts.app')

@section('page-title', 'About US')

@section('content')
    <h1>About US</h1>
@endsection

@section('aside')
    @parent

    <p>This is appended to the master sidebar.</p>
@endsection