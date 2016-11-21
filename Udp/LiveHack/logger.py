
dst = "C:/Users/nAkoustix/Desktop/log.txt"

logfile = open(dst,"w")
logfile.close()

def debug(msg):
    logfile = open(dst, "a")
    logfile.write(msg + "\n")
    logfile.close()
    