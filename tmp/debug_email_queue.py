import paramiko

def debug_email():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    remote_dir = '/home/victor/sistemarh'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            f"cd {remote_dir} && php artisan queue:failed",
            f"cd {remote_dir} && sudo mysql -e 'SELECT id, connection, queue, payload, exception FROM sistemarh_laravel.failed_jobs ORDER BY id DESC LIMIT 2\\G'",
            f"tail -n 100 {remote_dir}/storage/logs/laravel.log | grep -i -E 'mail|swift|resend|exception|error' | tail -n 20"
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
    debug_email()
