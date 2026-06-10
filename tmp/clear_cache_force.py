import paramiko

def clear_cache_force():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            "cd /home/victor/sistemarh && php artisan optimize:clear",
            "cd /home/victor/sistemarh && php artisan cache:clear",
            "cd /home/victor/sistemarh && php artisan view:clear",
            "cd /home/victor/sistemarh && php artisan route:clear",
            "cd /home/victor/sistemarh && php artisan config:clear",
            "sudo rm -rf /home/victor/sistemarh/bootstrap/cache/*.php",
            "sudo rm -rf /home/victor/sistemarh/storage/framework/views/*.php",
            "sudo rm -rf /home/victor/sistemarh/storage/framework/cache/data/*",
            "sudo systemctl restart php8.4-fpm",
            "sudo systemctl restart apache2"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if out: print(out)
            if err: print(f"Error: {err}")
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    clear_cache_force()
