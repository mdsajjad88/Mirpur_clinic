@extends('clinic::layouts.app2')

@section('content')
    <div class="container-fluid">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Application Logs'])
            <div class="row mb-3">
                <div class="col-md-3">
                    <form action="{{ route('logs.show') }}" method="GET">
                        <select class="form-control" name="date" onchange="this.form.submit()">
                            @foreach($lastFiveDays as $d)
                                <option value="{{ $d }}" {{ $d == $date ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="bg-dark text-white p-3 rounded"
                         style="height:800px; overflow-y:scroll; font-family: monospace; white-space: pre-wrap; word-break: break-word;">
                        @forelse($logs as $line)
                            <pre style="margin:0; white-space: pre-wrap; word-break: break-word;">{{ $line }}</pre>
                        @empty
                            <p>No logs found for {{ $date }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endcomponent
    </div>
@endsection
