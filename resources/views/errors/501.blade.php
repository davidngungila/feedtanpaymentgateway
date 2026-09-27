@php
    $code = '501';
    $title = 'Not implemented';
    $tone = 'terracotta';
@endphp

@extends('layouts.app')

@section('title', $code . ' — ' . $title)

@section('content')
    @include('errors.partials.plain', [
        'code' => $code,
        'title' => $title,
        'message' => 'The server does not support the functionality requested.',
        'tone' => $tone,
    ])
@endsection