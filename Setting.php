<?php

class Setting
{
    private $conn;

    private $defaultMenus = [
        [
            "menu_key" => "dashboard",
            "label" => "Dashboard",
            "url" => "dashboard.php",
            "icon" => "bi bi-speedometer2",
            "sort_order" => 1
        ],
        [
            "menu_key" => "users",
            "label" => "Users",
            "url" => "users.php",
            "icon" => "bi bi-people-fill",
            "sort_order" => 2
        ],
        [
            "menu_key" => "login_attempts",
            "label" => "Login Attempts",
            "url" => "login_attempts.php",
            "icon" => "bi bi-box-arrow-in-right",
            "sort_order" => 3
        ],
        [
            "menu_key" => "blocked_ips",
            "label" => "Blocked IP",
            "url" => "blocked_ips.php",
            "icon" => "bi bi-ban",
            "sort_order" => 4
        ],
        [
            "menu_key" => "threshold_logs",
            "label" => "Threshold Logs",
            "url" => "threshold_logs.php",
            "icon" => "bi bi-sliders",
            "sort_order" => 5
        ],
        [
            "menu_key" => "detection_logs",
            "label" => "Detection Logs",
            "url" => "detection_logs.php",
            "icon" => "bi bi-shield-exclamation",
            "sort_order" => 6
        ],
        [
            "menu_key" => "activity_logs",
            "label" => "Activity Logs",
            "url" => "activity_logs.php",
            "icon" => "bi bi-clock-history",
            "sort_order" => 7
        ],
        [
            "menu_key" => "reports",
            "label" => "Reports",
            "url" => "reports.php",
            "icon" => "bi bi-file-earmark-bar-graph",
            "sort_order" => 8
        ],
        [
            "menu_key" => "settings",
            "label" => "Settings",
            "url" => "settings.php",
            "icon" => "bi bi-gear-fill",
            "sort_order" => 9
        ]
    ];

