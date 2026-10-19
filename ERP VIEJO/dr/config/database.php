<?php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    die('Acceso denegado directamente');
}


/*
    VIEJA

    private string $host     = "172.16.4.140";
    private string $username = "sa";
    private string $password = "@Gr0f3m";
    
    NUEVA

    private string $host     = "172.16.2.70";
    private string $host     = "SERVIDOR/SQLEXPRESS";
    private string $username = "cms";
    private string $password = "cms2026";

*/

class Database
{
    private string $host     = "172.16.2.70";
    private string $db_name  = "cms";
    private string $username = "cms";
    private string $password = "cms2026";
    public ?PDO $conn        = null;

    public function getConnection(): ?PDO
    {
        try {
            $this->conn = new PDO(
                "sqlsrv:Server={$this->host};Database={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Loguear sin exponer credenciales al navegador
            error_log("DB Connection error: " . $exception->getMessage());
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>