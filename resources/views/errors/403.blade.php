@php
    $code = '403';
    $title = 'Forbidden';
    $tone = 'danger';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'You do not have permission to access this page. Contact an administrator if you believe this is a mistake.',
        'tone' => $tone,
    ])
@endsection