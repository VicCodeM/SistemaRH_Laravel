import paramiko

def fix_env_and_test():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    env_path = '/home/victor/sistemarh/.env'
    remote_test_script = '/home/victor/sistemarh/test_mail.php'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        # 1. Update env
        with sftp.open(env_path, 'r') as f:
            lines = f.readlines()
            
        updated_lines = []
        for line in lines:
            if line.startswith('MAIL_ENCRYPTION='):
                updated_lines.append('MAIL_ENCRYPTION=null\n')
            else:
                updated_lines.append(line)
                
        with sftp.open(env_path, 'w') as f:
            f.writelines(updated_lines)
            
        # 2. Run clear config
        ssh.exec_command("cd /home/victor/sistemarh && php artisan config:clear")
        
        # 3. Create test script
        php_code = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
use Illuminate\\Support\\Facades\\Mail;
try {
    Mail::raw('Este es un correo de prueba automatizado para verificar que la configuración de Resend está funcionando correctamente.', function($message) {
        $message->to('luisyoumi@gmail.com')
                ->subject('Prueba de correo exitosa - SistemaRH');
    });
    echo "CORREO_ENVIADO\\n";
} catch (\\Throwable $e) {
    echo "ERROR_AL_ENVIAR: " . $e->getMessage() . "\\n";
}
"""
        with sftp.open(remote_test_script, 'w') as f:
            f.write(php_code)
        sftp.close()
        
        import time
        time.sleep(1) # Wait for config to clear
        
        # 4. Run test
        cmd = f"cd /home/victor/sistemarh && php {remote_test_script}"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        if out: print(out)
        err = stderr.read().decode('utf-8', errors='replace').strip()
        if err: print("Error:", err)
        
        ssh.exec_command(f"rm {remote_test_script}")
        ssh.exec_command("sudo systemctl restart php8.4-fpm")
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    fix_env_and_test()
