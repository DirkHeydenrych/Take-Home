<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Order Viewer')</title>

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: #2563eb;
            margin-bottom: 10px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            padding: 8px 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #059669;
            color: white;
        }

        .btn-success:hover {
            background: #047857;
        }

        .stats {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }

        .orders-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .orders-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .orders-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .orders-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .orders-table tbody tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .paid-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .paid-yes {
            background: #d1fae5;
            color: #065f46;
        }

        .paid-no {
            background: #fee2e2;
            color: #991b1b;
        }

        .pagination {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }

        .pagination-controls {
            display: flex;
            gap: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            margin: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .order-details h4 {
            margin-bottom: 10px;
            color: #374151;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .detail-item {
            padding: 10px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .detail-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .detail-value {
            font-size: 14px;
            color: #111827;
            margin-top: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        /* Filter Toggle Styles */
        .filters-header {
            user-select: none;
        }

        .filter-toggle {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            transition: transform 0.2s;
        }

        .filters-header:hover .filter-toggle {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                justify-content: stretch;
            }

            .filter-actions .btn {
                flex: 1;
            }

            /* Auto-collapse filters on mobile */
            .filter-content {
                display: none;
            }

            .filter-toggle {
                font-size: 18px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            /* Mobile Table Styles - Convert to Cards */
            .table-container {
                overflow: visible;
            }

            .orders-table {
                display: none;
                /* Hide table on mobile */
            }

            /* Mobile card view */
            .mobile-orders {
                display: block;
            }

            .mobile-order-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 15px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .mobile-order-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 10px;
                font-weight: 600;
            }

            .mobile-order-id {
                color: #2563eb;
                font-size: 16px;
            }

            .mobile-order-status {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
            }

            .mobile-order-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 10px;
                font-size: 14px;
            }

            .mobile-order-detail {
                display: flex;
                flex-direction: column;
            }

            .mobile-order-label {
                font-weight: 600;
                color: #6b7280;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }

            .mobile-order-value {
                color: #374151;
            }

            .mobile-order-actions {
                text-align: center;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #f3f4f6;
            }

            .mobile-order-actions .btn {
                padding: 8px 16px;
                font-size: 14px;
            }            .detail-grid {
                grid-template-columns: 1fr;
            }

            /* Mobile Modal Items Table */
            .items-table {
                display: none; /* Hide table on mobile */
            }

            .mobile-items {
                display: block;
            }

            .mobile-item-card {
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 12px;
                margin-bottom: 10px;
            }

            .mobile-item-header {
                font-weight: 600;
                color: #374151;
                margin-bottom: 8px;
                font-size: 14px;
            }

            .mobile-item-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                font-size: 13px;
            }

            .mobile-item-detail {
                display: flex;
                flex-direction: column;
            }

            .mobile-item-label {
                font-weight: 600;
                color: #6b7280;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }

            .mobile-item-value {
                color: #374151;
            }

            .mobile-item-total {
                grid-column: 1 / -1;
                margin-top: 6px;
                padding-top: 6px;
                border-top: 1px solid #d1d5db;
                font-weight: 600;
                color: #111827;
                text-align: right;
            }

            .pagination {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Desktop - Hide mobile views */
        @media (min-width: 769px) {
            .mobile-orders,
            .mobile-items {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>

    <script>
        // CSRF token setup
        window.Laravel = {
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        // Global API base URL
        window.API_BASE_URL = '/api';
    </script>

    @yield('scripts')
</body>

</html>