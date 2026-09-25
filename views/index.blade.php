<!doctype html>
<html lang="en">
    <head>
        <title>Database Logging</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Google Fonts: Inter & JetBrains Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
        
        <!-- Bootstrap 4 & FontAwesome & DataTables & Select2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        
        <style>
            :root {
                --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                --font-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
                --color-bg: #f8fafc;
                --color-card-bg: #ffffff;
                --color-border: #e2e8f0;
                --color-border-subtle: #edf2f7;
                --color-border-input: #cbd5e1;
                --color-text-title: #0f172a;
                --color-text-body: #334155;
                --color-text-muted: #64748b;
                --color-primary: #3b82f6;
                --color-primary-dark: #1d4ed8;
                --radius-card: 12px;
                --radius-control: 8px;
            }

            body {
                font-family: var(--font-sans);
                background-color: var(--color-bg);
                color: var(--color-text-body);
                font-size: 13.5px;
                line-height: 1.55;
                -webkit-font-smoothing: antialiased;
            }

            .font-monospace {
                font-family: var(--font-mono) !important;
            }

            /* Top Navigation Bar */
            .top-navbar {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--color-border);
                padding: 0.85rem 1.75rem;
                position: sticky;
                top: 0;
                z-index: 100;
                transition: box-shadow 0.2s;
            }

            .nav-inner {
                max-width: 1440px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .nav-brand-group {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .nav-brand-icon {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
            }

            .nav-title {
                font-size: 1rem;
                font-weight: 700;
                color: var(--color-text-title);
                margin: 0;
                letter-spacing: -0.02em;
            }

            .nav-badge {
                font-size: 0.7rem;
                font-weight: 600;
                color: #3b82f6;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                padding: 0.15rem 0.5rem;
                border-radius: 20px;
            }

            /* Container */
            .main-content {
                max-width: 1440px;
                margin: 1.5rem auto 2.5rem;
                padding: 0 1.25rem;
            }

            /* Fluid Card Panels */
            .card-panel {
                background: var(--color-card-bg);
                border: 1px solid var(--color-border);
                border-radius: var(--radius-card);
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
                margin-bottom: 1.25rem;
                overflow: hidden;
                transition: border-color 0.2s, box-shadow 0.2s;
            }

            /* Filter Form Section */
            .filter-body {
                padding: 1.25rem 1.5rem;
            }

            .filter-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                margin-bottom: 0.4rem;
                display: flex;
                align-items: center;
                gap: 0.35rem;
            }

            .form-control-custom {
                height: 38px;
                font-size: 0.85rem;
                border-radius: var(--radius-control);
                border: 1px solid var(--color-border-input);
                background-color: #f8fafc;
                color: var(--color-text-title);
                padding: 0.4rem 0.75rem;
                transition: all 0.15s ease-in-out;
            }

            .form-control-custom:hover {
                border-color: #94a3b8;
                background-color: #ffffff;
            }

            .form-control-custom:focus {
                background-color: #ffffff;
                border-color: var(--color-primary);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                outline: none;
            }

            /* Button Styling */
            .btn-action-quiet {
                background: #ffffff;
                border: 1px solid var(--color-border-input);
                color: #334155;
                font-size: 0.8rem;
                font-weight: 500;
                padding: 0.35rem 0.75rem;
                border-radius: var(--radius-control);
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .btn-action-quiet:hover {
                background: #f1f5f9;
                border-color: #94a3b8;
                color: #0f172a;
                text-decoration: none;
            }

            .btn-action-quiet:active {
                transform: scale(0.98);
            }

            .btn-action-primary {
                background: #0f172a;
                border: 1px solid #0f172a;
                color: #ffffff;
                font-size: 0.8rem;
                font-weight: 500;
                padding: 0.35rem 1rem;
                border-radius: var(--radius-control);
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .btn-action-primary:hover {
                background: #1e293b;
                border-color: #1e293b;
                color: #ffffff;
                text-decoration: none;
            }

            .btn-action-primary:active {
                transform: scale(0.98);
            }

            /* Select2 Modern Custom Styling */
            .select2-container {
                width: 100% !important;
            }

            .select2-container .select2-selection--single {
                height: 38px !important;
                background-color: #f8fafc !important;
                border: 1px solid var(--color-border-input) !important;
                border-radius: var(--radius-control) !important;
                display: flex !important;
                align-items: center !important;
                transition: all 0.15s ease-in-out !important;
            }

            .select2-container .select2-selection--single:hover {
                border-color: #94a3b8 !important;
                background-color: #ffffff !important;
            }

            .select2-container--open .select2-selection--single,
            .select2-container--focus .select2-selection--single {
                background-color: #ffffff !important;
                border-color: var(--color-primary) !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
                outline: none !important;
            }

            .select2-container .select2-selection--single .select2-selection__rendered {
                color: var(--color-text-title) !important;
                font-size: 0.85rem !important;
                line-height: normal !important;
                padding-left: 0.75rem !important;
                padding-right: 1.75rem !important;
            }

            .select2-container .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
                right: 8px !important;
                top: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .select2-container .select2-selection--single .select2-selection__arrow b {
                border-color: #64748b transparent transparent transparent !important;
                border-width: 5px 4px 0 4px !important;
            }

            .select2-container--open .select2-selection--single .select2-selection__arrow b {
                border-color: transparent transparent #64748b transparent !important;
                border-width: 0 4px 5px 4px !important;
            }

            .select2-container .select2-selection--single .select2-selection__clear {
                font-size: 1.1rem !important;
                color: #94a3b8 !important;
                margin-right: 18px !important;
                line-height: 36px !important;
            }

            .select2-container .select2-selection--single .select2-selection__clear:hover {
                color: #ef4444 !important;
            }

            .select2-dropdown {
                border: 1px solid var(--color-border) !important;
                border-radius: var(--radius-control) !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
                font-size: 0.84rem !important;
                z-index: 1050 !important;
                overflow: hidden !important;
            }

            .select2-search--dropdown {
                padding: 0.5rem !important;
                background: #f8fafc !important;
                border-bottom: 1px solid var(--color-border-subtle) !important;
            }

            .select2-search--dropdown .select2-search__field {
                border: 1px solid var(--color-border-input) !important;
                border-radius: 6px !important;
                padding: 0.35rem 0.65rem !important;
                font-size: 0.82rem !important;
                outline: none !important;
                background: #ffffff !important;
            }

            .select2-search--dropdown .select2-search__field:focus {
                border-color: var(--color-primary) !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.12) !important;
            }

            .select2-results__option {
                padding: 0.45rem 0.75rem !important;
            }

            .select2-results__option--highlighted[aria-selected] {
                background-color: #f1f5f9 !important;
                color: var(--color-text-title) !important;
            }

            .select2-results__option[aria-selected="true"] {
                background-color: #eff6ff !important;
                color: var(--color-primary-dark) !important;
                font-weight: 600 !important;
            }

            /* Logs Table */
            .dt-container,
            .dataTables_wrapper {
                position: relative !important;
                width: 100% !important;
            }

            div.dt-processing,
            div.dataTables_processing {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                margin: 0 !important;
                padding: 0 !important;
                width: auto !important;
                min-width: 140px !important;
                background: #ffffff !important;
                border: 1px solid var(--color-border) !important;
                border-radius: var(--radius-control) !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
                z-index: 1050 !important;
            }

            div.dt-processing > div:last-child {
                display: none !important;
            }

            #logs-table {
                margin: 0 !important;
                border: none !important;
                width: 100% !important;
            }

            #logs-table thead th {
                background: #f8fafc;
                color: #475569;
                font-size: 0.73rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-top: none;
                border-bottom: 1px solid var(--color-border);
                padding: 0.8rem 1.15rem;
            }

            #logs-table tbody td {
                padding: 0.85rem 1rem;
                font-size: 0.84rem;
                border-top: 1px solid var(--color-border-subtle);
                vertical-align: middle;
            }

            #logs-table tbody tr:not(.detail-row-container) {
                cursor: pointer;
                transition: background-color 0.15s ease;
            }

            #logs-table tbody tr:not(.detail-row-container):hover {
                background-color: #f8fafc;
            }

            #logs-table tbody tr.shown {
                background-color: #f1f5f9 !important;
            }

            .detail-row-container {
                background-color: #f8fafc !important;
                cursor: default !important;
            }

            /* Method Tags */
            .method-tag {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-family: var(--font-mono);
                font-size: 0.72rem;
                font-weight: 600;
                padding: 0.2rem 0.55rem;
                border-radius: 6px;
                line-height: 1.2;
                min-width: 52px;
                letter-spacing: 0.03em;
            }

            .method-get {
                background: #ecfdf5;
                color: #047857;
                border: 1px solid #a7f3d0;
            }

            .method-post {
                background: #eff6ff;
                color: #1d4ed8;
                border: 1px solid #bfdbfe;
            }

            .method-put, .method-patch {
                background: #fffbeb;
                color: #b45309;
                border: 1px solid #fde68a;
            }

            .method-delete {
                background: #fef2f2;
                color: #b91c1c;
                border: 1px solid #fecaca;
            }

            /* Datatable Content Cells */
            .path-cell {
                max-width: 320px;
            }

            .path-main {
                display: block;
                font-weight: 600;
                color: var(--color-text-title);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .path-host {
                display: block;
                font-size: 0.73rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .user-cell {
                max-width: 190px;
            }

            .user-name {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-weight: 500;
            }

            .user-ip {
                font-size: 0.73rem;
            }

            .btn-detail-toggle {
                font-size: 0.78rem;
                font-weight: 500;
                color: #334155;
                background: #ffffff;
                border: 1px solid var(--color-border-input);
                border-radius: 6px;
                padding: 0.25rem 0.65rem;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                transition: all 0.15s ease;
            }

            .btn-detail-toggle:hover {
                background: #f1f5f9;
                border-color: #94a3b8;
                color: #0f172a;
            }

            .btn-detail-toggle.active-toggle {
                background: #0f172a;
                border-color: #0f172a;
                color: #ffffff;
            }

            /* Detail Drawer Container */
            .log-detail-container {
                padding: 1.25rem 1.5rem;
                background: #ffffff;
                border: 1px solid var(--color-border);
                border-radius: 10px;
                margin: 0.75rem 1rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            }

            .detail-summary-bar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                padding-bottom: 0.85rem;
                margin-bottom: 1rem;
                border-bottom: 1px solid var(--color-border-subtle);
                font-size: 0.82rem;
            }

            .summary-left {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
                color: var(--color-text-muted);
            }

            .detail-log-id {
                font-weight: 700;
                color: var(--color-text-title);
            }

            .detail-separator {
                color: #cbd5e1;
            }

            .detail-meta-url {
                max-width: 380px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                color: #475569;
            }

            /* Line Tabs */
            .detail-tabs {
                border-bottom: 1px solid var(--color-border);
                margin-bottom: 1.25rem;
            }

            .detail-tabs .nav-link {
                border: none;
                border-bottom: 2px solid transparent;
                color: var(--color-text-muted);
                font-size: 0.83rem;
                font-weight: 500;
                padding: 0.45rem 0.9rem;
                margin-bottom: -1px;
                background: transparent;
                transition: color 0.15s, border-color 0.15s;
            }

            .detail-tabs .nav-link:hover {
                color: var(--color-text-title);
                border-bottom-color: #cbd5e1;
            }

            .detail-tabs .nav-link.active {
                color: var(--color-primary);
                border-bottom-color: var(--color-primary);
                font-weight: 600;
                background: transparent;
            }

            .tab-count {
                font-size: 0.7rem;
                background: #f1f5f9;
                color: #64748b;
                padding: 0.15rem 0.45rem;
                border-radius: 12px;
                margin-left: 0.35rem;
                font-weight: 600;
            }

            .tab-count-active {
                background: #eff6ff;
                color: var(--color-primary);
            }

            /* Diff Block */
            .diff-block {
                border: 1px solid var(--color-border);
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 1rem;
            }

            .diff-block-header {
                background: #f8fafc;
                padding: 0.55rem 0.9rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid var(--color-border);
            }

            .diff-header-left {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .event-tag {
                font-family: var(--font-mono);
                font-size: 0.7rem;
                font-weight: 600;
                text-transform: uppercase;
                padding: 0.15rem 0.45rem;
                border-radius: 4px;
            }

            .event-create { background: #dcfce7; color: #15803d; }
            .event-update { background: #fef3c7; color: #b45309; }
            .event-delete { background: #fee2e2; color: #b91c1c; }

            .diff-table-name {
                font-weight: 600;
                color: var(--color-text-title);
                font-size: 0.84rem;
            }

            .diff-record-id {
                color: var(--color-text-muted);
                font-size: 0.8rem;
            }

            .diff-view-table {
                font-size: 0.82rem;
            }

            .diff-view-table thead th {
                background: #f8fafc;
                font-size: 0.71rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: var(--color-text-muted);
                padding: 0.5rem 0.9rem;
                border-bottom: 1px solid var(--color-border);
                border-top: none;
            }

            .diff-view-table tbody td {
                padding: 0.55rem 0.9rem;
                border-top: 1px solid var(--color-border-subtle);
                vertical-align: middle;
            }

            .diff-old {
                background: #fff5f5;
            }

            .diff-new {
                background: #f0fdf4;
            }

            .diff-null {
                color: #94a3b8;
                font-style: italic;
            }

            .diff-code-inline {
                background: transparent;
                padding: 0;
                font-size: 0.76rem;
                white-space: pre-wrap;
                word-break: break-all;
            }

            /* Key Value Panels */
            .panel-box {
                border: 1px solid var(--color-border);
                border-radius: 8px;
                overflow: hidden;
            }

            .panel-box-header {
                background: #f8fafc;
                padding: 0.55rem 0.9rem;
                border-bottom: 1px solid var(--color-border);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .panel-box-title {
                font-size: 0.82rem;
                font-weight: 600;
                color: #1e293b;
            }

            .kv-table td {
                padding: 0.55rem 0.9rem;
                font-size: 0.82rem;
                border-top: 1px solid var(--color-border-subtle);
            }

            .kv-key {
                width: 25%;
                font-weight: 500;
                color: var(--color-text-muted);
            }

            .code-box-light {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 0.5rem 0.75rem;
                font-size: 0.78rem;
                color: #0f172a;
                max-height: 220px;
                overflow-y: auto;
            }

            .code-box-editor {
                background: #0f172a;
                color: #e2e8f0;
                border-radius: 8px;
                padding: 0.85rem 1.15rem;
                font-size: 0.8rem;
                max-height: 350px;
                overflow-y: auto;
            }

            .query-list {
                divide-y: 1px solid #f1f5f9;
            }

            .query-item {
                padding: 0.75rem 0.9rem;
                border-bottom: 1px solid var(--color-border-subtle);
            }

            .query-item:last-child {
                border-bottom: none;
            }

            .query-item-meta {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 0.35rem;
                font-size: 0.75rem;
            }

            .query-index {
                color: var(--color-text-muted);
            }

            .query-time {
                color: #059669;
                font-weight: 600;
            }

            .query-slow {
                color: #dc2626;
            }

            .query-code {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 0.5rem 0.75rem;
                font-size: 0.78rem;
                color: #1e293b;
                white-space: pre-wrap;
                word-break: break-all;
            }

            .detail-empty-panel {
                padding: 2.5rem;
                text-align: center;
                background: #f8fafc;
                border: 1px dashed var(--color-border);
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="nav-inner">
                <div class="nav-brand-group">
                    <div class="nav-brand-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div>
                        <h1 class="nav-title">Database Logging</h1>
                    </div>
                    <span class="nav-badge">Audit Viewer</span>
                </div>
            </div>
        </nav>

        <!-- Main Content Container -->
        <div class="main-content">
            <!-- Filter Toolbar Card -->
            <div class="card-panel">
                <div class="filter-body">
                    <form id="form-search" data-url="{{ route('database-logging.datatable') }}" onsubmit="return false;">
                        <div class="form-row">
                            <!-- 1. Keyword / Search -->
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label for="filter-search" class="filter-label">Keyword / Search</label>
                                <input type="text" class="form-control form-control-custom" id="filter-search" placeholder="Path, user, IP, payload...">
                            </div>

                            <!-- 2. User / Actor -->
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label for="filter-user" class="filter-label">User / Actor</label>
                                <select class="form-control form-control-custom" name="user" id="filter-user">
                                    <option value="">- All Users -</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->loggable_type }}|{{ $user->loggable_id }}">
                                            {{ $user->name ?: ($user->user_name ?: 'User #' . $user->loggable_id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 3. Model / Table -->
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label for="filter-model" class="filter-label">Model / Table</label>
                                <select class="form-control form-control-custom" name="model" id="filter-model">
                                    <option value="">- All Models / Tables -</option>
                                    @foreach($tables as $tableName => $tableLabel)
                                        <option value="{{ $tableName }}">
                                            {{ $tableLabel }} ({{ $tableName }})
                                        </option>
                                    @endforeach
                                    <option value="__custom__">Custom Model...</option>
                                </select>
                                <input type="text" id="filter-model-custom" class="form-control form-control-custom mt-2 d-none" placeholder="Enter custom model class or table...">
                            </div>

                            <!-- 4. Model ID -->
                            <div class="col-lg-1 col-md-6 mb-2">
                                <label for="filter-model-id" class="filter-label">Model ID</label>
                                <input type="text" class="form-control form-control-custom font-monospace" name="model_id" id="filter-model-id" placeholder="ID / UUID">
                            </div>

                            <!-- 5. Method -->
                            <div class="col-lg-1 col-md-6 mb-2">
                                <label for="filter-method" class="filter-label">Method</label>
                                <select class="form-control form-control-custom font-monospace" name="method" id="filter-method">
                                    <option value="">All</option>
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="PATCH">PATCH</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>

                            <!-- 6. Date Range -->
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label for="filter-period" class="filter-label">Date Range</label>
                                <select class="form-control form-control-custom" id="filter-period">
                                    <option value="today" selected>Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="7days">Last 7 Days</option>
                                    <option value="30days">Last 30 Days</option>
                                    <option value="all">All Time</option>
                                    <option value="custom">Custom...</option>
                                </select>
                            </div>

                            <!-- Custom Date Range (collapsible) -->
                            <div class="col-12 mt-2 pt-2 border-top d-none" id="custom-date-container">
                                <div class="d-flex flex-wrap align-items-center bg-light p-2 rounded">
                                    <span class="small font-weight-bold text-muted mr-3 mb-1 mb-md-0">Custom Range:</span>
                                    <div class="d-flex align-items-center mr-3 mb-1 mb-md-0">
                                        <span class="small text-muted mr-2">From:</span>
                                        <input type="date" class="form-control form-control-custom form-control-sm" id="start-date" value="{{ now()->format('Y-m-d') }}" style="width: 150px;">
                                    </div>
                                    <div class="d-flex align-items-center mr-3 mb-1 mb-md-0">
                                        <span class="small text-muted mr-2">To:</span>
                                        <input type="date" class="form-control form-control-custom form-control-sm" id="end-date" value="{{ now()->format('Y-m-d') }}" style="width: 150px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center justify-content-end mt-3 pt-3 border-top" style="gap: 0.5rem;">
                            <button type="button" class="btn-action-quiet" id="btn-reset">
                                Reset
                            </button>
                            <button type="button" class="btn-action-primary" id="btn-filter">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card-panel">
                <table class="table table-hover mb-0" id="logs-table">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="width: 50px;">Method</th>
                            <th>Path & Host</th>
                            <th style="width: 200px;">Actor</th>
                            <th style="width: 180px;" class="text-nowrap">Timestamp</th>
                            <th style="width: 85px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        <script>
            $(document).ready(function () {
                // Initialize Select2 with search
                $('#filter-user').select2({
                    placeholder: '- All Users -',
                    allowClear: true,
                    width: '100%'
                });

                $('#filter-model').select2({
                    placeholder: '- All Models / Tables -',
                    allowClear: true,
                    width: '100%'
                });

                function escapeHtml(text) {
                    if (text === null || text === undefined) return '';
                    return String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                // Initialize DataTables
                var table = $('#logs-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ordering: false,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    dom: '<"table-responsive"t><"d-flex flex-wrap align-items-center justify-content-between p-3 border-top"lip>',
                    ajax: {
                        url: $('#form-search').data('url'),
                        data: function (d) {
                            d.user = $('#filter-user').val();
                            
                            var modelVal = $('#filter-model').val();
                            if (modelVal === '__custom__') {
                                modelVal = $('#filter-model-custom').val();
                            }
                            d.model = modelVal;
                            d.model_id = $('#filter-model-id').val();
                            d.method = $('#filter-method').val();
                            d.start_date = $('#start-date').val();
                            d.end_date = $('#end-date').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center font-monospace text-muted' },
                        {
                            data: 'method',
                            name: 'method',
                            render: function (data) {
                                var method = escapeHtml(data || 'GET').toUpperCase();
                                var methodLower = method.toLowerCase();
                                return '<span class="method-tag method-' + methodLower + '">' + method + '</span>';
                            }
                        },
                        {
                            data: 'path',
                            name: 'path',
                            render: function (data, type, row) {
                                var path = escapeHtml(data || '/');
                                var host = escapeHtml(row.host || '');
                                var cleanPath = path.charAt(0) === '/' ? path : '/' + path;
                                var fullUrl = (host ? host : '') + cleanPath;
                                return '<div class="path-cell" title="' + fullUrl + '">' +
                                    '<span class="path-main font-monospace">' + cleanPath + '</span>' +
                                    '<span class="path-host text-muted">' + host + '</span>' +
                                '</div>';
                            }
                        },
                        {
                            data: 'user',
                            name: 'user',
                            render: function (data, type, row) {
                                var userName = escapeHtml(data || 'Guest');
                                var ip = escapeHtml(row.ip_address || '-');
                                var userClass = data ? 'text-dark font-weight-500' : 'text-muted font-italic';
                                return '<div class="user-cell">' +
                                    '<div class="user-name ' + userClass + '" title="' + userName + '">' + userName + '</div>' +
                                    '<div class="user-ip font-monospace text-muted">' + ip + '</div>' +
                                '</div>';
                            }
                        },
                        {
                            data: 'date',
                            name: 'date',
                            className: 'text-nowrap font-monospace',
                            render: function (data) {
                                return '<span class="font-monospace text-nowrap text-dark" style="font-size: 0.82rem;">' + escapeHtml(data || '-') + '</span>';
                            }
                        },
                        {
                            data: 'id',
                            name: 'action',
                            className: 'text-center',
                            orderable: false,
                            render: function (data) {
                                return '<button type="button" class="btn btn-sm btn-detail-toggle" data-row-id="' + data + '">' +
                                    '<span>Detail</span>' +
                                    '<i class="fas fa-chevron-right toggle-icon ml-1"></i>' +
                                '</button>';
                            }
                        },
                    ],
                    language: {
                        processing: '<div class="py-2 px-3 d-flex align-items-center justify-content-center text-muted font-weight-500" style="gap: 0.5rem;"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span>Loading...</span></div>',
                        emptyTable: '<div class="py-5 text-center text-muted"><p class="mb-0 font-weight-500">No log records found matching your filters.</p></div>',
                    }
                });

                // Detail Toggle Button Click Handler (Accordion: single row open)
                $('#logs-table tbody').on('click', '.btn-detail-toggle', function (e) {
                    e.stopPropagation();
                    var tr = $(this).closest('tr');
                    var row = table.row(tr);
                    var $btn = $(this);
                    var icon = $btn.find('.toggle-icon');

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown details-open');
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                        $btn.removeClass('active-toggle');
                    } else {
                        // Close any other open rows
                        table.rows().every(function () {
                            if (this.child.isShown()) {
                                this.child.hide();
                                var $otherTr = $(this.node());
                                $otherTr.removeClass('shown details-open');
                                $otherTr.find('.btn-detail-toggle')
                                    .removeClass('active-toggle')
                                    .find('.toggle-icon')
                                    .removeClass('fa-chevron-down')
                                    .addClass('fa-chevron-right');
                            }
                        });

                        var rowData = row.data();
                        if (rowData && rowData.details) {
                            row.child(rowData.details, 'p-0 detail-row-container').show();
                            tr.addClass('shown details-open');
                            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                            $btn.addClass('active-toggle');
                        }
                    }
                });

                // Row click to toggle detail
                $('#logs-table tbody').on('click', 'tr', function (e) {
                    if ($(e.target).closest('button, a, input, select, textarea, pre, code, .copy-btn, .nav-tabs, .tab-pane').length) {
                        return;
                    }
                    if ($(this).hasClass('detail-row-container') || $(this).closest('.detail-row-container').length) {
                        return;
                    }
                    $(this).find('.btn-detail-toggle').trigger('click');
                });

                // Apply Filters on Demand
                function applyFilters() {
                    table.search($('#filter-search').val());
                    table.ajax.reload(null, true);
                }

                $('#btn-filter').on('click', function () {
                    applyFilters();
                });

                $('#form-search').on('submit', function (e) {
                    e.preventDefault();
                    applyFilters();
                });

                $('#filter-search, #filter-model-id, #filter-model-custom').on('keypress', function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        applyFilters();
                    }
                });

                // Custom Model Input Toggle (no auto reload)
                $('#filter-model').on('change', function () {
                    if ($(this).val() === '__custom__') {
                        $('#filter-model-custom').removeClass('d-none').focus();
                    } else {
                        $('#filter-model-custom').addClass('d-none').val('');
                    }
                });

                // Date Range Period Dropdown (sets dates, no auto reload)
                var formatDate = function (d) {
                    var month = '' + (d.getMonth() + 1),
                        day = '' + d.getDate(),
                        year = d.getFullYear();
                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;
                    return [year, month, day].join('-');
                };

                $('#filter-period').on('change', function () {
                    var period = $(this).val();
                    var today = new Date();

                    if (period === 'today') {
                        $('#custom-date-container').addClass('d-none');
                        $('#start-date').val(formatDate(today));
                        $('#end-date').val(formatDate(today));
                    } else if (period === 'yesterday') {
                        $('#custom-date-container').addClass('d-none');
                        var y = new Date();
                        y.setDate(today.getDate() - 1);
                        $('#start-date').val(formatDate(y));
                        $('#end-date').val(formatDate(y));
                    } else if (period === '7days') {
                        $('#custom-date-container').addClass('d-none');
                        var d7 = new Date();
                        d7.setDate(today.getDate() - 7);
                        $('#start-date').val(formatDate(d7));
                        $('#end-date').val(formatDate(today));
                    } else if (period === '30days') {
                        $('#custom-date-container').addClass('d-none');
                        var d30 = new Date();
                        d30.setDate(today.getDate() - 30);
                        $('#start-date').val(formatDate(d30));
                        $('#end-date').val(formatDate(today));
                    } else if (period === 'all') {
                        $('#custom-date-container').addClass('d-none');
                        $('#start-date').val('');
                        $('#end-date').val('');
                    } else if (period === 'custom') {
                        $('#custom-date-container').removeClass('d-none');
                    }
                });

                // Reset Filters
                $('#btn-reset').on('click', function () {
                    $('#filter-search').val('');
                    $('#filter-user').val('').trigger('change.select2');
                    $('#filter-model').val('').trigger('change.select2');
                    $('#filter-model-custom').val('').addClass('d-none');
                    $('#filter-model-id').val('');
                    $('#filter-method').val('');
                    $('#filter-period').val('today');
                    $('#start-date').val('{{ now()->format("Y-m-d") }}');
                    $('#end-date').val('{{ now()->format("Y-m-d") }}');
                    $('#custom-date-container').addClass('d-none');
                    
                    table.search('');
                    table.ajax.reload(null, true);
                });

                // Copy to Clipboard Handler
                $(document).on('click', '.copy-btn', function (e) {
                    e.stopPropagation();
                    var textToCopy = $(this).attr('data-clipboard');
                    if (!textToCopy) {
                        var targetCode = $(this).closest('.panel-box, .query-item').find('pre code');
                        if (targetCode.length) {
                            textToCopy = targetCode.text();
                        }
                    }
                    if (!textToCopy) return;

                    var btn = $(this);
                    var originalHtml = btn.html();

                    function showCopied() {
                        btn.html('<i class="fas fa-check text-success mr-1"></i> Copied');
                        setTimeout(function () {
                            btn.html(originalHtml);
                        }, 1400);
                    }

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(textToCopy).then(showCopied).catch(function () {
                            fallbackCopy(textToCopy, showCopied);
                        });
                    } else {
                        fallbackCopy(textToCopy, showCopied);
                    }
                });

                function fallbackCopy(text, callback) {
                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        callback();
                    } catch (err) {}
                    document.body.removeChild(textArea);
                }
            });
        </script>
    </body>
</html>