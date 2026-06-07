import paramiko

def check_health():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # Check if the app loads successfully
        cmd = "curl -s -I http://localhost:8086/"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print(out)
        
        # Check logs for the latest errors
        cmd_logs = "tail -n 20 /home/victor/sistemarh/storage/logs/laravel.log"
        print(f"--- Running: {cmd_logs} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd_logs)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print(out.encode('ascii', 'replace').decode('ascii'))
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    check_health()
