@extends('layouts.app')

@section('title', 'Signal History')

@section('content')

<a href="{{ route('signal.history') }}">ALL</a>
<span>|</span>
<a href="{{ route('signal.history', ['clock' => 86400]) }}">1天</a>
<span>|</span>
<a href="{{ route('signal.history', ['clock' => 144]) }}">144分鐘</a>
<span>|</span>
<a href="{{ route('signal.history', ['clock' => 2]) }}">2分鐘</a>
<span>|</span>
<a href="{{ route('signal.history', ['clock' => 1]) }}">1分鐘</a>

<table border="1">
    <thead>
        <tr>
            <td>Created At</td>
            <td>Clock</td>
            <td>Content</td>
        </tr>
    </thead>
    <tbody>
        @forelse ($collection as $item)
        <tr>
            <td>{{ $item->created_at }}</td>
            <td>{{ $item->clock }}</td>
            <td>{{ $item->message }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3">No History</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection
