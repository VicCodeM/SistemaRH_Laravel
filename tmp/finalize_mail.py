import paramiko

def finalize_mail():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    env_path = '/home/victor/sistemarh/.env'
    remote_test_script = '/home/victor/sistemarh/test_mail_final.php'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        # 1. Update MAIL_FROM_ADDRESS in .env
        with sftp.open(env_path, 'r') as f:
            lines = f.readlines()
            
        updated_lines = []
        for line in lines:
            if line.startswith('MAIL_FROM_ADDRESS='):
                updated_lines.append('MAIL_FROM_ADDRESS="notificaciones@vmsoi.xyz"\\n')
            else:
                updated_lines.append(line)
                
        with sftp.open(env_path, 'w') as f:
            f.writelines(updated_lines)
            
        # 2. Clear config and restart FPM
        ssh.exec_command("cd /home/victor/sistemarh && php artisan config:clear && php artisan config:cache")
        ssh.exec_command("sudo systemctl restart php8.4-fpm")
        
        import time
        time.sleep(2)
        
        # 3. Create test script to send a real email
        php_code = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

use Illuminate\\Support\\Facades\\Mail;
try {
    Mail::raw('¡Felicidades! Tu dominio ha sido verificado correctamente y ahora el sistema SistemaRH puede enviar correos a cualquier persona desde notificaciones@vmsoi.xyz sin restricciones.', function($message) {
        $message->to('luisyoumi@gmail.com')
                ->subject('¡Dominio Verificado! - SistemaRH Listo');
    });
    echo "CORREO_FINAL_ENVIADO\\n";
} catch (\\Throwable $e) {
    echo "ERROR_AL_ENVIAR: " . $e->getMessage() . "\\n";
}
"""
        with sftp.open(remote_test_script, 'w') as f:
            f.write(php_code)
        sftp.close()
        
        # 4. Run the test
        cmd = f"cd /home/victor/sistemarh && php {remote_test_script}"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        if out: print(out)
        err = stderr.read().decode('utf-8', errors='replace').strip()
        if err: print("Error:", err)
        
        ssh.exec_command(f"rm {remote_test_script}")
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    finalize_mail()
