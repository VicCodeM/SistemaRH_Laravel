import paramiko

def check_log():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        cmd = "grep -E '^\[2026' /home/victor/sistemarh/storage/logs/laravel.log | tail -n 5"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        print(out.encode('ascii', errors='replace').decode('ascii'))
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    check_log()
