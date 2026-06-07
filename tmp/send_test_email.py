import paramiko

def send_email():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        php_code = """
<?php
use Illuminate\\Support\\Facades\\Mail;
try {
    Mail::raw('Este es un correo de prueba automatizado para verificar que la configuración de Resend está funcionando correctamente.', function($message) {
        $message->to('luisyoumi@gmail.com')
                ->subject('Prueba de correo - SistemaRH');
    });
    echo "CORREO_ENVIADO\\n";
} catch (\\Exception $e) {
    echo "ERROR_AL_ENVIAR: " . $e->getMessage() . "\\n";
}
"""
        sftp = ssh.open_sftp()
        with sftp.open('/home/victor/sistemarh/test_mail.php', 'w') as f:
            f.write(php_code)
        sftp.close()
        
        # We can just run php with artisan tinker, or just `php artisan tinker test_mail.php`
        # But `php artisan tinker test_mail.php` sometimes hangs because PsySH waits for interaction on some envs? 
        # Better to run it as a regular Laravel script. Wait, in Laravel 11, we can create a quick artisan command or just run a script that bootstraps the app.
        bootstrap_code = """
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();
use Illuminate\\Support\\Facades\\Mail;
try {
    Mail::raw('Este es un correo de prueba automatizado para verificar que la configuración de Resend está funcionando correctamente.', function($message) {
        $message->to('luisyoumi@gmail.com')
                ->subject('Prueba de correo - SistemaRH');
    });
    echo "CORREO_ENVIADO\\n";
} catch (\\Exception $e) {
    echo "ERROR_AL_ENVIAR: " . $e->getMessage() . "\\n";
}
"""
        with sftp.open('/home/victor/sistemarh/test_mail.php', 'w') as f:
            f.write(bootstrap_code)
        
        cmd = "cd /home/victor/sistemarh && php test_mail.php"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        exit_status = stdout.channel.recv_exit_status() # wait for completion
        out = stdout.read().decode('utf-8', errors='replace').strip()
        if out: print(out)
        err = stderr.read().decode('utf-8', errors='replace').strip()
        if err: print("Error:", err)
        print("Exit:", exit_status)
        
        ssh.exec_command("rm /home/victor/sistemarh/test_mail.php")
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    send_email()
