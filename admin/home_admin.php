<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}
$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// --- 1. HITUNG STATISTIK ---
$q_dosen = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='dosen'");
$total_dosen = ($q_dosen) ? mysqli_fetch_assoc($q_dosen)['total'] : 0;

$q_mhs = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='mahasiswa'");
$total_mhs = ($q_mhs) ? mysqli_fetch_assoc($q_mhs)['total'] : 0;

$q_skripsi = mysqli_query($conn, "SELECT COUNT(*) as total FROM skripsi");
$total_skripsi = ($q_skripsi) ? mysqli_fetch_assoc($q_skripsi)['total'] : 0;

// --- 2. DATA GRAFIK ---
$labels = [];
$data_grafik = [];
$q_grafik = mysqli_query($conn, "SELECT prodi, COUNT(*) as jumlah FROM data_mahasiswa GROUP BY prodi");
if ($q_grafik) {
    while($row = mysqli_fetch_assoc($q_grafik)) {
        $labels[] = $row['prodi'];
        $data_grafik[] = $row['jumlah'];
    }
}

// --- 3. LOGIKA KALENDER ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

$jadwal_sql = "SELECT tanggal, ruang, status FROM ujian_skripsi 
               WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'";
$q_jadwal = mysqli_query($conn, $jadwal_sql);

$events = [];
if ($q_jadwal) {
    while ($row = mysqli_fetch_assoc($q_jadwal)) {
        $events[$row['tanggal']][] = $row;
    }
}

