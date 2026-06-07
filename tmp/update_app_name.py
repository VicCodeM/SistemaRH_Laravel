import paramiko

def update_app_name():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    env_path = '/home/victor/sistemarh/.env'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        sftp = ssh.open_sftp()
        
        # 1. Update env
        with sftp.open(env_path, 'r') as f:
            lines = f.readlines()
            
        updated_lines = []
        found_app_name = False
        
        for line in lines:
            if line.startswith('APP_NAME='):
                updated_lines.append('APP_NAME="Sistema RH"\n')
                found_app_name = True
            else:
                updated_lines.append(line)
                
        if not found_app_name:
            updated_lines.insert(0, 'APP_NAME="Sistema RH"\n')
                
        with sftp.open(env_path, 'w') as f:
            f.writelines(updated_lines)
            
        sftp.close()
        
        # 2. Run clear config
        ssh.exec_command("cd /home/victor/sistemarh && php artisan config:clear && php artisan config:cache")
        import time
        time.sleep(2)
        
        # 3. Create test script to verify
        php_code = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

echo "APP_NAME IS NOW: " . config('app.name') . "\\n";
echo "MAIL_FROM_NAME IS NOW: " . config('mail.from.name') . "\\n";
"""
        remote_test_script = '/home/victor/sistemarh/test_app_name.php'
        sftp = ssh.open_sftp()
        with sftp.open(remote_test_script, 'w') as f:
            f.write(php_code)
        sftp.close()
        
        # 4. Run verification
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
    update_app_name()
