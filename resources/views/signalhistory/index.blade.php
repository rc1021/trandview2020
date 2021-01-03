@extends('layouts.app')

@section('title', 'Signal History')

@section('content')

<table border="1">
    <thead>
        <tr>
            <td>Created At</td>
            <td>Clock</td>
            <td>Content</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($collection as $item)
        <tr>
            <td>{{ $item->created_at }}</td>
            <td>{{ $item->clock }}</td>
            <td>{{ $item->message }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