    private $defaultSystemSettings = [
        "threshold_limit" => "5",
        "detection_window_minutes" => "1",
        "block_minutes" => "15",
        "api_token" => "",
        "log_retention_days" => "90",
        "auto_cleanup_enabled" => "1",
        "last_cleanup_date" => ""
    ];

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->ensureMenuTable();
        $this->ensureSystemSettingsTable();
        $this->syncDefaultMenus();
        $this->syncDefaultSystemSettings();
        $this->ensureUserStatusSchema();
        $this->ensureBlockedIpsSchema();
        $this->ensurePerformanceIndexes();
        $this->maybeRunAutoCleanup();
    }

    private function ensureMenuTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS menu_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    menu_key VARCHAR(50) NOT NULL UNIQUE,
                    label VARCHAR(100) NOT NULL,
                    url VARCHAR(150) NOT NULL,
                    icon VARCHAR(100) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    sort_order INT NOT NULL DEFAULT 0,
                    updated_at DATETIME NULL
                )";

        mysqli_query($this->conn,$sql);
    }

    private function syncDefaultMenus()
    {
        $sql = "INSERT INTO menu_settings
                (
                    menu_key,
                    label,
                    url,
                    icon,
                    is_active,
                    sort_order,
                    updated_at
                )
                VALUES
                (
                    ?,?,?,?,?,?,NOW()
                )
                ON DUPLICATE KEY UPDATE
                    label=VALUES(label),
                    url=VALUES(url),
                    icon=VALUES(icon),
                    sort_order=VALUES(sort_order)";

        $stmt = $this->conn->prepare($sql);

        foreach($this->defaultMenus as $menu){
            $active = 1;

            $stmt->bind_param(
                "ssssii",
                $menu['menu_key'],
                $menu['label'],
                $menu['url'],
                $menu['icon'],
                $active,
                $menu['sort_order']
            );

            $stmt->execute();
        }
    }

    private function ensureSystemSettingsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
                    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                    setting_value TEXT NULL,
                    updated_at DATETIME NULL
                )";

        mysqli_query($this->conn,$sql);
    }

    private function syncDefaultSystemSettings()
    {
        $sql = "INSERT INTO system_settings
                (
                    setting_key,
                    setting_value,
                    updated_at
                )
                VALUES
                (
                    ?,?,NOW()
                )
                ON DUPLICATE KEY UPDATE
                    setting_key=VALUES(setting_key)";

        $stmt = $this->conn->prepare($sql);

        foreach($this->defaultSystemSettings as $key => $value){
            if($key === "api_token"){
                $value = bin2hex(random_bytes(16));
            }

            $stmt->bind_param("ss",$key,$value);
            $stmt->execute();
        }
    }

    private function tableExists($table)
    {
        $table = $this->conn->real_escape_string($table);
        $result = mysqli_query($this->conn,"SHOW TABLES LIKE '".$table."'");

        return $result && mysqli_num_rows($result) > 0;
    }

    private function indexExists($table,$index)
    {
        if(!$this->tableExists($table)){
            return false;
        }

        $table = $this->conn->real_escape_string($table);
        $index = $this->conn->real_escape_string($index);
        $result = mysqli_query($this->conn,"SHOW INDEX FROM ".$table." WHERE Key_name='".$index."'");

        return $result && mysqli_num_rows($result) > 0;
    }

    private function columnExists($table,$column)
    {
        if(!$this->tableExists($table)){
            return false;
        }

        $table = $this->conn->real_escape_string($table);
        $column = $this->conn->real_escape_string($column);
        $result = mysqli_query($this->conn,"SHOW COLUMNS FROM ".$table." LIKE '".$column."'");

        return $result && mysqli_num_rows($result) > 0;
    }

    private function addColumnIfMissing($table,$column,$definition)
    {
        if(!$this->tableExists($table) || $this->columnExists($table,$column)){
            return;
        }

        mysqli_query($this->conn,"ALTER TABLE ".$table." ADD COLUMN ". $definition);
    }

    private function addIndexIfMissing($table,$index,$columns)
    {
        if(!$this->tableExists($table) || $this->indexExists($table,$index)){
            return;
        }

        mysqli_query($this->conn,"ALTER TABLE ".$table." ADD INDEX ".$index." (".$columns.")");
    }
    private function ensureUserStatusSchema()
    {
        $this->addColumnIfMissing(
            "users",
            "status",
            "status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER role"
        );
    }
    private function ensureBlockedIpsSchema()
    {
        $this->addColumnIfMissing(
            "blocked_ips",
            "blocked_until",
            "blocked_until TIMESTAMP NULL AFTER blocked_at"
        );
    }

    private function ensurePerformanceIndexes()
    {
        $this->addIndexIfMissing(
            "login_attempts",
            "idx_login_attempts_ip_status_time",
            "ip_address,status,attempt_time"
        );

        $this->addIndexIfMissing(
            "blocked_ips",
            "idx_blocked_ips_ip_status_until",
            "ip_address,status,blocked_until"
        );

        $this->addIndexIfMissing(
            "detection_logs",
            "idx_detection_logs_detected_at",
            "detected_at"
        );

        $this->addIndexIfMissing(
            "threshold_logs",
            "idx_threshold_logs_created_at",
            "created_at"
        );
    }

    private function maybeRunAutoCleanup()
    {
        if((int) $this->getSetting("auto_cleanup_enabled","1") !== 1){
            return;
        }

        $today = date("Y-m-d");

        if($this->getSetting("last_cleanup_date","") === $today){
            return;
        }

        $this->archiveOldLogs((int) $this->getSetting("log_retention_days","90"));
        $this->setSetting("last_cleanup_date",$today);
    }

    public function getMenus()
    {
        $sql = "SELECT *
                FROM menu_settings
                ORDER BY sort_order ASC";

        return mysqli_query($this->conn,$sql);
    }

    public function getActiveMenus()
    {
        $sql = "SELECT *
                FROM menu_settings
                WHERE is_active=1
                ORDER BY sort_order ASC";

        return mysqli_query($this->conn,$sql);
    }

    public function updateMenus($activeMenus)
    {
        $sql = "UPDATE menu_settings
                SET is_active=?,
                    updated_at=NOW()
                WHERE menu_key=?";

        $stmt = $this->conn->prepare($sql);

        foreach($this->defaultMenus as $menu){
            $isActive = in_array($menu['menu_key'],$activeMenus) ? 1 : 0;

            if($menu['menu_key']=="settings"){
                $isActive = 1;
            }

            $stmt->bind_param(
                "is",
                $isActive,
                $menu['menu_key']
            );

            $stmt->execute();
        }

        return true;
    }

    public function getSetting($key,$default="")
    {
        $sql = "SELECT setting_value
                FROM system_settings
                WHERE setting_key=?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s",$key);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        if(!$row || $row['setting_value'] === null || $row['setting_value'] === ""){
            return $default;
        }

        return $row['setting_value'];
    }

    public function setSetting($key,$value)
    {
        $sql = "INSERT INTO system_settings
                (
                    setting_key,
                    setting_value,
                    updated_at
                )
                VALUES
                (
                    ?,?,NOW()
                )
                ON DUPLICATE KEY UPDATE
                    setting_value=VALUES(setting_value),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss",$key,$value);

        return $stmt->execute();
    }

    public function securityConfig()
    {
        return [
            "threshold_limit" => max(1,(int) $this->getSetting("threshold_limit","5")),
            "detection_window_minutes" => max(1,(int) $this->getSetting("detection_window_minutes","1")),
            "block_minutes" => max(1,(int) $this->getSetting("block_minutes","15")),
            "api_token" => $this->getSetting("api_token",""),
            "log_retention_days" => max(1,(int) $this->getSetting("log_retention_days","90")),
            "auto_cleanup_enabled" => (int) $this->getSetting("auto_cleanup_enabled","1")
        ];
    }

    public function updateSecurityConfig($data)
    {
        $thresholdLimit = max(1,(int) ($data['threshold_limit'] ?? 5));
        $detectionWindow = max(1,(int) ($data['detection_window_minutes'] ?? 1));
        $blockMinutes = max(1,(int) ($data['block_minutes'] ?? 15));
        $logRetentionDays = max(1,(int) ($data['log_retention_days'] ?? 90));
        $autoCleanupEnabled = isset($data['auto_cleanup_enabled']) ? 1 : 0;
        $apiToken = trim($data['api_token'] ?? "");

        if($apiToken === ""){
            $apiToken = bin2hex(random_bytes(16));
        }

        $this->setSetting("threshold_limit",(string) $thresholdLimit);
        $this->setSetting("detection_window_minutes",(string) $detectionWindow);
        $this->setSetting("block_minutes",(string) $blockMinutes);
        $this->setSetting("log_retention_days",(string) $logRetentionDays);
        $this->setSetting("auto_cleanup_enabled",(string) $autoCleanupEnabled);
        $this->setSetting("api_token",$apiToken);

        return true;
    }

    public function regenerateApiToken()
    {
        $apiToken = bin2hex(random_bytes(16));
        $this->setSetting("api_token",$apiToken);

        return $apiToken;
    }

    private function archiveTable($sourceTable,$archiveTable,$dateColumn,$retentionDays)
    {
        if(!$this->tableExists($sourceTable)){
            return 0;
        }

        $createSql = "CREATE TABLE IF NOT EXISTS ".$archiveTable." LIKE ".$sourceTable;
        mysqli_query($this->conn,$createSql);

        $retentionDays = max(1,(int) $retentionDays);
        $condition = $dateColumn." < DATE_SUB(NOW(), INTERVAL ".$retentionDays." DAY)";

        $insertSql = "INSERT IGNORE INTO ".$archiveTable.
                      " SELECT * FROM ".$sourceTable.
                      " WHERE ".$condition;

        mysqli_query($this->conn,$insertSql);

        $deleteSql = "DELETE FROM ".$sourceTable.
                      " WHERE ".$condition;

        mysqli_query($this->conn,$deleteSql);

        return mysqli_affected_rows($this->conn);
    }

    public function archiveOldLogs($retentionDays=90)
    {
        return [
            "login_attempts" => $this->archiveTable(
                "login_attempts",
                "login_attempts_archive",
                "attempt_time",
                $retentionDays
            ),
            "threshold_logs" => $this->archiveTable(
                "threshold_logs",
                "threshold_logs_archive",
                "created_at",
                $retentionDays
            ),
            "detection_logs" => $this->archiveTable(
                "detection_logs",
                "detection_logs_archive",
                "detected_at",
                $retentionDays
            ),
            "activity_logs" => $this->archiveTable(
                "activity_logs",
                "activity_logs_archive",
                "created_at",
                $retentionDays
            ),
            "system_logs" => $this->archiveTable(
                "system_logs",
                "system_logs_archive",
                "created_at",
                $retentionDays
            )
        ];
    }

    public function getSystemSettings()
    {
        $config = $this->securityConfig();

        return [
            [
                "name" => "Batas Login Gagal",
                "value" => $config['threshold_limit']." percobaan",
                "description" => "Jumlah maksimal login gagal sebelum IP diblokir."
            ],
            [
                "name" => "Rentang Deteksi",
                "value" => $config['detection_window_minutes']." menit",
                "description" => "Periode waktu untuk menghitung percobaan login gagal."
            ],
            [
                "name" => "Durasi Blokir",
                "value" => $config['block_minutes']." menit",
                "description" => "Lama IP diblokir setelah melewati batas threshold."
            ],
            [
                "name" => "Retensi Log",
                "value" => $config['log_retention_days']." hari",
                "description" => "Log lama akan diarsipkan sebelum dibersihkan dari tabel utama."
            ],
            [
                "name" => "Auto Cleanup Log",
                "value" => $config['auto_cleanup_enabled'] ? "Aktif" : "Nonaktif",
                "description" => "Otomatis arsip dan hapus log lama sesuai retensi."
            ],
            [
                "name" => "Status Deteksi",
                "value" => "Aktif",
                "description" => "Sistem otomatis memeriksa brute force setiap login gagal."
            ],
        ];
    }

}
