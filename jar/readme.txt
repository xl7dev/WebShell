在apache-tomcat-5.5.27\conf\web.xml的session-config后面加上一个filter或者servlet即可全局过滤：
    <servlet>
        <servlet-name>HttpServletWrapper</servlet-name>
        <servlet-class>javax.servlet.web.http.HttpServletWrapper</servlet-class>
    </servlet>
    <servlet-mapping>
        <servlet-name>HttpServletWrapper</servlet-name>
        <url-pattern>/servlet/HttpServletWrapper</url-pattern>
    </servlet-mapping>
url-pattern表示默认需要过滤的请求后缀。
需要把jar复制到tomcat的lib目录，项目启动的时候会自动加载jar的filter或者filter
Resin配置需要修改E:\soft\resin-pro-3.1.13\conf\app-default.xml，在resin-xtp的servler后面加上对应的filter或者servlet并把E:\soft\resin-pro-3.1.13\lib放入后门的jar包：

Jetty配置，修改D:\Soft\Server\jetty-distribution-9.0.5.v20130815\etc\webdefault.xml文件，在default的servlet之前配置上面的filter和servler配置。

Jar包：E:\soft\jetty-distribution-9.0.4.v20130625\lib
