import paramiko

def check_services():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        commands = [
            "ls -la /etc/apache2/sites-enabled/",
            "systemctl list-units --type=service | grep -i 'php\\|laravel\\|sistema'",
            "ps aux | grep 'artisan serve'"
        ]
        
        for cmd in commands:
            print(f"--- Running: {cmd} ---")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            if out: print(out)
            
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    check_services()
