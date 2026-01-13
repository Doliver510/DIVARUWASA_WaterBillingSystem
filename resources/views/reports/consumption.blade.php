<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Reports</div>
                <h2 class="page-title">Water Consumption Report</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('reports.consumption.export', ['format' => 'pdf', 'year' => $year]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('reports.consumption.export', ['format' => 'excel', 'year' => $year]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="subheader text-white-50">Total Consumption {{ $year }}</div>
                    <div class="h1 mb-0">{{ number_format($totalConsumption) }} cu.m</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Monthly Average</div>
                    <div class="h1 mb-0">{{ $monthlyData->count() > 0 ? number_format($totalConsumption / $monthlyData->count()) : 0 }} cu.m</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Months with Data</div>
                    <div class="h1 mb-0">{{ $monthlyData->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        {{-- Monthly Consumption Table --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Water Consumption - {{ $year }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Readings</th>
                                <th class="text-end">Total Consumption</th>
                                <th class="text-end">Average per Consumer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyData as $data)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $data->billing_period)->format('F') }}</td>
                                    <td class="text-center">{{ $data->reading_count }}</td>
                                    <td class="text-end fw-bold">{{ number_format($data->total_consumption) }} cu.m</td>
                                    <td class="text-end text-muted">{{ number_format($data->avg_consumption, 1) }} cu.m</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No consumption data for {{ $year }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($monthlyData->count() > 0)
                            <tfoot>
                                <tr class="bg-light fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center">{{ $monthlyData->sum('reading_count') }}</td>
                                    <td class="text-end">{{ number_format($totalConsumption) }} cu.m</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Consumers --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Consumers - {{ $year }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Consumer</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topConsumers as $consumer)
                                <tr>
                                    <td>
                                        <div>{{ $consumer->consumer->full_name ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ $consumer->consumer->id_no ?? '' }}</small>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($consumer->total_consumption) }} cu.m</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        No data available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

