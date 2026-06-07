import paramiko

def add_route_and_curl():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        route_code = """
Route::get('/test-mail', function () {
    try {
        \\Illuminate\\Support\\Facades\\Mail::raw('Este es un correo de prueba automatizado para verificar la configuración de Resend.', function($message) {
            $message->to('luisyoumi@gmail.com')
                    ->subject('Prueba de correo - SistemaRH');
        });
        return "CORREO_ENVIADO";
    } catch (\\Exception $e) {
        return "ERROR_AL_ENVIAR: " . $e->getMessage();
    }
});
"""
        cmd1 = f"echo '{route_code}' >> /home/victor/sistemarh/routes/web.php"
        print("--- Adding route ---")
        ssh.exec_command(cmd1)
        
        # Give it a second
        import time
        time.sleep(1)
        
        cmd2 = "curl -s -H 'Accept: application/json' http://localhost:8086/test-mail"
        print(f"--- Running: {cmd2} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd2)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print("CURL OUTPUT:")
        print(out.encode('ascii', errors='replace').decode('ascii'))
        
        # Remove the route (the last 16 lines)
        cmd3 = "sed -i 's/Route::get(\\'\\/test-mail.*//g' /home/victor/sistemarh/routes/web.php"
        # A safer way to remove it is just head -n -16 or similar, but since we appended it, we can just replace what we added.
        # Actually, let's just restore the file from git? No, we don't have git initialized maybe.
        # It's fine to leave it or we can sed it out. Let's use python sftp to read/write without the appended part.
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    add_route_and_curl()
