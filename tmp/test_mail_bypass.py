import paramiko

def test_bypass():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    remote_test_script = '/home/victor/sistemarh/test_mail.php'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        php_code = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

// Force encryption to null to let Symfony Mailer handle STARTTLS on port 587
config(['mail.mailers.smtp.encryption' => null]);

use Illuminate\\Support\\Facades\\Mail;
try {
    Mail::raw('Este es un correo de prueba automatizado para verificar que la configuración de Resend está funcionando correctamente en el servidor.', function($message) {
        $message->to('luisyoumi@gmail.com')
                ->subject('Prueba de correo exitosa - SistemaRH (Bypass)');
    });
    echo "CORREO_ENVIADO\\n";
} catch (\\Throwable $e) {
    echo "ERROR_AL_ENVIAR: " . $e->getMessage() . "\\n";
}
"""
        with sftp.open(remote_test_script, 'w') as f:
            f.write(php_code)
        sftp.close()
        
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
    test_bypass()
