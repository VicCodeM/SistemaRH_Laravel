import paramiko

def test_smtp():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # Test connection using nc (netcat) or bash pseudo-device
        cmd = "timeout 5 bash -c '</dev/tcp/smtp.resend.com/587' && echo 'OPEN' || echo 'CLOSED'"
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        if out: print(out)
        
        cmd_465 = "timeout 5 bash -c '</dev/tcp/smtp.resend.com/465' && echo 'OPEN' || echo 'CLOSED'"
        print(f"--- Running: {cmd_465} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd_465)
        out = stdout.read().decode('utf-8', errors='replace').strip()
        if out: print(out)
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    test_smtp()
