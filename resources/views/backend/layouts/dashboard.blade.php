@php
    use App\Helper\Helper;
@endphp

@extends('backend.app', ['title' => 'Dashboard'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>


                <div class="row">
                    <x-dashboard.card title="Total Users" value="{{ Helper::formatNumberShort($totalUsers) }}"
                        icon="users" color="primary" />
                    <x-dashboard.card title="Total Reviews" value="{{ Helper::formatNumberShort($totalReviews) }}"
                        icon="message-square" color="success" />
                    <x-dashboard.card title="Total Ratings" value="{{ Helper::formatNumberShort($totalRatings) }}"
                        icon="star" color="warning" />
                    <x-dashboard.card title="Growth" value="{{ Helper::formatNumberShort($growth) }}%" icon="trending-up"
                        color="info" />
                </div>


                <div class="row mt-4">
                    <div class="col-lg-12 mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">User Join Statistics</h4>
                            <select id="filterRange" class="form-select w-auto ms-3">
                                <option value="7">Last 7 Days</option>
                                <option value="30" selected>Last 30 Days</option>
                                <option value="90">Last 3 Months</option>
                                <option value="365">Last Year</option>
                                <option value="9999">All Time</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-lg-12 w-500">
                        <div id="userJoinChart" style="min-height: 380px;"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rawData = @json($chartData);
            const users = @json($users);
            const chartEl = document.getElementById('userJoinChart');

            if (!rawData || rawData.length === 0) {
                chartEl.innerHTML = '<div class="text-center text-muted p-5">No user data available</div>';
                return;
            }

            function getFilteredData(days) {
                if (days >= 9999) return rawData.map(i => ({
                    x: i.timestamp,
                    y: i.count
                }));

                const now = new Date();
                const cutoff = new Date(now.getFullYear(), now.getMonth(), now.getDate() - days + 1);
                cutoff.setHours(0, 0, 0, 0);

                return rawData
                    .filter(item => item.timestamp >= cutoff.getTime())
                    .map(item => ({
                        x: item.timestamp,
                        y: item.count
                    }));
            }

            const options = {
                chart: {
                    type: 'area',
                    height: 380,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Users Joined',
                    data: getFilteredData(7)
                }],
                xaxis: {
                    type: 'datetime',
                    labels: {
                        format: 'dd MMM'
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    },
                    custom: function({
                        dataPointIndex,
                        w
                    }) {
                        const ts = w.globals.seriesX[0][dataPointIndex];
                        const dateStr = new Date(ts).toISOString().slice(0, 10);
                        const dayUsers = users.filter(u => u.join_date === dateStr);

                        if (!dayUsers.length) {
                            return `<div class="p-3">No users joined on ${dateStr}</div>`;
                        }

                        return `
                    <div style="padding:12px;max-height:260px;overflow-y:auto;font-size:13px;background:#fff;border-radius:6px;box-shadow:0 2px 10px rgba(0,0,0,0.15);">
                        <div style="font-weight:600;margin-bottom:8px;color:#222;">
                            ${dayUsers.length} user(s) joined
                        </div>
                        ${dayUsers.map(u => `
                                    <div style="display:flex;align-items:center;margin:5px 0;gap:8px;">
                                        <img src="${u.avatar}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                        <span style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            ${u.name}
                                        </span>
                                    </div>
                                `).join('')}
                    </div>`;
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#00BFA6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.6,
                        opacityTo: 0.1
                    }
                },
            };

            const chart = new ApexCharts(chartEl, options);
            chart.render();

            document.getElementById('filterRange').addEventListener('change', function() {
                const days = parseInt(this.value, 10);
                chart.updateSeries([{
                    data: getFilteredData(days)
                }]);
            });
        });
    </script>
@endpush
