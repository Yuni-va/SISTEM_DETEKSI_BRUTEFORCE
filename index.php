<?php

require_once "config/database.php";
require_once "app/models/ActivityLog.php";

$model = new ActivityLog($conn);

$search = $_GET['search'] ?? "";

if($search!=""){
    $data = $model->search($search);
}else{
    $data = $model->getAll();
}

$total = $model->total();
$today = $model->today();
$week  = $model->week();

?>

<div class="row mb-4">

    <div class="col-md-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <h6>Total Activity</h6>
                <h2><?= $total ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <h6>Hari Ini</h6>
                <h2><?= $today ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-danger text-white shadow">
            <div class="card-body">
                <h6>Minggu Ini</h6>
                <h2><?= $week ?></h2>
            </div>
        </div>
    </div>

</div>

<div class="card shadow">

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i>
            Activity Logs
        </h5>

        <a href="activity_logs.php" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </a>

    </div>

    <div class="card-body">

        <form method="GET" class="mb-3">

            <div class="input-group">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari User / Aktivitas / IP Address..."
                    value="<?= htmlspecialchars($search) ?>">

                <button class="btn btn-primary">
                    <i class="bi bi-search"></i>
                    Cari
                </button>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>
                        <th width="70">No</th>
                        <th>User</th>
                        <th>Username</th>
                        <th>Aktivitas</th>
                        <th>IP Address</th>
                        <th>Waktu</th>
                    </tr>

                </thead>

                <tbody>

                <?php

                $no=1;

                while($row=mysqli_fetch_assoc($data)){

                ?>

                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['fullName'] ?? "-") ?></td>
                        <td><?= htmlspecialchars($row['username'] ?? "-") ?></td>
                        <td><?= htmlspecialchars($row['activity']) ?></td>
                        <td><?= htmlspecialchars($row['ip_address']) ?></td>
                        <td><?= date('d-m-Y H:i:s',strtotime($row['created_at'])) ?></td>
                    </tr>

                <?php } ?>

                <?php if(mysqli_num_rows($data)===0){ ?>

                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Data activity logs belum tersedia.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
