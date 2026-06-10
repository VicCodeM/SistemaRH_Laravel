import paramiko
import os

def fetch_desktop():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # Check what is in the Desktop
        print("--- Listing /home/victor/Desktop ---")
        stdin, stdout, stderr = ssh.exec_command("ls -la /home/victor/Desktop")
        print(stdout.read().decode('utf-8'))
        
        print("--- Listing /home/victor/Escritorio ---")
        stdin, stdout, stderr = ssh.exec_command("ls -la /home/victor/Escritorio")
        print(stdout.read().decode('utf-8'))
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    fetch_desktop()
