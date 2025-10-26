@extends('backend.app', ['title' => 'Dashboard'])

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row">
                    @php
                        use App\Helper\Helper;
                    @endphp

                    {{-- Total User --}}
                    <x-dashboard.card title="Total Users" value="{{ Helper::formatNumberShort($totalUsers) }}"
                        icon="users" color="primary" />
                    <x-dashboard.card title="Total " value="{{ Helper::formatNumberShort(0) }}"
                        icon="question" color="success" />
                    <x-dashboard.card title="Total " value="{{ Helper::formatNumberShort(0) }}"
                        icon="star" color="warning" />
                    <x-dashboard.card title="Total " value="{{ Helper::formatNumberShort(0) }}"
                        icon="chart-line" color="warning" />
                </div>
                <!-- ROW-1 END -->

            </div>
        </div>
    </div>
@endsection


