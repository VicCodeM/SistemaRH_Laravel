import paramiko

def fix_env_and_migrate():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    remote_dir = '/home/victor/sistemarh'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            f"cd {remote_dir} && sed -i 's/^DB_USERNAME=.*/DB_USERNAME=sistemarh/' .env",
            f"cd {remote_dir} && sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=sistemarh/' .env",
            f"sudo mysql -e \"GRANT ALL PRIVILEGES ON sistemarh_laravel.* TO 'sistemarh'@'localhost' IDENTIFIED BY 'sistemarh'; FLUSH PRIVILEGES;\"",
            f"cd {remote_dir} && php artisan optimize:clear",
            f"cd {remote_dir} && php artisan migrate --force"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            exit_status = stdout.channel.recv_exit_status()
            out = stdout.read().decode('utf-8', errors='replace').strip()
            if out: print(out)
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if err: print("Error:", err)
            print("Exit code:", exit_status)
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    fix_env_and_migrate()
