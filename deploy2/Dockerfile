FROM ubuntu:20.04
LABEL maintainer="Folarin Martins <folarin@engineer.com>"

ENV TZ=Africa/Lagos
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt update -y

RUN apt install -y apache2
#ENV TZ=Africa/Lagos
#RUN DEBIAN_FRONTEND=noninteractive TZ=Africa/Lagos
RUN apt install -y php libapache2-mod-php php-cli php-common php-mbstring php-gd php-intl php-xml php-mysql mcrypt php-zip
RUN rm -rf /var/www/html/*
ADD app /var/www
#RUN chown -R www-data:www-data /var/www
#ENV APACHE_RUN_USER www-data
#ENV APACHE_RUN_GROUP www-data
#ENV APACHE_LOG_DIR /var/log/apache2

#expose port 80
EXPOSE 80

#start apache server
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