function build_calendar($month, $year, $events) {
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $dayOfWeek = $dateComponents['wday'];
    $monthName = date('F', $firstDayOfMonth);
    
    $calendar = "<div class='calendar-header mb-3 fw-bold text-center'>$monthName $year</div>";
    $calendar .= "<table class='table table-bordered table-sm text-center calendar-table'>";
    $calendar .= "<thead class='table-light'><tr>";
    $calendar .= "<th class='text-danger'>M</th><th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th>";
    $calendar .= "</tr></thead><tbody><tr>";

    if ($dayOfWeek > 0) { 
        for ($k = 0; $k < $dayOfWeek; $k++) { $calendar .= "<td class='bg-light'></td>"; }
    }
    
    $currentDay = 1;
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while ($currentDay <= $numberDays) {
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
        
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";
        $today = date('Y-m-d');
        
        $hasEvent = isset($events[$date]);
        $isToday = ($date == $today) ? "bg-primary text-white fw-bold" : "";
        $eventDot = $hasEvent ? "<div class='event-dot'></div>" : "";
        
        // --- LOGIKA KLIK POPUP ---
        $onclick = "";
        $cursorStyle = "cursor:default;";
        
        if ($hasEvent) {
            // Encode data jadwal ke JSON agar bisa dibaca Javascript
            // Kita simpan list ruangan di dalam atribut data-jadwal
            $jsonDetails = htmlspecialchars(json_encode($events[$date]), ENT_QUOTES, 'UTF-8');
            $onclick = "onclick=\"showSchedule('$date', $jsonDetails)\"";
            $cursorStyle = "cursor:pointer;"; // Ubah kursor jadi tangan
        }

        $calendar .= "<td class='$isToday position-relative' $onclick style='$cursorStyle height: 50px; vertical-align: middle;'>
                        <span>$currentDay</span>
                        $eventDot
                      </td>";
        
        $currentDay++;
        $dayOfWeek++;
    }
    
    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        for ($l = 0; $l < $remainingDays; $l++) { $calendar .= "<td class='bg-light'></td>"; } 
    }
    
    $calendar .= "</tr></tbody></table>";
    return $calendar;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Statistik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* --- LAYOUT --- */
    body { display: flex; flex-direction: column; height: 100vh; overflow: hidden; background-color: #f4f6f9; margin: 0; }
    .header { height: 70px; width: 100%; background: #fff; border-bottom: 1px solid #dee2e6; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; flex-shrink: 0; position: relative; z-index: 9999; }
    .layout-wrapper { display: flex; flex: 1; overflow: hidden; width: 100%; }
    .sidebar-area { background-color: #343a40; overflow-y: auto; height: 100%; flex-shrink: 0; }
    .main-content { flex: 1; padding: 30px; overflow-y: auto; height: 100%; }
    
    .stat-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    
    /* Calendar Styles */
    .calendar-table th { font-size: 12px; }
    .calendar-table td { font-size: 14px; position: relative; }
    .calendar-table td:hover { background-color: #e9ecef; } /* Efek hover */
    .event-dot { width: 6px; height: 6px; background-color: #dc3545; border-radius: 50%; margin: 2px auto 0 auto; }
    .legend-item { font-size: 11px; color: #6c757d; }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 50px;">
        <h4 class="m-0 ms-2 text-dark">DASHBOARD UTAMA</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end"><small class="d-block text-muted">Admin</small><b><?= htmlspecialchars($nama_admin) ?></b></div>
        <div style="width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; justify-content: center; align-items: center;">👤</div>
    </div>
</div>

<div class="layout-wrapper">
    <div class="sidebar-area">
        <?php $page = 'home_admin'; include "../templates/sidebar_admin.php"; ?>
    </div>

    <div class="main-content">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">Jumlah Dosen</p><h2 class="fw-bold m-0"><?= $total_dosen ?></h2></div>
                        <div class="icon-box bg-primary text-white">👨‍🏫</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">Jumlah Mahasiswa</p><h2 class="fw-bold m-0"><?= $total_mhs ?></h2></div>
                        <div class="icon-box bg-info text-white">🎓</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-1">Judul Skripsi Aktif</p><h2 class="fw-bold m-0"><?= $total_skripsi ?></h2></div>
                        <div class="icon-box bg-success text-white">📚</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card stat-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <h5 class="m-0">Statistik Prodi</h5>
                    </div>
                    <div style="flex: 1; min-height: 300px; position: relative;">
                        <canvas id="prodiChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card p-4 h-100">
                    <h5 class="mb-3">Jadwal Ujian Skripsi</h5>
                    
                    <?= build_calendar($bulan_ini, $tahun_ini, $events); ?>
                    
                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2 legend-item">
                            <div style="width: 10px; height: 10px; background:#0d6efd;"></div> Hari Ini
                        </div>
                        <div class="d-flex align-items-center gap-2 legend-item">
                            <div style="width: 6px; height: 6px; background:#dc3545; border-radius:50%;"></div> Ada Jadwal (Klik tanggal untuk detail)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">📅 Jadwal Ujian: <span id="modalDate"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="scheduleList">
           </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Chart JS Config
  const ctx = document.getElementById('prodiChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Mahasiswa',
        data: <?= json_encode($data_grafik) ?>,
        backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'],
        borderWidth: 1
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  // FUNCTION UNTUK MENAMPILKAN MODAL
  function showSchedule(dateStr, eventsArray) {
      // 1. Set Judul Tanggal (Format jadi Tgl-Bln-Thn)
      const dateObj = new Date(dateStr);
      const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
      document.getElementById('modalDate').innerText = formattedDate;

      // 2. Buat List Ruangan HTML
      let listHtml = '';
      if (eventsArray.length > 0) {
          eventsArray.forEach(item => {
              listHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-primary">🚪 Ruang: ${item.ruang}</span><br>
                        <small class="text-muted">Status: ${item.status}</small>
                    </div>
                </li>
              `;
          });
      } else {
          listHtml = '<li class="list-group-item text-center">Tidak ada jadwal detail.</li>';
      }

      // 3. Masukkan ke Modal dan Tampilkan
      document.getElementById('scheduleList').innerHTML = listHtml;
      const myModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
      myModal.show();
  }
</script>

</body>
</html>