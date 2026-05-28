<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flat Management</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Header */

        .flats-header {
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            border-radius: 18px;
            padding: 30px;
            color: #fff;
            margin-bottom: 25px;
        }

        .flats-header h2 {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .btn-add-flat {
            background: #fff;
            color: #1e3a8a;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-add-flat:hover {
            background: #dbeafe;
            transform: translateY(-2px);
        }

        /* Stats Cards */

        .stats-card {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-4px);
        }

        .stats-title {
            color: #6b7280;
            font-size: 14px;
        }

        .stats-value {
            font-size: 32px;
            font-weight: 700;
            margin-top: 10px;
        }

        /* Filter Card */

        .filter-card {
            background: #fff;
            border-radius: 18px;
            padding: 25px;
            margin-top: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .filter-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Table */

        .table-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .table-card .card-header {
            background: #fff;
            padding: 18px 24px;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }

        table thead {
            background: #eff6ff;
        }

        table thead th {
            color: #1e3a8a !important;
            font-weight: 700;
            border: none !important;
        }

        table tbody tr:hover {
            background: #f9fafb;
        }

        /* Status Badges */

        .badge-status {
            padding: 8px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }

        .occupied {
            background: #dcfce7;
            color: #166534;
        }

        .vacant {
            background: #fee2e2;
            color: #991b1b;
        }

        .maintenance {
            background: #fef3c7;
            color: #92400e;
        }

        /* Responsive */

        @media(max-width:768px) {
            .flats-header {
                text-align: center;
            }

            .btn-add-flat {
                width: 100%;
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <!-- Header -->

        <div class="flats-header d-flex justify-content-between align-items-center flex-wrap">

            <div>
                <h2>Flat Management</h2>
                <p class="mb-0">
                    Manage all society flats, owners and occupancy details.
                </p>
            </div>

            <button class="btn-add-flat">
                + Add New Flat
            </button>

        </div>

        <!-- Stats -->

        <div class="row g-4">

            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-title">
                        Total Flats
                    </div>

                    <div class="stats-value">
                        120
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-title">
                        Occupied Flats
                    </div>

                    <div class="stats-value text-success">
                        95
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-title">
                        Vacant Flats
                    </div>

                    <div class="stats-value text-danger">
                        15
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-title">
                        Maintenance
                    </div>

                    <div class="stats-value text-warning">
                        10
                    </div>
                </div>
            </div>

        </div>

        <!-- Filters -->

        <div class="filter-card">

            <div class="filter-title">
                Filter Flats
            </div>

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">
                        Block
                    </label>

                    <select class="form-select">

                        <option>
                            All Blocks
                        </option>

                        <option>
                            Block A
                        </option>

                        <option>
                            Block B
                        </option>

                        <option>
                            Block C
                        </option>

                    </select>
                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Status
                    </label>

                    <select class="form-select">

                        <option>
                            All Status
                        </option>

                        <option>
                            Occupied
                        </option>

                        <option>
                            Vacant
                        </option>

                        <option>
                            Maintenance
                        </option>

                    </select>

                </div>

                <div class="col-md-4 d-flex align-items-end">

                    <button class="btn btn-outline-primary w-100">
                        Reset Filters
                    </button>

                </div>

            </div>

        </div>

        <!-- Table -->

        <div class="card table-card border-0">

            <div class="card-header">
                Flats List
            </div>

            <div class="card-body table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Flat No</th>
                            <th>Block</th>
                            <th>Owner Name</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr>
                            <td>1</td>
                            <td>A-101</td>
                            <td>A</td>
                            <td>Arpit Patel</td>
                            <td>
                                <span class="badge-status occupied">
                                    Occupied
                                </span>
                            </td>
                            <td>4</td>

                            <td>
                                <button class="btn btn-sm btn-primary">
                                    Edit
                                </button>

                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>B-204</td>
                            <td>B</td>
                            <td>Raj Shah</td>
                            <td>
                                <span class="badge-status vacant">
                                    Vacant
                                </span>
                            </td>
                            <td>0</td>

                            <td>
                                <button class="btn btn-sm btn-primary">
                                    Edit
                                </button>

                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>3</td>
                            <td>C-305</td>
                            <td>C</td>
                            <td>Meet Joshi</td>
                            <td>
                                <span class="badge-status maintenance">
                                    Maintenance
                                </span>
                            </td>
                            <td>2</td>

                            <td>
                                <button class="btn btn-sm btn-primary">
                                    Edit
                                </button>

                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</body>

</html>
