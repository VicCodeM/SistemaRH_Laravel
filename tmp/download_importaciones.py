import paramiko
import os
import zipfile

def download_folders():
    host = '10.10.10.10'
    user = 'victor'
    password = '6433'
    
    remote_zip = '/home/victor/Desktop/importaciones.zip'
    local_zip = 'C:/Dev/Web/SistemaRH_Laravel/tmp/importaciones.zip'
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(host, username=user, password=password, timeout=10)
        
        # 1. Zip the folders remotely
        cmd = 'cd /home/victor/Desktop && zip -r importaciones.zip "ControldeImportaciones" "ControldeImportaciones - Sin licencia"'
        print(f"--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        stdout.read() # Wait for completion
        
        # 2. Download the zip
        print("Downloading zip...")
        sftp = ssh.open_sftp()
        sftp.get(remote_zip, local_zip)
        sftp.close()
        
        # 3. Clean up remote zip
        ssh.exec_command(f'rm {remote_zip}')
        
        # 4. Unzip locally
        print("Unzipping locally...")
        extract_dir = 'C:/Dev/Web/SistemaRH_Laravel/tmp/importaciones_source'
        if not os.path.exists(extract_dir):
            os.makedirs(extract_dir)
            
        with zipfile.ZipFile(local_zip, 'r') as zip_ref:
            zip_ref.extractall(extract_dir)
            
        print("Done! Files are in tmp/importaciones_source")
        
    except Exception as e:
        print(f"Failed: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    download_folders()
