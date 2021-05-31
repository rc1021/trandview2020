<table {!! $attributes !!}>
    <thead>
    <tr>
        @foreach($headers as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
    <tr>
        @foreach($row as $item)
        @if (gettype($item) == 'array')
        <td colspan="{{ $item['col'] }}">{!! $item['content'] !!}</td>
        @else
        <td>{!! $item !!}</td>
        @endif
        @endforeach
    </tr>
    @endforeach
    </tbody>
</table>
