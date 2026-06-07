import paramiko

def clear_cache():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    remote_dir = '/home/victor/sistemarh'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            f"cd {remote_dir} && php artisan optimize:clear",
            f"cd {remote_dir} && php artisan cache:clear",
            f"cd {remote_dir} && php artisan view:clear",
            "sudo systemctl restart php8.4-fpm",
            "sudo systemctl restart apache2"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            if out: print(out)
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if err: print("Error:", err)
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    clear_cache()
