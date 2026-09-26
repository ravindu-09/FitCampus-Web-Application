// Handle logout session destruction
    public function terminateSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();
        if (function_exists('session_unset')) {
            session_unset();
        }

        // Delete the session cookie from the browser
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session on the server
        session_destroy();
        
        // Ensure session write is closed immediately to prevent data persistence
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }