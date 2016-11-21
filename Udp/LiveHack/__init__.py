"""
# Copyright (C) 2007 Nathan Ramella (nar@remix.net)
#
# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.
#
# This library is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public
# License along with this library; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# For questions regarding this module contact
# Nathan Ramella <nar@remix.net> or visit http://www.liveapi.org
"""

import sys
import Live
    
#path = "/Users/ST8/Production/Arduinome/Dev/LiveOSC"
#errorLog = open(path + "/stderr.txt", "w")
#errorLog.write("Starting Error Log")
#sys.stderr = errorLog
#stdoutLog = open(path + "/stdout.txt", "w")
#stdoutLog.write("Starting Standard Out Log")
#sys.stdout = stdoutLog

from livehack import LiveHack

def create_instance(c_instance):
    return LiveHack(c_instance)
