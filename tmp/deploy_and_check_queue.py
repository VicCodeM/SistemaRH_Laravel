import paramiko

def deploy_and_check():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    local_zip = 'deploy.zip'
    remote_dir = '/home/victor/sistemarh'
    remote_zip = f"{remote_dir}/deploy.zip"
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # 1. Upload and deploy
        sftp = ssh.open_sftp()
        sftp.put(local_zip, remote_zip)
        sftp.close()
        
        commands = [
            f"cd {remote_dir} && unzip -q -o deploy.zip",
            f"cd {remote_dir} && php artisan optimize:clear",
            f"sudo systemctl restart php8.4-fpm",
            # 2. Check jobs table
            f"cd {remote_dir} && php artisan db:show --counts | grep jobs",
            # 3. Check if queue worker is running
            "ps aux | grep -i 'queue:work'",
            # 4. Check logs for mail errors
            "grep -i -E 'mail|smtp|resend' /home/victor/sistemarh/storage/logs/laravel.log | tail -n 10"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if out: print(out.encode('ascii', 'replace').decode('ascii'))
            if err: print(f"Error: {err.encode('ascii', 'replace').decode('ascii')}")
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    deploy_and_check()
