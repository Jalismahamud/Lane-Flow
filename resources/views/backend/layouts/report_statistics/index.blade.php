@extends('backend.app', ['title' => 'Report Statistics'])

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">📊 Report Statistics Dashboard</h1>
                        <p class="text-muted mb-0">Comprehensive overview of report analytics</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Report Statistics</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    @php use App\Helper\Helper; @endphp
                    
                    <x-dashboard.card title="Total Reports" value="{{ Helper::formatNumberShort($totalReports) }}" icon="file-alt" color="primary" />
                    <x-dashboard.card title="Reports This Month" value="{{ $totalThisMonth }}" icon="file" color="success" />
                    <x-dashboard.card title="Updates This Month" value="{{ $updatesThisMonth }}" icon="edit" color="warning" />
                    <x-dashboard.card title="Recent Reports" value="{{ Helper::formatNumberShort($recentReports->total()) }}" icon="history" color="info" />
                </div>

                <!-- Zone Distribution 3D Pie Chart -->
                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-gradient-primary text-white">
                                <h3 class="card-title mb-0">🗺️ Reports by Zone</h3>
                                <p class="mb-0 small">Geographical distribution of reports</p>
                            </div>
                            <div class="card-body">
                                <div id="zone-piechart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-gradient-danger text-white">
                                <h3 class="card-title mb-0">⚠️ Reports by Status</h3>
                                <p class="mb-0 small">Status distribution overview</p>
                            </div>
                            <div class="card-body">
                                <div id="status-piechart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart with Advanced Filters -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-gradient-info text-white">
                                <h3 class="card-title mb-2">📈 Reports Analytics - Created vs Updated</h3>
                                <form method="GET" class="d-flex align-items-center flex-wrap gap-2" id="filterForm">
                                    <!-- Filter Type -->
                                    <div class="d-flex align-items-center">
                                        <label class="me-2 text-white">View:</label>
                                        <select name="filter_type" class="form-select form-select-sm" style="width: 120px;" onchange="toggleFilters(this.value)">
                                            <option value="daily" {{ $filterType == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ $filterType == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ $filterType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                    </div>

                                    <!-- Daily/Monthly Filters -->
                                    <div id="month-filter" class="d-flex align-items-center" style="display: {{ $filterType != 'weekly' ? 'flex' : 'none' }} !important;">
                                        <label class="me-2 text-white">Month:</label>
                                        <select name="month" class="form-select form-select-sm" style="width: 120px;">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                                    {{ \DateTime::createFromFormat('!m', $m)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- Weekly Filter -->
                                    <div id="week-filter" class="d-flex align-items-center" style="display: {{ $filterType == 'weekly' ? 'flex' : 'none' }} !important;">
                                        <label class="me-2 text-white">Week:</label>
                                        <select name="week" class="form-select form-select-sm" style="width: 120px;">
                                            @for($w = 1; $w <= 52; $w++)
                                                <option value="{{ $w }}" {{ $selectedWeek == $w ? 'selected' : '' }}>Week {{ $w }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- Year Filter -->
                                    <div class="d-flex align-items-center">
                                        <label class="me-2 text-white">Year:</label>
                                        <select name="year" class="form-select form-select-sm" style="width: 100px;">
                                            @php $currentYear = date('Y'); $startYear = $currentYear - 5; @endphp
                                            @for($y = $startYear; $y <= $currentYear; $y++)
                                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <button class="btn btn-sm btn-light shadow-sm" type="submit">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                    
                                    <button class="btn btn-sm btn-danger shadow-sm" type="button" onclick="resetFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <div id="reports-linechart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reports with Search & Pagination -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-gradient-success text-white flex-column flex-md-row d-flex justify-content-between align-items-center">
                                <h3 class="card-title">📋 Recent Reports</h3>
                             
                                <!-- Search & Filters Form -->
                                <form method="GET" class="row g-3" id="searchForm">
                                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                                    <input type="hidden" name="week" value="{{ $selectedWeek }}">
                                    
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            {{-- <span class="input-group-text bg-white">
                                                <i class="fas fa-search"></i>
                                            </span> --}}
                                            <input type="text" name="search" style="height: 35px" class="form-control" placeholder="Search by text, user, status..." value="{{ $search }}" onkeyup="handleSearchInput()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="status" style="height: 35px" class="form-select" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $s)
                                                <option value="{{ $s }}" style="font-size: 14px" {{ $selectedStatus == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="lane" class="form-select" style="height: 35px" onchange="this.form.submit()">
                                            <option value="">All Lanes</option>
                                            @foreach($lanes as $l)
                                                <option value="{{ $l }}" style="font-size: 14px" {{ $selectedLane == $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                   
                                </form>

                                  
                            </div>



                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Text</th>
                                                <th>Status</th>
                                                <th>Lane</th>
                                                <th>Location</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentReports as $index => $r)
                                                <tr>
                                                    <td>{{ $recentReports->firstItem() + $index }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                                {{ $r->user ? substr($r->user->name ?? $r->user->email, 0, 1) : '?' }}
                                                            </div>
                                                            <span>{{ $r->user ? ($r->user->name ?? $r->user->email) : '-' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $r->text ? Str::limit($r->text, 60) : '-' }}</td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'blocked' => 'danger',
                                                                'clear' => 'success',
                                                                'accident' => 'warning',
                                                                'other' => 'secondary'
                                                            ];
                                                            $color = $statusColors[$r->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }}">
                                                            {{ ucfirst($r->status) }}
                                                        </span>
                                                    </td>
                                                    <td><span class="badge bg-info">{{ ucfirst($r->lane) }}</span></td>
                                                    <td>
                                                        @if($r->latitude && $r->longitude)
                                                            <a href="javascript:void(0)" onclick="openMapModal({{ $r->latitude }}, {{ $r->longitude }}, '{{ addslashes($r->user ? ($r->user->name ?? $r->user->email) : 'Unknown') }}', '{{ addslashes($r->text ?? 'No description') }}', '{{ $r->status }}', '{{ $r->lane }}')" class="text-decoration-none">
                                                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                                                {{ number_format($r->latitude, 4) }}, {{ number_format($r->longitude, 4) }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i') : '-' }}</td>
                                                    <td>{{ $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->format('Y-m-d H:i') : '-' }}</td>
                                                    <td>
                                                        @if($r->latitude && $r->longitude)
                                                            <button class="btn btn-sm btn-outline-primary" onclick="openMapModal({{ $r->latitude }}, {{ $r->longitude }}, '{{ addslashes($r->user ? ($r->user->name ?? $r->user->email) : 'Unknown') }}', '{{ addslashes($r->text ?? 'No description') }}', '{{ $r->status }}', '{{ $r->lane }}')">
                                                                <i class="fas fa-map"></i> View
                                                            </button>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No reports found</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        Showing {{ $recentReports->firstItem() ?? 0 }} to {{ $recentReports->lastItem() ?? 0 }} 
                                        of {{ $recentReports->total() }} reports
                                    </div>
                                    <div>
                                        {{ $recentReports->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="mapModalLabel">🌍 Interactive 3D Map View</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body p-0">
                    <div id="map-container" style="height: 600px; position: relative;">
                        <div id="map" style="height: 100%; width: 100%;"></div>
                        <div class="map-controls" style="position: absolute; top: 10px; right: 10px; z-index: 1000;">
                            <button class="btn btn-sm btn-light shadow-sm me-2" onclick="toggle3DMap()">
                                <i class="fas fa-cube"></i> Toggle 3D
                            </button>
                            <button class="btn btn-sm btn-light shadow-sm" onclick="resetMapView()">
                                <i class="fas fa-sync"></i> Reset
                            </button>
                        </div>
                        <div id="map-info" class="card shadow-lg" style="position: absolute; bottom: 20px; left: 20px; z-index: 1000; max-width: 350px; display: none;">
                            <div class="card-body p-3">
                                <h6 class="mb-2"><strong id="info-user"></strong></h6>
                                <p class="mb-1 small" id="info-text"></p>
                                <div class="d-flex gap-2">
                                    <span class="badge" id="info-status"></span>
                                    <span class="badge bg-info" id="info-lane"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #0093E9 0%, #80D0C7 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #f85032 0%, #e73827 100%);
    }
    .card {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.1);
        cursor: pointer;
    }
    .avatar {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    .map-controls button {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.9) !important;
    }
    .modal-xl {
        max-width: 95%;
    }
</style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.3/dist/apexcharts.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <script>
        const createdData = {!! $createdDataJson !!};
        const updatedData = {!! $updatedDataJson !!};
        const mapReports = {!! json_encode($mapReports) !!};
        const zoneData = {!! json_encode($zoneData) !!};
        const statusDistribution = {!! json_encode($statusDistribution) !!};
        
        let map;
        let is3DMode = false;
        let markers = [];

        // Initialize Map (delayed until modal opens)
        function initMap(lat = null, lng = null) {
            if (map) {
                map.remove();
            }

            const centerLat = lat || 23.8103;
            const centerLng = lng || 90.4125;
            
            map = L.map('map').setView([centerLat, centerLng], lat ? 15 : 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Clear existing markers
            markers.forEach(marker => marker.remove());
            markers = [];

            // Add all markers
            mapReports.forEach(report => {
                if (report.latitude && report.longitude) {
                    const statusColors = {
                        'blocked': 'red',
                        'clear': 'green',
                        'accident': 'orange',
                        'other': 'gray'
                    };
                    
                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="background-color: ${statusColors[report.status] || 'gray'}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    const marker = L.marker([report.latitude, report.longitude], { icon: icon })
                        .bindPopup(`
                            <div style="min-width: 200px;">
                                <strong>${report.user ? (report.user.name || report.user.email) : 'Unknown'}</strong><br>
                                <em style="font-size: 12px;">${report.text ? report.text.substring(0, 80) + '...' : 'No description'}</em><br>
                                <div class="mt-2">
                                    <span class="badge bg-${statusColors[report.status] === 'green' ? 'success' : statusColors[report.status] === 'red' ? 'danger' : statusColors[report.status] === 'orange' ? 'warning' : 'secondary'}">${report.status}</span>
                                    <span class="badge bg-info">${report.lane}</span>
                                </div>
                            </div>
                        `);
                    marker.addTo(map);
                    markers.push(marker);
                }
            });

            // Focus on specific marker if provided
            if (lat && lng) {
                setTimeout(() => {
                    map.setView([lat, lng], 15);
                }, 100);
            } else if (mapReports.length > 0) {
                const bounds = L.latLngBounds(mapReports.map(r => [r.latitude, r.longitude]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        function openMapModal(lat, lng, user, text, status, lane) {
            const modal = new bootstrap.Modal(document.getElementById('mapModal'));
            modal.show();
            
            // Initialize map after modal is shown
            setTimeout(() => {
                initMap(lat, lng);
                
                // Show info card
                const infoCard = document.getElementById('map-info');
                document.getElementById('info-user').textContent = user;
                document.getElementById('info-text').textContent = text.substring(0, 100);
                
                const statusBadge = document.getElementById('info-status');
                const statusColors = {
                    'blocked': 'danger',
                    'clear': 'success',
                    'accident': 'warning',
                    'other': 'secondary'
                };
                statusBadge.className = 'badge bg-' + (statusColors[status] || 'secondary');
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
                document.getElementById('info-lane').textContent = lane.charAt(0).toUpperCase() + lane.slice(1);
                infoCard.style.display = 'block';
            }, 300);
        }

        function toggle3DMap() {
            is3DMode = !is3DMode;
            if (is3DMode) {
                map.eachLayer(layer => {
                    if (layer instanceof L.TileLayer) {
                        map.removeLayer(layer);
                    }
                });
                L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles © Esri',
                    maxZoom: 19
                }).addTo(map);
            } else {
                map.eachLayer(layer => {
                    if (layer instanceof L.TileLayer) {
                        map.removeLayer(layer);
                    }
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
            }
        }

        function resetMapView() {
            if (mapReports.length > 0) {
                const bounds = L.latLngBounds(mapReports.map(r => [r.latitude, r.longitude]));
                map.fitBounds(bounds, { padding: [50, 50] });
            } else {
                map.setView([23.8103, 90.4125], 11);
            }
        }

        // Chart for line graph
        function drawReportsLineChart(created, updated) {
            const categories = Object.keys(created);
            const createdCounts = Object.values(created);
            const updatedCounts = Object.values(updated);

            const options = {
                series: [
                    { name: 'Created', data: createdCounts, color: '#11998e' },
                    { name: 'Updated', data: updatedCounts, color: '#ffc107' }
                ],
                chart: {
                    height: 400,
                    type: 'area',
                    zoom: { enabled: true },
                    toolbar: { show: true },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                    }
                },
                xaxis: { 
                    categories: categories, 
                    labels: { rotate: -45 }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                title: { 
                    text: 'Reports Activity Over Time', 
                    align: 'center',
                    style: {
                        fontSize: '18px',
                        fontWeight: 'bold'
                    }
                },
                tooltip: { 
                    shared: true, 
                    intersect: false,
                    theme: 'dark'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right'
                }
            };

            const chart = new ApexCharts(document.querySelector('#reports-linechart'), options);
            chart.render();
        }

        // 3D Pie Chart for Zones
        function drawZonePieChart(zones) {
            const labels = zones.map(z => z.name);
            const series = zones.map(z => z.count);

            const options = {
                series: series,
                chart: {
                    type: 'donut',
                    height: 400,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                labels: labels,
                colors: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe', '#43e97b', '#fa709a'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px'
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 'bold'
                                },
                                total: {
                                    show: true,
                                    label: 'Total Reports',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex]
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                },
                title: {
                    text: 'Geographical Distribution',
                    align: 'center',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold'
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " reports"
                        }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector('#zone-piechart'), options);
            chart.render();
        }

        // 3D Pie Chart for Status Distribution
        function drawStatusPieChart(statusData) {
            const labels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
            const series = Object.values(statusData);

            const statusColors = {
                'Blocked': '#dc3545',
                'Clear': '#28a745',
                'Accident': '#ffc107',
                'Other': '#6c757d'
            };

            const colors = labels.map(label => statusColors[label] || '#6c757d');

            const options = {
                series: series,
                chart: {
                    type: 'pie',
                    height: 400,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                labels: labels,
                colors: colors,
                plotOptions: {
                    pie: {
                        expandOnClick: true,
                        dataLabels: {
                            offset: -5
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.labels[opts.seriesIndex] + ": " + opts.w.config.series[opts.seriesIndex]
                    },
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold'
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                },
                title: {
                    text: 'Status Distribution',
                    align: 'center',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold'
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " reports"
                        }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector('#status-piechart'), options);
            chart.render();
        }

        function toggleFilters(filterType) {
            const monthFilter = document.getElementById('month-filter');
            const weekFilter = document.getElementById('week-filter');
            
            if (filterType === 'weekly') {
                monthFilter.style.display = 'none';
                weekFilter.style.display = 'flex';
            } else {
                monthFilter.style.display = 'flex';
                weekFilter.style.display = 'none';
            }
        }

        function resetFilters() {
            const now = new Date();
            const currentMonth = now.getMonth() + 1;
            const currentYear = now.getFullYear();
            
            // Build URL with default values
            const url = new URL(window.location.href);
            url.search = ''; // Clear all params
            url.searchParams.set('filter_type', 'monthly');
            url.searchParams.set('month', currentMonth);
            url.searchParams.set('year', currentYear);
            
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Draw charts
            if (!createdData || Object.keys(createdData).length === 0) {
                const d = new Date();
                const key = d.toISOString().slice(0,10);
                drawReportsLineChart({ [key]: 0 }, { [key]: 0 });
            } else {
                drawReportsLineChart(createdData, updatedData);
            }

            // Draw zone pie chart
            if (zoneData && zoneData.length > 0) {
                drawZonePieChart(zoneData);
            } else {
                document.querySelector('#zone-piechart').innerHTML = '<div class="text-center py-5"><i class="fas fa-chart-pie fa-3x text-muted mb-3"></i><p class="text-muted">No zone data available</p></div>';
            }

            // Draw status pie chart
            if (statusDistribution && Object.keys(statusDistribution).length > 0) {
                drawStatusPieChart(statusDistribution);
            } else {
                document.querySelector('#status-piechart').innerHTML = '<div class="text-center py-5"><i class="fas fa-chart-pie fa-3x text-muted mb-3"></i><p class="text-muted">No status data available</p></div>';
            }
        });
    </script>
@endpush